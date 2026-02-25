<?php

namespace App\Http\Controllers;

use App\Models\tbldataentry;
use App\Models\StateInsight;
use App\Models\tblriskindicators;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\CorrectionFactorForStates;
use App\Http\Controllers\Traits\CalculatesRisk;
use App\Http\Controllers\Traits\GeneratesInsights;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Services\LocationInsightGenerator;
use App\Models\LocationInsight;

class LocationController extends Controller
{
    use CalculatesRisk, GeneratesInsights;

    // ──────────────────────────────────────────────────────────────────────────
    // Shared look-ups
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * FIXED: added Cache::remember (was uncached, fired on every page load).
     */
    private function getCrimeIndexIndicators()
    {
        return Cache::remember('indicators_crime', 86400, function () {
            try {
                return tblriskindicators::where('author', 'crimeIndex')
                    ->orderByRaw('CAST(indicators AS CHAR) ASC')
                    ->pluck('indicators');
            } catch (\Exception $e) {
                Log::error('CRITICAL: Could not fetch crime indicators. ' . $e->getMessage());
                return collect();
            }
        });
    }

    /**
     * FIXED: was a live CorrectionFactorForStates::where(state) on every call.
     * Now delegates to the memoized getCorrectionFactors() from the trait.
     */
    private function getStateCorrectionFactors(string $state): object
    {
        $factors = $this->getCorrectionFactors()->get($this->norm($state));

        return $factors ?? (object) [
            'incident_correction' => 1,
            'victim_correction'   => 1,
            'death_correction'    => 1,
        ];
    }

