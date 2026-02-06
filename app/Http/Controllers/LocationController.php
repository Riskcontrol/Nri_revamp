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

class LocationController extends Controller
{
    use CalculatesRisk, GeneratesInsights;

    /**
     * Crime Index indicators (by author=crimeIndex)
     */
    private function getCrimeIndexIndicators()
    {
        try {
            return tblriskindicators::where('author', 'crimeIndex')
                ->orderByRaw('CAST(indicators AS CHAR) ASC')
                ->pluck('indicators');
        } catch (\Exception $e) {
            Log::error("CRITICAL: Could not fetch crime indicators. " . $e->getMessage());
            return collect();
        }
    }

    /**
     * ✅ FIXED: trait expects death_correction, not casualty_correction
     */
    private function getStateCorrectionFactors($state)
    {
        $factors = CorrectionFactorForStates::where('state', $state)->first();

        if (!$factors) {
            return (object)[
                'incident_correction' => 1,
                'victim_correction'   => 1,
                'death_correction'    => 1,
            ];
        }

        return $factors;
    }

    /**
     * ✅ NEW: Build state reports (normalized_ratio, risk_level, incident_count) using indicator engine.
     * This is how we ensure Location page uses the same non-composite logic.
     */
    private function buildCrimeIndicatorReports(int $year): array
    {
        $crimeIndicators = $this->getCrimeIndexIndicators();

        if ($crimeIndicators->isEmpty()) {
            return [];
        }

        $data = DB::table('tbldataentry')
            ->where('yy', $year)
            ->whereNotNull('location')
            ->where('location', '!=', '')
            ->whereIn('riskindicators', $crimeIndicators->all())
            ->selectRaw('
                TRIM(location) as location,
                yy,
                TRIM(riskindicators) as risk_indicator,
                COUNT(*) as total_incidents,
                COALESCE(SUM(victim),0) as total_victims,
                COALESCE(SUM(Casualties_count),0) as total_deaths
            ')
            ->groupBy(DB::raw('TRIM(location)'), 'yy', DB::raw('TRIM(riskindicators)'))
            ->get();

        return $this->calculateStateRiskFromIndicators($data);
    }

    /**
     * ✅ NEW: Tie-aware ranking (dense rank: 1,2,2,3...)
     * Score returned is the indicator-engine normalized_ratio (already rounded to 2dp by the trait).
     */
    private function calculateRankAndScore($targetState, $year)
    {
        $reports = $this->buildCrimeIndicatorReports((int) $year);

        if (empty($reports)) {
            return ['score' => 0, 'rank' => 'N/A', 'ordinal' => '', 'total_states' => 0];
        }

        // normalize state name same way the trait stores it (trim whitespace)
        $targetKey = trim(preg_replace('/\s+/', ' ', (string) $targetState));

        $rows = collect($reports)
            ->values()
            ->sortByDesc(fn($r) => (float) ($r['normalized_ratio'] ?? 0))
            ->values();

        $ranked = [];
        $rank = 0;
        $prevScore = null;

        foreach ($rows as $r) {
            $score = (float) ($r['normalized_ratio'] ?? 0);

            if ($prevScore === null || $score !== $prevScore) {
                $rank++; // dense rank
                $prevScore = $score;
            }

            $ranked[$r['location']] = [
                'rank'  => $rank,
                'score' => round($score, 2), // safe (trait already rounds)
            ];
        }

        $target = $ranked[$targetKey] ?? null;

        if (!$target) {
            return ['score' => 0, 'rank' => 'N/A', 'ordinal' => '', 'total_states' => count($ranked)];
        }

        $ordinal = $this->ordinalSuffix((int) $target['rank']);

        return [
            'score'        => $target['score'],
            'rank'         => $target['rank'],
            'ordinal'      => $ordinal,
            'total_states' => count($ranked),
        ];
    }

    private function ordinalSuffix(int $n): string
    {
        if (!in_array(($n % 100), [11, 12, 13], true)) {
            switch ($n % 10) {
                case 1:
                    return 'st';
                case 2:
                    return 'nd';
                case 3:
                    return 'rd';
            }
        }
        return 'th';
    }

    /**
     * MAIN VIEW
     */
    public function getTotalIncident($state, $year = null)
    {
        $year = (int) ($year ?: now()->year);
        $availableYears = range(now()->year, 2018);
        $states = StateInsight::all();

        if (!in_array($year, $availableYears, true)) {
            $year = now()->year;
        }

        $total_incidents = tbldataentry::whereRaw('LOWER(location) = ?', [strtolower($state)])
            ->where('yy', $year)
            ->count();

        $mostFrequentRisk = tbldataentry::select('riskindicators', DB::raw('COUNT(*) as occurrences'))
            ->whereRaw('LOWER(location) = ?', [strtolower($state)])
            ->where('yy', $year)
            ->groupBy('riskindicators')
            ->orderByDesc('occurrences')
            ->take(2)
            ->get();

        // ✅ UPDATED: Month names mapping for display
        $monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

        // ✅ UPDATED: Determine end month
        $endMonth = ((int) $year === (int) now()->year) ? (int) now()->format('n') : 12;

        // ✅ UPDATED: Get numeric months (01-12) for querying
        $numericMonths = collect(range(1, $endMonth))
            ->map(fn($m) => str_pad($m, 2, '0', STR_PAD_LEFT)) // Convert to '01', '02', etc.
            ->values();

        // ✅ UPDATED: Query using mm column instead of month_pro
        $monthlyData = tbldataentry::selectRaw('mm, COUNT(*) as total_incidents')
            ->whereRaw('LOWER(location) = ?', [strtolower($state)])
            ->where('yy', (int) $year)
            ->whereIn('mm', $numericMonths->all())
            ->groupBy('mm')
            ->get()
            ->keyBy('mm');

        // ✅ UPDATED: Chart labels use month names
        $chartLabels = collect(range(1, $endMonth))
            ->map(fn($m) => $monthNames[$m - 1])
            ->values();

        // ✅ UPDATED: Map numeric months to incident counts
        $incidentCounts = $numericMonths->map(function ($mm) use ($monthlyData) {
            return (int) ($monthlyData[$mm]->total_incidents ?? 0);
        })->values();

        $topRiskIndicators = tbldataentry::select('riskindicators', DB::raw('COUNT(*) as occurrences'))
            ->whereRaw('LOWER(location) = ?', [strtolower($state)])
            ->where('yy', $year)
            ->groupBy('riskindicators')
            ->orderByDesc('occurrences')
            ->take(5)
            ->get();

        $topRiskLabels = $topRiskIndicators->pluck('riskindicators')->toArray();
        $topRiskCounts = $topRiskIndicators->pluck('occurrences')->toArray();

        $yearlyData = tbldataentry::selectRaw('yy, COUNT(*) as total_incidents')
            ->whereRaw('LOWER(location) = ?', [strtolower($state)])
            ->where('yy', '>=', 2018)
            ->groupBy('yy')
            ->orderBy('yy', 'asc')
            ->get();

        $yearLabels = $yearlyData->pluck('yy')->toArray();
        $yearCounts = $yearlyData->pluck('total_incidents')->toArray();

        $motiveData = tbldataentry::join('motives_specific', 'tbldataentry.motive_specific', '=', 'motives_specific.id')
            ->select('motives_specific.name', DB::raw('COUNT(*) as occurrences'))
            ->whereRaw('LOWER(tbldataentry.location) = ?', [strtolower($state)])
            ->where('tbldataentry.yy', $year)
            ->whereRaw('LOWER(motives_specific.name) != ?', ['others'])
            ->groupBy('motives_specific.name')
            ->orderByDesc('occurrences')
            ->take(5)
            ->get();

        $motiveLabels = $motiveData->pluck('name')->toArray();
        $motiveCounts = $motiveData->pluck('occurrences')->toArray();

        $attackData = tbldataentry::join('attack_group', 'tbldataentry.attack_group_name', '=', 'attack_group.id')
            ->select('attack_group.name', DB::raw('COUNT(*) as occurrences'))
            ->whereRaw('LOWER(tbldataentry.location) = ?', [strtolower($state)])
            ->where('tbldataentry.yy', $year)
            ->whereRaw('LOWER(attack_group.name) != ?', ['others'])
            ->groupBy('attack_group.name')
            ->orderByDesc('occurrences')
            ->take(5)
            ->get();

        $attackLabels = $attackData->pluck('name')->toArray();
        $attackCounts = $attackData->pluck('occurrences')->toArray();

        $mostAffectedLGA = tbldataentry::select('lga', DB::raw('COUNT(*) as occurrences'))
            ->whereRaw('LOWER(location) = ?', [strtolower($state)])
            ->where('yy', $year)
            ->where('lga', '!=', '')
            ->groupBy('lga')
            ->orderByDesc('occurrences')
            ->first();

        $recentIncidents = tbldataentry::select('lga', 'add_notes', 'riskindicators', 'impact', 'datecreated')
            ->whereRaw('LOWER(location) = ?', [strtolower($state)])
            ->orderBy('datecreated', 'desc')
            ->limit(5)
            ->get();

        // keep your Carbon sort if your stored format is "M d, Y"
        $recentIncidents = $recentIncidents->sortByDesc(function ($incident) {
            try {
                return \Carbon\Carbon::createFromFormat('M d, Y', $incident->datecreated)->timestamp;
            } catch (\Throwable $e) {
                return 0;
            }
        })->take(5);

        $getStates = StateInsight::pluck('state');

        /**
         * Crime table breakdown (counts per crime indicator for the state)
         * ✅ keep this logic: it is a breakdown table, not the score engine
         */
        $crimeIndicators = $this->getCrimeIndexIndicators();
        $crimeTable = [];

        if ($crimeIndicators->isNotEmpty()) {
            $currentStateIndicators = tbldataentry::select('riskindicators', DB::raw('COUNT(*) as incident_count'))
                ->whereRaw('LOWER(location) = ?', [strtolower($state)])
                ->where('yy', $year)
                ->whereIn('riskindicators', $crimeIndicators)
                ->groupBy('riskindicators')
                ->get()
                ->keyBy('riskindicators');

            $previousStateIndicators = tbldataentry::select('riskindicators', DB::raw('COUNT(*) as incident_count'))
                ->whereRaw('LOWER(location) = ?', [strtolower($state)])
                ->where('yy', $year - 1)
                ->whereIn('riskindicators', $crimeIndicators)
                ->groupBy('riskindicators')
                ->get()
                ->keyBy('riskindicators');

            foreach ($crimeIndicators as $indicator) {
                $currentCount = $currentStateIndicators->get($indicator)->incident_count ?? 0;
                $previousCount = $previousStateIndicators->get($indicator)->incident_count ?? 0;

                if ($currentCount > 0) {
                    $status = 'Stable';
                    if ($currentCount > $previousCount) $status = 'Escalating';
                    elseif ($currentCount < $previousCount) $status = 'Improving';

                    $crimeTable[] = [
                        'indicator_name'       => $indicator,
                        'incident_count'       => $currentCount,
                        'previous_year_count'  => $previousCount,
                        'status'               => $status,
                    ];
                }
            }

            $crimeTable = collect($crimeTable)->sortByDesc('incident_count')->values()->all();
        }

        // --- Insights (your existing trait) ---
        $trendInsights = $this->calculateTrendInsights($state, $year);
        $lethalityInsight = $this->calculateLethalityInsights($state, $year);
        $forecastInsight = $this->calculateForecast($state);

        $automatedInsights = [
            $trendInsights['velocity'] ?? null,
            $trendInsights['emerging'] ?? null,
            $lethalityInsight,
            $forecastInsight,
        ];
        $automatedInsights = array_values(array_filter($automatedInsights));

        /**
         * ✅ UPDATED: Score + Rank come from indicator engine (non-composite)
         */
        $rankingData = $this->calculateRankAndScore($state, $year);
        $stateCrimeIndexScore = $rankingData['score'];
        $stateRank = $rankingData['rank'];
        $stateRankOrdinal = $rankingData['ordinal'];

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
            'stateRankOrdinal'
        ));
    }

    /**
     * AJAX DATA
     */
    public function getStateData(Request $request, $state, $year)
    {
        $total_incidents = tbldataentry::whereRaw('LOWER(location) = ?', [strtolower($state)])
            ->where('yy', $year)
            ->count();

        $mostFrequentRisk = tbldataentry::select('riskindicators', DB::raw('COUNT(*) as occurrences'))
            ->whereRaw('LOWER(location) = ?', [strtolower($state)])
            ->where('yy', $year)
            ->groupBy('riskindicators')
            ->orderByDesc('occurrences')
            ->take(2)
            ->get();

        $mostAffectedLGA = tbldataentry::select('lga', DB::raw('COUNT(*) as occurrences'))
            ->whereRaw('LOWER(location) = ?', [strtolower($state)])
            ->where('yy', $year)
            ->where('lga', '!=', '')
            ->groupBy('lga')
            ->orderByDesc('occurrences')
            ->first();

        $recentIncidents = tbldataentry::select('lga', 'add_notes', 'riskindicators', 'impact', 'datecreated')
            ->whereRaw('LOWER(location) = ?', [strtolower($state)])
            ->orderBy('datecreated', 'desc')
            ->limit(5)
            ->get();

        $topRiskIndicators = tbldataentry::select('riskindicators', DB::raw('COUNT(*) as occurrences'))
            ->whereRaw('LOWER(location) = ?', [strtolower($state)])
            ->where('yy', $year)
            ->groupBy('riskindicators')
            ->orderByDesc('occurrences')
            ->take(5)
            ->get();

        $topRiskLabels = $topRiskIndicators->pluck('riskindicators')->values();
        $topRiskCounts = $topRiskIndicators->pluck('occurrences')->values();

        // ✅ UPDATED: Month names for display
        $monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $endMonth = ((int) $year === (int) now()->year) ? (int) now()->format('n') : 12;

        // ✅ UPDATED: Get numeric months for querying
        $numericMonths = collect(range(1, $endMonth))
            ->map(fn($m) => str_pad($m, 2, '0', STR_PAD_LEFT))
            ->values();

        // ✅ UPDATED: Query using mm column
        $monthlyDataRaw = tbldataentry::selectRaw('mm, COUNT(*) as total_incidents')
            ->whereRaw('LOWER(location) = ?', [strtolower($state)])
            ->where('yy', (int) $year)
            ->whereIn('mm', $numericMonths->all())
            ->groupBy('mm')
            ->get()
            ->keyBy('mm');

        // ✅ UPDATED: Chart labels use month names
        $chartLabels = collect(range(1, $endMonth))
            ->map(fn($m) => $monthNames[$m - 1])
            ->values()
            ->toArray();

        // ✅ UPDATED: Map numeric months to counts
        $incidentCounts = $numericMonths->map(function ($mm) use ($monthlyDataRaw) {
            return (int) ($monthlyDataRaw[$mm]->total_incidents ?? 0);
        })->values()->toArray();

        $yearlyData = tbldataentry::selectRaw('yy, COUNT(*) as total_incidents')
            ->whereRaw('LOWER(location) = ?', [strtolower($state)])
            ->where('yy', '>=', 2018)
            ->groupBy('yy')
            ->orderBy('yy', 'asc')
            ->get();

        $yearLabels = $yearlyData->pluck('yy')->values();
        $yearCounts = $yearlyData->pluck('total_incidents')->values();

        $attackData = tbldataentry::join('attack_group', 'tbldataentry.attack_group_name', '=', 'attack_group.id')
            ->select('attack_group.name', DB::raw('COUNT(*) as occurrences'))
            ->whereRaw('LOWER(tbldataentry.location) = ?', [strtolower($state)])
            ->where('tbldataentry.yy', $year)
            ->whereRaw('LOWER(attack_group.name) != ?', ['others'])
            ->groupBy('attack_group.name')
            ->orderByDesc('occurrences')
            ->take(5)
            ->get();

        $attackLabels = $attackData->pluck('name')->toArray();
        $attackCounts = $attackData->pluck('occurrences')->toArray();

        // Crime indicator table (breakdown)
        $crimeIndicators = $this->getCrimeIndexIndicators();
        $crimeTable = [];

        if ($crimeIndicators->isNotEmpty()) {
            $currentStateIndicators = tbldataentry::select('riskindicators', DB::raw('COUNT(*) as incident_count'))
                ->whereRaw('LOWER(location) = ?', [strtolower($state)])
                ->where('yy', $year)
                ->whereIn('riskindicators', $crimeIndicators)
                ->groupBy('riskindicators')
                ->get()
                ->keyBy('riskindicators');

            $previousStateIndicators = tbldataentry::select('riskindicators', DB::raw('COUNT(*) as incident_count'))
                ->whereRaw('LOWER(location) = ?', [strtolower($state)])
                ->where('yy', $year - 1)
                ->whereIn('riskindicators', $crimeIndicators)
                ->groupBy('riskindicators')
                ->get()
                ->keyBy('riskindicators');

            foreach ($crimeIndicators as $indicator) {
                $currentCount = $currentStateIndicators->get($indicator)->incident_count ?? 0;
                $previousCount = $previousStateIndicators->get($indicator)->incident_count ?? 0;

                if ($currentCount > 0) {
                    $status = 'Stable';
                    if ($currentCount > $previousCount) $status = 'Escalating';
                    elseif ($currentCount < $previousCount) $status = 'Improving';

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

        $trendInsights = $this->calculateTrendInsights($state, $year);
        $lethalityInsight = $this->calculateLethalityInsights($state, $year);
        $forecastInsight = $this->calculateForecast($state);

        $automatedInsights = [
            $trendInsights['velocity'] ?? null,
            $trendInsights['emerging'] ?? null,
            $lethalityInsight,
            $forecastInsight,
        ];
        $automatedInsights = array_values(array_filter($automatedInsights));

        /**
         * ✅ UPDATED: engine-based score + tie-aware rank
         */
        $rankingData = $this->calculateRankAndScore($state, $year);

        return response()->json([
            'total_incidents'       => $total_incidents,
            'mostFrequentRisk'      => $mostFrequentRisk,
            'mostAffectedLGA'       => $mostAffectedLGA,
            'recentIncidents'       => $recentIncidents,
            'chartLabels'           => $chartLabels,
            'incidentCounts'        => $incidentCounts,
            'topRiskLabels'         => $topRiskLabels,
            'topRiskCounts'         => $topRiskCounts,
            'yearLabels'            => $yearLabels,
            'yearCounts'            => $yearCounts,
            'attackLabels'          => $attackLabels,
            'attackCounts'          => $attackCounts,
            'stateCrimeIndexScore'  => $rankingData['score'],
            'stateRank'             => $rankingData['rank'],
            'stateRankOrdinal'      => $rankingData['ordinal'],
            'crimeTable'            => $crimeTable,
            'automatedInsights'     => $automatedInsights,
        ]);
    }

    public function getTotalIncidentsOnly($state, $year)
    {
        $total_incidents = tbldataentry::whereRaw('LOWER(location) = ?', [strtolower($state)])
            ->where('yy', $year)
            ->count();

        return response()->json([
            'total_incidents' => $total_incidents,
        ]);
    }

    public function getIncidentLocations(Request $request, $state, $year)
    {
        $locations = tbldataentry::select('lga_lat', 'lga_long', 'lga', 'add_notes', 'impact', 'datecreated')
            ->whereRaw('LOWER(location) = ?', [strtolower($state)])
            ->where('yy', $year)
            ->where('impact', 'High')
            ->whereNotNull('lga_lat')
            ->whereNotNull('lga_long')
            ->where('lga_lat', '!=', '')
            ->where('lga_long', '!=', '')
            ->get();

        return response()->json($locations);
    }

    public function getTop5Risks($state, $year)
    {
        $topRiskIndicators = tbldataentry::select('riskindicators', DB::raw('COUNT(*) as occurrences'))
            ->whereRaw('LOWER(location) = ?', [strtolower($state)])
            ->where('yy', $year)
            ->where('riskindicators', '!=', '')
            ->groupBy('riskindicators')
            ->orderByDesc('occurrences')
            ->take(5)
            ->get();

        return response()->json([
            'labels' => $topRiskIndicators->pluck('riskindicators')->values(),
            'counts' => $topRiskIndicators->pluck('occurrences')->values(),
        ]);
    }

    public function getLgaIncidentCounts($state, $year)
    {
        $lgaCounts = tbldataentry::select('lga', DB::raw('COUNT(*) as incident_count'))
            ->whereRaw('LOWER(location) = ?', [strtolower($state)])
            ->where('yy', $year)
            ->where('lga', '!=', '')
            ->groupBy('lga')
            ->get()
            ->pluck('incident_count', 'lga');

        return response()->json($lgaCounts);
    }

    public function getComparisonRiskCounts(Request $request)
    {
        $state = $request->input('state');
        $year = $request->input('year');
        $indicators = $request->input('indicators');

        if (!$state || !$year || empty($indicators)) {
            return response()->json(['counts' => []]);
        }

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

        return response()->json([
            'counts' => $orderedCounts,
        ]);
    }
}