    /**
     * FIXED: wrapped in Cache::remember (1 h) so the national crime-indicator
     * aggregate is not re-computed on every location page visit.
     */
    private function buildCrimeIndicatorReports(int $year): array
    {
        return Cache::remember("crime_indicator_reports:{$year}", 3600, function () use ($year) {
            $crimeIndicators = $this->getCrimeIndexIndicators();
            if ($crimeIndicators->isEmpty()) return [];

            $data = DB::table('tbldataentry')
                ->where('yy', $year)
                ->whereNotNull('location')
                ->where('location', '!=', '')
                ->whereIn('riskindicators', $crimeIndicators->all())
                ->selectRaw('
                    TRIM(location) AS location,
                    yy,
                    COUNT(*) AS raw_incident_count,
                    COALESCE(SUM(Casualties_count), 0) AS raw_casualties_sum,
                    COALESCE(SUM(victim), 0) AS raw_victims_sum
                ')
                ->groupBy(DB::raw('TRIM(location)'), 'yy')
                ->get();

            return $this->calculateCrimeRiskIndexFromIndicators($data);
        });
    }

    private function calculateRankAndScore(string $targetState, int $year): array
    {
        $reports = $this->buildCrimeIndicatorReports($year);

        if (empty($reports)) {
            return ['score' => 0, 'rank' => 'N/A', 'ordinal' => '', 'total_states' => 0];
        }

        $targetKey = trim(preg_replace('/\s+/', ' ', $targetState));

        $ranked    = [];
        $rank      = 0;
        $prevScore = null;

        foreach (collect($reports)->sortByDesc(fn($r) => (float) ($r['normalized_ratio'] ?? 0))->values() as $r) {
            $score = (float) ($r['normalized_ratio'] ?? 0);
            if ($prevScore === null || $score !== $prevScore) {
                $rank++;
                $prevScore = $score;
            }
            $ranked[$r['location']] = ['rank' => $rank, 'score' => $r['normalized_ratio']];
        }

        $target = $ranked[$targetKey] ?? null;
        if (!$target) {
            return ['score' => 0, 'rank' => 'N/A', 'ordinal' => '', 'total_states' => count($ranked)];
        }

        return [
            'score'        => $target['score'],
            'rank'         => $target['rank'],
            'ordinal'      => $this->ordinalSuffix((int) $target['rank']),
            'total_states' => count($ranked),
        ];
    }

    private function ordinalSuffix(int $n): string
    {
        if (!in_array(($n % 100), [11, 12, 13], true)) {
            return match ($n % 10) {
                1 => 'st',
                2 => 'nd',
                3 => 'rd',
                default => 'th',
            };
        }
        return 'th';
    }

    // ──────────────────────────────────────────────────────────────────────────
    // MAIN VIEW
    // ──────────────────────────────────────────────────────────────────────────

    public function getTotalIncident(LocationInsightGenerator $ai, string $state, $year = null)
    {
        $this->enforceTier2LocationLock($state, request());

        $year           = (int) ($year ?: now()->year);
        $availableYears = range(now()->year, 2018);

        if (!in_array($year, $availableYears, true)) {
            $year = now()->year;
        }

        // ── Fetch all state-specific data in one cached bundle ────────────────
        $bundle = $this->getLocationBundle($state, $year);

        // Unpack bundle
        $total_incidents  = $bundle['total_incidents'];
        $mostFrequentRisk = $bundle['mostFrequentRisk'];
        $monthlyData      = $bundle['monthlyData'];
        $topRiskIndicators = $bundle['topRiskIndicators'];
        $yearlyData       = $bundle['yearlyData'];
        $motiveData       = $bundle['motiveData'];
        $attackData       = $bundle['attackData'];
        $mostAffectedLGA  = $bundle['mostAffectedLGA'];
        $recentIncidents  = $bundle['recentIncidents'];
        $crimeTable       = $bundle['crimeTable'];

        // Chart arrays
        $monthNames    = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $endMonth      = ((int) $year === (int) now()->year) ? (int) now()->format('n') : 12;
        $numericMonths = collect(range(1, $endMonth))->map(fn($m) => str_pad($m, 2, '0', STR_PAD_LEFT))->values();

        $chartLabels   = collect(range(1, $endMonth))->map(fn($m) => $monthNames[$m - 1])->values();
        $incidentCounts = $numericMonths->map(fn($mm) => (int) ($monthlyData[$mm]->total_incidents ?? 0))->values();

        $topRiskLabels = $topRiskIndicators->pluck('riskindicators')->toArray();
        $topRiskCounts = $topRiskIndicators->pluck('occurrences')->toArray();
        $yearLabels    = $yearlyData->pluck('yy')->toArray();
        $yearCounts    = $yearlyData->pluck('total_incidents')->toArray();
        $motiveLabels  = $motiveData->pluck('name')->toArray();
        $motiveCounts  = $motiveData->pluck('occurrences')->toArray();
        $attackLabels  = $attackData->pluck('name')->toArray();
        $attackCounts  = $attackData->pluck('occurrences')->toArray();

        $getStates = StateInsight::pluck('state'); // small look-up table, fine as-is

        // ── Score + rank (national, cached 1 h) ──────────────────────────────
        $rankingData         = $this->calculateRankAndScore($state, $year);
        $stateCrimeIndexScore = $rankingData['score'];
        $stateRank           = $rankingData['rank'];
        $stateRankOrdinal    = $rankingData['ordinal'];

        // ── Automated insights ────────────────────────────────────────────────
        $trendInsights   = $this->calculateTrendInsights($state, $year);
        $lethalityInsight = $this->calculateLethalityInsights($state, $year);
        $forecastInsight  = $this->calculateForecast($state);

        $automatedInsights = array_values(array_filter([
            $trendInsights['velocity'] ?? null,
            $trendInsights['emerging'] ?? null,
            $lethalityInsight,
            $forecastInsight,
        ]));

        // ── Groq AI override ──────────────────────────────────────────────────
        $fallbackInsights = $automatedInsights;

        $summary = [
            'total_incidents'    => (int) $total_incidents,
            'most_frequent_risks' => $mostFrequentRisk->map(fn($r) => ['risk' => $r->riskindicators, 'count' => (int) $r->occurrences])->values()->all(),
            'most_affected_lga'  => $mostAffectedLGA?->lga,
            'monthly_incidents'  => array_combine($chartLabels->toArray(), $incidentCounts->toArray()),
            'top_risks'          => collect($topRiskLabels)->values()->map(fn($label, $i) => ['risk' => $label, 'count' => (int) ($topRiskCounts[$i] ?? 0)])->all(),
            'top_actors'         => collect($attackLabels)->values()->map(fn($label, $i) => ['actor' => $label, 'count' => (int) ($attackCounts[$i] ?? 0)])->all(),
            'crime_index'        => ['score' => (float) $stateCrimeIndexScore, 'rank' => is_numeric($stateRank) ? (int) $stateRank : $stateRank],
        ];

        $hash     = $ai->hashSummary($summary);
        $cacheKey = "location_ai:{$state}:{$year}:{$hash}";

        $stored = LocationInsight::where('state', $state)->where('year', (int) $year)->first();

        if ($stored && !empty($stored->insights)) {
            $automatedInsights = $stored->insights;
        } else {
            $cached = Cache::get($cacheKey);
            if (is_array($cached) && !empty($cached)) {
                $automatedInsights = $cached;
            } else {
                $res               = $ai->generateWithMeta($state, (int) $year, $summary, $fallbackInsights);
                $automatedInsights = $res['insights'] ?? $fallbackInsights;
                $source            = $res['meta']['source'] ?? 'fallback';

                LocationInsight::updateOrCreate(
                    ['state' => $state, 'year' => (int) $year],
                    [
                        'hash'         => $hash,
                        'summary'      => $summary,
                        'insights'     => $automatedInsights,
                        'source'       => $source,
                        'model'        => config('services.groq.model') ?? null,
                        'generated_at' => now(),
                    ]
                );

                Cache::put($cacheKey, $automatedInsights, $source === 'groq' ? 86400 : 300);
            }
        }

        return view('locationIntelligence', compact(
            'total_incidents',
            'availableYears',
            'year',
            'state',
            'mostFrequentRisk',
            'chartLabels',
            'incidentCounts',
            'topRiskLabels',
            'topRiskCounts',
            'yearLabels',
            'yearCounts',
            'attackLabels',
            'attackCounts',
            'motiveLabels',
            'motiveCounts',
            'mostAffectedLGA',
            'recentIncidents',
            'getStates',
            'stateCrimeIndexScore',
            'crimeTable',
            'automatedInsights',
            'stateRank',
            'stateRankOrdinal',
        ));
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Location data bundle — ONE cached method for all state queries
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Bundles all per-state queries into a single Cache::remember block.
     * TTL = 30 min. Key includes state+year so each combination is independent.
     *
     * FIXES:
     *  - 10+ separate uncached tbldataentry queries → 8 queries cached for 30 min
     *  - LOWER(location) pattern preserved for data compatibility (add generated
     *    column index if you can normalise data at source, see README)
     */
    private function getLocationBundle(string $state, int $year): array
    {
        $cacheKey = 'location_bundle:' . strtolower($state) . ':' . $year;

        return Cache::remember($cacheKey, 1800, function () use ($state, $year) {
            $stateParam = strtolower($state);

            $total_incidents = tbldataentry::whereRaw('LOWER(location) = ?', [$stateParam])
                ->where('yy', $year)
                ->count();

            $mostFrequentRisk = tbldataentry::select('riskindicators', DB::raw('COUNT(*) as occurrences'))
                ->whereRaw('LOWER(location) = ?', [$stateParam])
                ->where('yy', $year)
                ->groupBy('riskindicators')
                ->orderByDesc('occurrences')
                ->take(2)
                ->get();

            $endMonth      = ((int) $year === (int) now()->year) ? (int) now()->format('n') : 12;
            $numericMonths = collect(range(1, $endMonth))->map(fn($m) => str_pad($m, 2, '0', STR_PAD_LEFT))->values();

            $monthlyData = tbldataentry::selectRaw('mm, COUNT(*) as total_incidents')
                ->whereRaw('LOWER(location) = ?', [$stateParam])
                ->where('yy', (int) $year)
                ->whereIn('mm', $numericMonths->all())
                ->groupBy('mm')
                ->get()
                ->keyBy('mm');

            $topRiskIndicators = tbldataentry::select('riskindicators', DB::raw('COUNT(*) as occurrences'))
                ->whereRaw('LOWER(location) = ?', [$stateParam])
                ->where('yy', $year)
                ->groupBy('riskindicators')
                ->orderByDesc('occurrences')
                ->take(5)
                ->get();

            $yearlyData = tbldataentry::selectRaw('yy, COUNT(*) as total_incidents')
                ->whereRaw('LOWER(location) = ?', [$stateParam])
                ->where('yy', '>=', 2018)
                ->groupBy('yy')
                ->orderBy('yy', 'asc')
                ->get();

            $motiveData = tbldataentry::join('motives_specific', 'tbldataentry.motive_specific', '=', 'motives_specific.id')
                ->select('motives_specific.name', DB::raw('COUNT(*) as occurrences'))
                ->whereRaw('LOWER(tbldataentry.location) = ?', [$stateParam])
                ->where('tbldataentry.yy', $year)
                ->whereRaw('LOWER(motives_specific.name) != ?', ['others'])
                ->groupBy('motives_specific.name')
                ->orderByDesc('occurrences')
                ->take(5)
                ->get();

            $attackData = tbldataentry::join('attack_group', 'tbldataentry.attack_group_name', '=', 'attack_group.id')
                ->select('attack_group.name', DB::raw('COUNT(*) as occurrences'))
                ->whereRaw('LOWER(tbldataentry.location) = ?', [$stateParam])
                ->where('tbldataentry.yy', $year)
                ->whereRaw('LOWER(attack_group.name) != ?', ['others'])
                ->groupBy('attack_group.name')
                ->orderByDesc('occurrences')
                ->take(5)
                ->get();

            $mostAffectedLGA = tbldataentry::select('lga', DB::raw('COUNT(*) as occurrences'))
                ->whereRaw('LOWER(location) = ?', [$stateParam])
                ->where('yy', $year)
                ->where('lga', '!=', '')
                ->groupBy('lga')
                ->orderByDesc('occurrences')
                ->first();

            $recentIncidents = tbldataentry::select('lga', 'add_notes', 'riskindicators', 'impact', 'datecreated')
                ->whereRaw('LOWER(location) = ?', [$stateParam])
                ->orderBy('datecreated', 'desc')
                ->limit(5)
                ->get()
                ->sortByDesc(function ($incident) {
                    try {
                        return \Carbon\Carbon::createFromFormat('M d, Y', $incident->datecreated)->timestamp;
                    } catch (\Throwable $e) {
                        return 0;
                    }
                })
                ->take(5);

            // Crime table (current + previous year, two queries merged from three)
            $crimeIndicators = $this->getCrimeIndexIndicators();
            $crimeTable      = [];

            if ($crimeIndicators->isNotEmpty()) {
                // Fetch both years in one query, pivot in PHP
                $bothYears = tbldataentry::select('riskindicators', 'yy', DB::raw('COUNT(*) as incident_count'))
                    ->whereRaw('LOWER(location) = ?', [$stateParam])
                    ->whereIn('yy', [$year, $year - 1])
                    ->whereIn('riskindicators', $crimeIndicators)
                    ->groupBy('riskindicators', 'yy')
                    ->get()
                    ->groupBy('riskindicators');

                foreach ($crimeIndicators as $indicator) {
                    $rows        = $bothYears->get($indicator) ?? collect();
                    $currentCount  = (int) ($rows->firstWhere('yy', $year)?->incident_count  ?? 0);
                    $previousCount = (int) ($rows->firstWhere('yy', $year - 1)?->incident_count ?? 0);

                    if ($currentCount > 0) {
                        $status = 'Stable';
                        if ($currentCount > $previousCount)      $status = 'Escalating';
                        elseif ($currentCount < $previousCount)  $status = 'Improving';

                        $crimeTable[] = [
                            'indicator_name'      => $indicator,
                            'incident_count'      => $currentCount,
                            'previous_year_count' => $previousCount,
                            'status'              => $status,
                        ];
                    }
                }

                $crimeTable = collect($crimeTable)->sortByDesc('incident_count')->values()->all();
            }

            return compact(
                'total_incidents',
                'mostFrequentRisk',
                'monthlyData',
                'topRiskIndicators',
                'yearlyData',
                'motiveData',
                'attackData',
                'mostAffectedLGA',
                'recentIncidents',
                'crimeTable'
            );
        });
    }

    // ──────────────────────────────────────────────────────────────────────────
    // AJAX data endpoint (year-switcher)
    // ──────────────────────────────────────────────────────────────────────────

    public function getStateData(LocationInsightGenerator $ai, Request $request, string $state, $year)
    {
        $this->enforceTier2LocationLock($state, $request);

        $year   = (int) $year;
        $bundle = $this->getLocationBundle($state, $year);

        $total_incidents   = $bundle['total_incidents'];
        $mostFrequentRisk  = $bundle['mostFrequentRisk'];
        $monthlyData       = $bundle['monthlyData'];
        $topRiskIndicators = $bundle['topRiskIndicators'];
        $yearlyData        = $bundle['yearlyData'];
        $attackData        = $bundle['attackData'];
        $mostAffectedLGA   = $bundle['mostAffectedLGA'];
        $recentIncidents   = $bundle['recentIncidents'];
        $crimeTable        = $bundle['crimeTable'];

        $monthNames    = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $endMonth      = ($year === (int) now()->year) ? (int) now()->format('n') : 12;
        $numericMonths = collect(range(1, $endMonth))->map(fn($m) => str_pad($m, 2, '0', STR_PAD_LEFT))->values();

        $chartLabels    = collect(range(1, $endMonth))->map(fn($m) => $monthNames[$m - 1])->values()->toArray();
        $incidentCounts = $numericMonths->map(fn($mm) => (int) ($monthlyData[$mm]->total_incidents ?? 0))->values()->toArray();
        $topRiskLabels  = $topRiskIndicators->pluck('riskindicators')->values();
        $topRiskCounts  = $topRiskIndicators->pluck('occurrences')->values();
        $yearLabels     = $yearlyData->pluck('yy')->toArray();
        $yearCounts     = $yearlyData->pluck('total_incidents')->toArray();
        $attackLabels   = $attackData->pluck('name')->values();
        $attackCounts   = $attackData->pluck('occurrences')->values();

        $rankingData         = $this->calculateRankAndScore($state, $year);
        $stateCrimeIndexScore = $rankingData['score'];

        // AI insights (same pattern as getTotalIncident)
        $trendInsights   = $this->calculateTrendInsights($state, $year);
        $lethalityInsight = $this->calculateLethalityInsights($state, $year);
        $forecastInsight  = $this->calculateForecast($state);

        $automatedInsights = array_values(array_filter([
            $trendInsights['velocity'] ?? null,
            $trendInsights['emerging'] ?? null,
            $lethalityInsight,
            $forecastInsight,
        ]));

        $fallbackInsights = $automatedInsights;

        $summary = [
            'total_incidents'    => (int) $total_incidents,
            'most_frequent_risks' => $mostFrequentRisk->map(fn($r) => ['risk' => $r->riskindicators, 'count' => (int) $r->occurrences])->values()->all(),
            'most_affected_lga'  => $mostAffectedLGA?->lga,
            'monthly_incidents'  => array_combine($chartLabels, $incidentCounts),
            'top_risks'          => collect($topRiskLabels)->values()->map(fn($label, $i) => ['risk' => $label, 'count' => (int) ($topRiskCounts[$i] ?? 0)])->all(),
            'top_actors'         => collect($attackLabels)->values()->map(fn($label, $i) => ['actor' => $label, 'count' => (int) ($attackCounts[$i] ?? 0)])->all(),
            'crime_index'        => ['score' => (float) $stateCrimeIndexScore, 'rank' => is_numeric($rankingData['rank']) ? (int) $rankingData['rank'] : $rankingData['rank']],
        ];

        $hash     = $ai->hashSummary($summary);
        $cacheKey = "location_ai:{$state}:{$year}:{$hash}";

        $stored = LocationInsight::where('state', $state)->where('year', (int) $year)->where('hash', $hash)->first();

        if ($stored && !empty($stored->insights)) {
            $automatedInsights = $stored->insights;
        } else {
            $cached = Cache::get($cacheKey);
            if (is_array($cached) && !empty($cached)) {
                $automatedInsights = $cached;
            } else {
                $res               = $ai->generateWithMeta($state, (int) $year, $summary, $fallbackInsights);
                $automatedInsights = $res['insights'] ?? $fallbackInsights;
                $source            = $res['meta']['source'] ?? 'fallback';

                LocationInsight::updateOrCreate(
                    ['state' => $state, 'year' => (int) $year],
                    [
                        'hash'         => $hash,
                        'summary'      => $summary,
                        'insights'     => $automatedInsights,
                        'source'       => $source,
                        'model'        => config('services.groq.model') ?? null,
                        'generated_at' => now(),
                    ]
                );

                Cache::put($cacheKey, $automatedInsights, $source === 'groq' ? 86400 : 300);
            }
        }

        return response()->json([
            'total_incidents'      => $total_incidents,
            'mostFrequentRisk'     => $mostFrequentRisk,
            'mostAffectedLGA'      => $mostAffectedLGA,
            'recentIncidents'      => $recentIncidents,
            'chartLabels'          => $chartLabels,
            'incidentCounts'       => $incidentCounts,
            'topRiskLabels'        => $topRiskLabels,
            'topRiskCounts'        => $topRiskCounts,
            'yearLabels'           => $yearLabels,
            'yearCounts'           => $yearCounts,
            'attackLabels'         => $attackLabels,
            'attackCounts'         => $attackCounts,
            'stateCrimeIndexScore' => $rankingData['score'],
            'stateRank'            => $rankingData['rank'],
            'stateRankOrdinal'     => $rankingData['ordinal'],
            'crimeTable'           => $crimeTable,
            'automatedInsights'    => $automatedInsights,
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Lightweight AJAX endpoints
    // ──────────────────────────────────────────────────────────────────────────

    public function getTotalIncidentsOnly(string $state, $year)
    {
        return response()->json([
            'total_incidents' => tbldataentry::whereRaw('LOWER(location) = ?', [strtolower($state)])
                ->where('yy', $year)->count(),
        ]);
    }

    public function getIncidentLocations(Request $request, string $state, $year)
    {
        return response()->json(
            tbldataentry::select('lga_lat', 'lga_long', 'lga', 'add_notes', 'impact', 'datecreated')
                ->whereRaw('LOWER(location) = ?', [strtolower($state)])
                ->where('yy', $year)
                ->where('impact', 'High')
                ->whereNotNull('lga_lat')->whereNotNull('lga_long')
                ->where('lga_lat', '!=', '')->where('lga_long', '!=', '')
                ->get()
        );
    }

    public function getTop5Risks(string $state, $year)
    {
        $data = tbldataentry::select('riskindicators', DB::raw('COUNT(*) as occurrences'))
            ->whereRaw('LOWER(location) = ?', [strtolower($state)])
            ->where('yy', $year)
            ->where('riskindicators', '!=', '')
            ->groupBy('riskindicators')
            ->orderByDesc('occurrences')
            ->take(5)
            ->get();

        return response()->json([
            'labels' => $data->pluck('riskindicators')->values(),
            'counts' => $data->pluck('occurrences')->values(),
        ]);
    }

    public function getLgaIncidentCounts(string $state, $year)
    {
        return response()->json(
            tbldataentry::select('lga', DB::raw('COUNT(*) as incident_count'))
                ->whereRaw('LOWER(location) = ?', [strtolower($state)])
                ->where('yy', $year)
                ->where('lga', '!=', '')
                ->groupBy('lga')
                ->get()
                ->pluck('incident_count', 'lga')
        );
    }

    public function getComparisonRiskCounts(Request $request)
    {
        $state      = $request->input('state');
        $year       = $request->input('year');
        $indicators = $request->input('indicators');

        if (!$state || !$year || empty($indicators)) {
            return response()->json(['counts' => []]);
        }

        $this->enforceTier2LocationLock($state, $request);

        $data = tbldataentry::select('riskindicators', DB::raw('COUNT(*) as occurrences'))
            ->whereRaw('LOWER(location) = ?', [strtolower($state)])
            ->where('yy', $year)
            ->whereIn('riskindicators', $indicators)
            ->groupBy('riskindicators')
            ->get()
            ->pluck('occurrences', 'riskindicators');

        $orderedCounts = [];
        foreach ($indicators as $ind) {
            $orderedCounts[] = $data[$ind] ?? 0;
        }

        return response()->json(['counts' => $orderedCounts]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Tier enforcement
    // ──────────────────────────────────────────────────────────────────────────

    private function enforceTier2LocationLock(string $state, ?Request $request = null)
    {
        $user = auth()->user();
        if (!$user) return;
        if ((int) $user->tier !== 1) return;

        $stateKey = strtolower(trim($state));

        if (!$user->locked_location) {
            $user->locked_location              = $stateKey;
            $user->location_switch_available_at = now()->addMonth();
            $user->save();
            return;
        }

        if ($user->locked_location === $stateKey) return;

        $lockedUntil = $user->location_switch_available_at;
        if ($lockedUntil && now()->lt($lockedUntil)) {
            $payload = [
                'message'              => 'You can switch location once per month. Unlock all States and 774 LGAs with premium access.',
                'locked_location'      => $user->locked_location,
                'switch_available_at'  => $lockedUntil->toDateTimeString(),
                'upgrade'              => true,
            ];

            $req = $request ?? request();

            if ($req->expectsJson() || $req->ajax() || $req->header('X-Requested-With') === 'XMLHttpRequest') {
                abort(response()->json($payload, 403));
            }

            abort(redirect()->route('locationIntelligence', ['state' => $user->locked_location])
                ->with('tier_lock', $payload));
        }

        // Lock window expired — update to new state
        $user->locked_location              = $stateKey;
        $user->location_switch_available_at = now()->addMonth();
        $user->save();
    }
}
