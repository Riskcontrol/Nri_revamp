<?php

namespace App\Http\Controllers;

use App\Models\tbldataentry;
use App\Models\CorrectionFactorForStates;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Http\Controllers\Traits\CalculatesRisk;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Services\SecurityInsightGenerator;
use App\Models\SecurityInsight;


class SecurityIntelligenceController extends Controller
{
    use CalculatesRisk;

    private $geopoliticalZones = [
        'North West'    => ['Jigawa', 'Kaduna', 'Kano', 'Katsina', 'Kebbi', 'Sokoto', 'Zamfara'],
        'North East'    => ['Adamawa', 'Bauchi', 'Borno', 'Gombe', 'Taraba', 'Yobe'],
        'North Central' => ['Benue', 'Federal Capital Territory', 'Kogi', 'Kwara', 'Nasarawa', 'Niger', 'Plateau'],
        'South West'    => ['Ekiti', 'Lagos', 'Ogun', 'Ondo', 'Osun', 'Oyo'],
        'South East'    => ['Abia', 'Anambra', 'Ebonyi', 'Enugu', 'Imo'],
        'South South'   => ['Akwa Ibom', 'Bayelsa', 'Cross River', 'Delta', 'Edo', 'Rivers'],
    ];

    private $riskMapping = [
        'Terrorism Index'      => 'Terrorism',
        'Kidnapping Index'     => 'Kidnapping',
        'Composite Risk Index' => 'All',
    ];

    // ──────────────────────────────────────────────────────────────────────────
    // Overview Page (Security Intelligence landing)
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * FIXED:
     *  - Entire method output cached for 30 min — was 13+ uncached queries per load
     *  - Duplicate queries #11 + #12 (topRisks + rawContributionData) consolidated
     *    into a single query, pivoted in PHP
     *  - Zone top-risk queries (was 2 per request inside map()) eliminated by
     *    pre-loading all risk counts per zone in one query
     */
    public function getOverview()
    {
        $startYear   = 2018;
        $currentYear = now()->year;

        $payload = Cache::remember("security_overview:{$currentYear}", 1800, function () use ($startYear, $currentYear) {

            // ── 1. Totals ──────────────────────────────────────────────────────
            $totalIncidents = tbldataentry::where('yy', '>=', $startYear)->count();
            $totalDeaths    = tbldataentry::where('yy', '>=', $startYear)->sum('Casualties_count');

            // ── 2. State data (one query — reused for zone + top-5 calc) ──────
            $stateData = tbldataentry::selectRaw('
                location,
                COUNT(*) as total_incidents,
                SUM(Casualties_count) as total_deaths,
                SUM(victim) as total_victims,
                MAX(yy) as yy
            ')
                ->where('yy', '>=', $startYear)
                ->groupBy('location')
                ->get();

            $top5States        = $stateData->sortByDesc('total_incidents')->take(5);
            $top5LocationNames = $top5States->pluck('location')->toArray();
            $previousYear      = $currentYear - 1;

            // ── 3. Year-over-year change for top 5 (two simple queries) ────────
            $baselineData = tbldataentry::selectRaw('location, COUNT(*) as count')
                ->where('yy', $previousYear)
                ->whereIn('location', $top5LocationNames)
                ->groupBy('location')
                ->pluck('count', 'location');

            $currentData = tbldataentry::selectRaw('location, COUNT(*) as count')
                ->where('yy', $currentYear)
                ->whereIn('location', $top5LocationNames)
                ->groupBy('location')
                ->pluck('count', 'location');

            $stateChangeLabels = [];
            $stateChangeData   = [];

            foreach ($top5States as $state) {
                $loc        = $state->location;
                $startCount = $baselineData[$loc] ?? 0;
                $endCount   = $currentData[$loc]   ?? 0;

                if ($startCount > 0) {
                    $percentChange = (($endCount - $startCount) / $startCount) * 100;
                } elseif ($endCount > 0) {
                    $percentChange = 100;
                } else {
                    $percentChange = 0;
                }

                $stateChangeLabels[] = $loc;
                $stateChangeData[]   = round($percentChange, 1);
            }

            // ── 4. Prominent risks ──────────────────────────────────────────────
            $trendingRiskFactors = tbldataentry::selectRaw('riskindicators, COUNT(*) as frequency')
                ->where('yy', '>=', $startYear)
                ->groupBy('riskindicators')
                ->orderByDesc('frequency')
                ->take(4)
                ->get();

            $prominentRisks = $trendingRiskFactors->pluck('riskindicators')->implode(', ');

            // ── 5. Zone data — derived in PHP from $stateData (no extra query) ─
            $zoneData = [];
            foreach ($stateData as $state) {
                $zone = $this->getGeopoliticalZone($state->location);
                if ($zone === 'Unknown') continue;

                if (!isset($zoneData[$zone])) {
                    $zoneData[$zone] = ['zone' => $zone, 'total_deaths' => 0];
                }
                $zoneData[$zone]['total_deaths'] += $state->total_deaths;
            }

            $sortedZones = collect($zoneData)->sortByDesc('total_deaths');

            // ── 6. Top risk per zone — ONE query replacing 2 zone-level queries ─
            $topRiskPerZone = $this->getTopRiskPerZone($startYear);

            $activeRegions = $sortedZones->take(2)->map(function ($regionData) use ($topRiskPerZone) {
                $regionData['top_risk'] = $topRiskPerZone[$regionData['zone']] ?? 'N/A';
                return $regionData;
            });

            // ── 7. Fatality trend (line chart) ─────────────────────────────────
            $fatalityData = tbldataentry::selectRaw('yy, SUM(Casualties_count) as total_deaths')
                ->where('yy', '>=', $startYear)
                ->groupBy('yy')
                ->orderBy('yy', 'asc')
                ->get()
                ->keyBy('yy');

            $trendLabels = [];
            $trendData   = [];
            foreach (range($startYear, $currentYear) as $chartYear) {
                $trendLabels[] = $chartYear;
                $trendData[]   = $fatalityData->get($chartYear)->total_deaths ?? 0;
            }

            // ── 8. Pie chart ────────────────────────────────────────────────────
            $regionChartLabels = $sortedZones->pluck('zone')->values();
            $regionChartData   = $sortedZones->pluck('total_deaths')->values();

            // ── 9. Bar chart (top 6 indicators) ────────────────────────────────
            $allRiskIndicators = tbldataentry::selectRaw('riskindicators, COUNT(*) as frequency')
                ->where('yy', '>=', $startYear)
                ->groupBy('riskindicators')
                ->orderByDesc('frequency')
                ->take(6)
                ->get();

            $riskIndicatorLabels = $allRiskIndicators->pluck('riskindicators')->toArray();
            $riskIndicatorData   = $allRiskIndicators->pluck('frequency')->toArray();

            // ── 10. Contribution chart — ONE query (was two: topRisks + raw) ───
            //   Old code: query A → top 5 risk names, query B → state × risk counts
            //   Fixed:    single query, derive both from the same result in PHP
            $rawContributionData = tbldataentry::select('riskindicators', 'location', DB::raw('COUNT(*) as count'))
                ->where('yy', '>=', $startYear)
                ->groupBy('riskindicators', 'location')
                ->orderByDesc('count')
                ->get();

            // Derive top 5 risks by total volume (PHP, no extra query)
            $topRisks = $rawContributionData
                ->groupBy('riskindicators')
                ->map(fn($rows) => $rows->sum('count'))
                ->sortDesc()
                ->take(5)
                ->keys();

            $riskLabels = $topRisks->toArray();

            $stateColors = ['#3B82F6', '#EF4444', '#10B981', '#F59E0B', '#8B5CF6', '#6B7280'];

            // Filter contribution data to only the top 5 risks
            $filteredContribution = $rawContributionData->filter(fn($r) => in_array($r->riskindicators, $riskLabels));

            // Pivot: risk → state → count
            $pivotData = [];
            foreach ($filteredContribution as $row) {
                $pivotData[$row->riskindicators][$row->location] = $row->count;
            }

            // Top 5 states by total count across all top risks
            $topStatesOverall = $filteredContribution
                ->groupBy('location')
                ->map(fn($rows) => $rows->sum('count'))
                ->sortDesc()
                ->take(5)
                ->keys()
                ->toArray();

            $contributionDatasets = [];
            foreach ($topStatesOverall as $index => $state) {
                $data = [];
                foreach ($riskLabels as $risk) {
                    $data[] = $pivotData[$risk][$state] ?? 0;
                }
                $contributionDatasets[] = [
                    'label'           => $state,
                    'data'            => $data,
                    'backgroundColor' => $stateColors[$index] ?? '#ccc',
                ];
            }

            // "Others" dataset
            $othersData = [];
            foreach ($riskLabels as $risk) {
                $totalRiskCount  = collect($pivotData[$risk] ?? [])->sum();
                $topStatesCount  = 0;
                foreach ($topStatesOverall as $state) {
                    $topStatesCount += $pivotData[$risk][$state] ?? 0;
                }
                $othersData[] = $totalRiskCount - $topStatesCount;
            }

            $contributionDatasets[] = [
                'label'           => 'Others',
                'data'            => $othersData,
                'backgroundColor' => '#9CA3AF',
            ];

            return compact(
                'totalIncidents',
                'totalDeaths',
                'prominentRisks',
                'activeRegions',
                'top5States',
                'trendLabels',
                'trendData',
                'regionChartLabels',
                'regionChartData',
                'riskIndicatorLabels',
                'riskIndicatorData',
                'startYear',
                'currentYear',
                'stateChangeLabels',
                'stateChangeData',
                'riskLabels',
                'contributionDatasets'
            );
        });

        return view('securityIntelligence', $payload);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Zone helpers
    // ──────────────────────────────────────────────────────────────────────────

    private function getGeopoliticalZone(string $state): string
    {
        $normalizedState = trim(ucwords(strtolower($state)));
        foreach ($this->geopoliticalZones as $zone => $states) {
            if (in_array($normalizedState, $states)) {
                return $zone;
            }
        }
        return 'Unknown';
    }

    private function getStatesForZone(string $zone): array
    {
        return $this->geopoliticalZones[$zone] ?? [];
    }

    /**
     * Returns the top risk indicator per geopolitical zone — ONE query.
     * Replaces the old pattern of one query per zone inside a map() callback.
     */
    private function getTopRiskPerZone(int $startYear): array
    {
        // Build a flat state→zone lookup
        $stateToZone = [];
        foreach ($this->geopoliticalZones as $zone => $states) {
            foreach ($states as $state) {
                $stateToZone[mb_strtolower(trim($state))] = $zone;
            }
        }

        // One query: all state × risk counts since startYear
        $rows = tbldataentry::selectRaw('location, riskindicators, COUNT(*) as cnt')
            ->where('yy', '>=', $startYear)
            ->groupBy('location', 'riskindicators')
            ->get();

        // Aggregate in PHP: zone → [risk → total_count]
        $zoneRiskCounts = [];
        foreach ($rows as $row) {
            $zone = $stateToZone[mb_strtolower(trim($row->location))] ?? null;
            if (!$zone) continue;

            $zoneRiskCounts[$zone][$row->riskindicators] = ($zoneRiskCounts[$zone][$row->riskindicators] ?? 0) + $row->cnt;
        }

        // Return top risk per zone
        $result = [];
        foreach ($zoneRiskCounts as $zone => $risks) {
            arsort($risks);
            $result[$zone] = array_key_first($risks) ?? 'N/A';
        }

        return $result;
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Preview endpoint (unauthenticated users)
    // ──────────────────────────────────────────────────────────────────────────

    public function getPreviewRiskData(Request $request)
    {
        $currentYear = now()->year;
        $cacheKey    = 'risk_data_preview:' . $currentYear;

        $payload = Cache::remember($cacheKey, 3600, function () use ($currentYear) {
            $currentComposite = collect($this->calculateCompositeIndexByRiskFactors($currentYear));
            $sortedCurrent    = $currentComposite->sortDesc();

            // Derive totals from composite calc's already-cached data
            $totalTrackedIncidents = (int) tbldataentry::where('yy', $currentYear)->count();
            $totalFatalities       = (int) tbldataentry::where('yy', $currentYear)->sum('Casualties_count');

            $treemapData = [];
            $tableData   = $sortedCurrent->map(function ($score, $state) use (&$treemapData) {
                $score2       = round((float) $score, 2);
                $treemapData[] = ['x' => $state, 'y' => $score];
                return [
                    'state'         => $state,
                    'risk_score'    => $score2,
                    'risk_level'    => $this->getRiskCategoryFromLevel($this->determineBusinessRiskLevel($score)),
                    'rank_current'  => '-',
                    'rank_previous' => '-',
                    'status'        => 'N/A',
                    'incidents'     => 0,
                ];
            })->values();

            $topThreatGroups = 'N/A';
            try {
                $results = tbldataentry::join('attack_group', 'tbldataentry.attack_group_name', '=', 'attack_group.id')
                    ->select('attack_group.name')
                    ->where('tbldataentry.yy', $currentYear)
                    ->where('attack_group.name', '!=', 'Others')
                    ->groupBy('attack_group.name')
                    ->orderByRaw('COUNT(*) DESC')
                    ->take(5)
                    ->get();

                if ($results->isNotEmpty()) {
                    $topThreatGroups = $results->pluck('name')->implode(', ');
                }
            } catch (\Throwable $e) {
                Log::error('Preview: Failed to get Top Threat Groups: ' . $e->getMessage());
            }

            return [
                'treemapSeries' => [['data' => $treemapData]],
                'tableData'     => $tableData,
                'cardData'      => [
                    'nationalThreatLevel'   => 'Medium',
                    'totalTrackedIncidents' => $totalTrackedIncidents,
                    'topThreatGroups'       => $topThreatGroups,
                    'totalFatalities'       => $totalFatalities,
                ],
                'trendSeries' => [
                    'labels' => [(string) $currentYear],
                    'data'   => [(int) $totalFatalities],
                ],
                'aiInsights' => [
                    ['title' => 'Preview Mode', 'text' => 'Sign in to access detailed insights and historical data.']
                ],
                'aiMeta' => ['source' => 'preview'],
            ];
        });

        return response()->json($payload);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Risk data AJAX (authenticated — treemap/table/AI insights)
    // ──────────────────────────────────────────────────────────────────────────

    public function getRiskData(Request $request, SecurityInsightGenerator $insightGen)
    {
        $selectedYear  = (int) $request->input('year', now()->year);
        $selectedIndex = (string) $request->input('index_type', 'Composite Risk Index');

        $user = $request->user();
        if ($user && (int) $user->tier === 1) {
            $currentYear = (int) now()->year;

            // Composite Risk Index is FREE for all years — never gate it.
            // Only gate: non-Composite indices, OR historical years on non-Composite.
            $isComposite    = $selectedIndex === 'Composite Risk Index';
            $isCurrentYear  = $selectedYear === $currentYear;

            // Tier2 rule:
            //   Composite Risk Index → allowed for ANY year (no restriction)
            //   Any other index      → only current year allowed
            $blocked = !$isComposite && !$isCurrentYear;

            if ($blocked) {
                $payload = [
                    'message' => 'Premium Access: Select other indexes and historical years with premium.',
                    'upgrade' => true,
                    'context' => 'risk',
                    'allowed' => ['index_type' => 'Terrorism Index', 'year' => $currentYear],
                ];

                if ($request->expectsJson() || $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                    return response()->json($payload, 403);
                }

                return redirect()->route('securityIntelligence')->with('tier_lock', $payload);
            }
        }

        $riskIndicator   = $this->riskMapping[$selectedIndex] ?? 'All';
        $indicatorFilter = ($riskIndicator === 'All') ? null : $riskIndicator;
        $mode            = ($selectedIndex === 'Composite Risk Index') ? 'composite_rf' : 'indicator_engine';

        $cacheKey = 'risk_data:' . $selectedYear . ':' . md5($selectedIndex . '|' . ($indicatorFilter ?? 'all') . '|' . $mode);

        $payload = Cache::remember($cacheKey, 3600, function () use (
            $selectedYear,
            $selectedIndex,
            $indicatorFilter,
            $mode,
            $insightGen
        ) {

            // ── 1. Current + previous year reports ────────────────────────────
            if ($selectedIndex === 'Composite Risk Index') {
                $currentComposite = collect($this->calculateCompositeIndexByRiskFactors($selectedYear));
                $prevComposite    = collect($this->calculateCompositeIndexByRiskFactors($selectedYear - 1));

                $sortedCurrent = $currentComposite->sortDesc();
                $sortedPrev    = $prevComposite->sortDesc();

                $totalTrackedIncidents = (int) tbldataentry::where('yy', $selectedYear)->count();
                $totalFatalities       = (int) tbldataentry::where('yy', $selectedYear)->sum('Casualties_count');

                $stateRiskReportsCurrent = $sortedCurrent->map(fn($score, $state) => [
                    'location'         => $state,
                    'incident_count'   => 0,
                    'normalized_ratio' => round((float) $score, 2),
                    'risk_level'       => $this->determineBusinessRiskLevel($score),
                    'year'             => $selectedYear,
                ])->values()->all();

                $stateRiskReportsPrev = $sortedPrev->map(fn($score, $state) => [
                    'location'         => $state,
                    'incident_count'   => 0,
                    'normalized_ratio' => round((float) $score, 2),
                    'risk_level'       => $this->determineBusinessRiskLevel($score),
                    'year'             => $selectedYear - 1,
                ])->values()->all();

                $incidentCounts = tbldataentry::selectRaw('TRIM(location) as location, COUNT(*) as cnt')
                    ->where('yy', $selectedYear)
                    ->groupBy(DB::raw('TRIM(location)'))
                    ->pluck('cnt', 'location');

                $stateRiskReportsCurrent = collect($stateRiskReportsCurrent)->map(function ($r) use ($incidentCounts) {
                    $r['incident_count'] = (int) ($incidentCounts[$r['location']] ?? 0);
                    return $r;
                })->all();
            } else {
                $stateDataCurrent = $this->buildIndicatorAggregates($selectedYear, $indicatorFilter);
                $totalFatalities  = (int) $stateDataCurrent->sum('total_deaths');
                $stateRiskReportsCurrent = $this->calculateStateRiskFromIndicators($stateDataCurrent);

                $stateDataPrev        = $this->buildIndicatorAggregates($selectedYear - 1, $indicatorFilter);
                $stateRiskReportsPrev = $this->calculateStateRiskFromIndicators($stateDataPrev);

                $sortedCurrent         = collect($stateRiskReportsCurrent)->sortByDesc('normalized_ratio');
                $totalTrackedIncidents = (int) $sortedCurrent->sum('incident_count');
            }

            $sortedReports = collect($stateRiskReportsCurrent)->sortByDesc('normalized_ratio');

            // ── 2. Previous-year ranks ─────────────────────────────────────────
            $prevYearRanks = collect($stateRiskReportsPrev)
                ->sortByDesc('normalized_ratio')
                ->values()
                ->map(fn($report, $index) => [
                    'rank'     => $index + 1,
                    'score'    => $report['normalized_ratio'],
                    'location' => $report['location'],
                ])
                ->keyBy('location');

            $highestRiskReport  = $sortedReports->first();
            $nationalThreatLevel = $highestRiskReport
                ? $this->getRiskCategoryFromLevel($highestRiskReport['risk_level'])
                : 'Low';

            // ── 3. Top threat groups ───────────────────────────────────────────
            $topThreatGroups = 'N/A';
            try {
                $q = tbldataentry::join('attack_group', 'tbldataentry.attack_group_name', '=', 'attack_group.id')
                    ->select('attack_group.name', DB::raw('COUNT(*) as occurrences'))
                    ->where('tbldataentry.yy', $selectedYear)
                    ->where('attack_group.name', '!=', 'Others')
                    ->groupBy('attack_group.name')
                    ->orderByDesc('occurrences')
                    ->take(5);

                if ($selectedIndex !== 'Composite Risk Index' && $indicatorFilter) {
                    $q->where('tbldataentry.riskindicators', $indicatorFilter);
                }

                $results = $q->get();
                if ($results->isNotEmpty()) {
                    $topThreatGroups = $results->pluck('name')->implode(', ');
                }
            } catch (\Throwable $e) {
                Log::error('Failed to get Top Threat Groups: ' . $e->getMessage());
            }

            // ── 4. Treemap + table ─────────────────────────────────────────────
            $treemapData = [];
            $sorted      = $sortedReports->values();

            $rank      = 0;
            $pos       = 0;
            $lastScore = null;

            $tableData = $sorted->map(function ($report) use ($prevYearRanks, &$treemapData, &$rank, &$pos, &$lastScore) {
                $pos++;
                $stateName = $report['location'];
                $score2    = number_format(round((float) ($report['normalized_ratio'] ?? 0), 2), 2, '.', '');

                if ($lastScore === null || $score2 !== $lastScore) {
                    $rank      = $pos;
                    $lastScore = $score2;
                }

                $prevData     = $prevYearRanks->get($stateName);
                $previousRank = $prevData['rank'] ?? '-';

                $status = 'Stable';
                if ($prevData) {
                    $prevScore2 = round((float) ($prevData['score'] ?? 0), 2);
                    if ($score2 > $prevScore2)      $status = 'Escalating';
                    elseif ($score2 < $prevScore2)  $status = 'Improving';
                }

                $treemapData[] = ['x' => $stateName, 'y' => $report['normalized_ratio']];

                return [
                    'state'         => $stateName,
                    'risk_score'    => $score2,
                    'risk_level'    => $this->getRiskCategoryFromLevel($report['risk_level']),
                    'rank_current'  => $rank,
                    'rank_previous' => $previousRank,
                    'status'        => $status,
                    'incidents'     => (int) ($report['incident_count'] ?? 0),
                ];
            });

            // ── 5. Fatality trend ──────────────────────────────────────────────
            $trendYears         = range(2018, $selectedYear);
            $fatalityTrendQuery = tbldataentry::selectRaw('yy, SUM(Casualties_count) as total_deaths')
                ->whereIn('yy', $trendYears);

            if ($selectedIndex !== 'Composite Risk Index' && $indicatorFilter) {
                $fatalityTrendQuery->where('riskindicators', $indicatorFilter);
            }

            $fatalityTrend = $fatalityTrendQuery->groupBy('yy')->orderBy('yy', 'asc')->get()->keyBy('yy');

            $trendLabels = [];
            $trendData   = [];
            foreach ($trendYears as $y) {
                $trendLabels[] = (string) $y;
                $trendData[]   = $fatalityTrend->has($y) ? (int) $fatalityTrend[$y]->total_deaths : 0;
            }

            // ── 6. AI Insights ─────────────────────────────────────────────────
            $indexType = $selectedIndex;

            $zoneDeaths = $this->buildZoneImpact($selectedYear, $indicatorFilter);

            $topRiskPerZoneYear = $this->getTopRiskPerZoneForYear($selectedYear, $indicatorFilter);

            $activeRegions = collect($zoneDeaths)
                ->sortByDesc('fatalities')
                ->take(2)
                ->map(function ($z) use ($topRiskPerZoneYear) {
                    return [
                        'zone'        => $z['zone'] ?? 'N/A',
                        'total_deaths' => (int) ($z['fatalities'] ?? 0),
                        'top_risk'    => $topRiskPerZoneYear[$z['zone'] ?? ''] ?? 'N/A',
                    ];
                })
                ->values()
                ->all();

            $summary = $this->buildInsightSummary(
                $indexType,
                $selectedYear,
                $tableData->toArray(),
                $trendLabels,
                $trendData,
                $activeRegions,
                ''
            );

            $latestAudit  = tbldataentry::where('yy', $selectedYear)->max('audittimecreated');
            $summaryHash  = hash('sha256', json_encode($summary));

            $aiCacheKey = 'ai_insights:' . $selectedYear . ':' . md5(
                $indexType . '|' . ($indicatorFilter ?? 'all') . '|' . $mode . '|' . $summaryHash . '|' . ($latestAudit ?? 'na')
            );

            $aiBlock = null;

            try {
                $q = SecurityInsight::where('year', $selectedYear)
                    ->where('index_type', $indexType)
                    ->where('hash', $summaryHash);

                if ($indicatorFilter === null) $q->whereNull('indicator');
                else $q->where('indicator', $indicatorFilter);

                $row = $q->first();

                if ($row && !empty($row->insights)) {
                    $aiBlock = [
                        'insights' => $row->insights,
                        'meta'     => ['source' => $row->source ?? 'unknown', 'cached' => true, 'model' => $row->model ?? null],
                    ];
                }
            } catch (\Throwable $e) {
                Log::error('DB_INSIGHT_LOOKUP_FAILED', ['year' => $selectedYear, 'index_type' => $indexType, 'error' => $e->getMessage()]);
            }

            if (!$aiBlock) {
                $cached = Cache::get($aiCacheKey);
                if (is_array($cached) && isset($cached['insights'], $cached['meta'])) {
                    $aiBlock = $cached;
                } else {
                    $result   = $insightGen->generateWithMeta($indexType, $selectedYear, $summary);
                    $insights = $result['insights'] ?? [];
                    $aiMeta   = $result['meta']     ?? [];
                    $source   = $aiMeta['source']   ?? 'unknown';

                    $aiBlock = ['insights' => $insights, 'meta' => $aiMeta];

                    try {
                        SecurityInsight::updateOrCreate(
                            ['year' => $selectedYear, 'index_type' => $indexType, 'indicator' => $indicatorFilter],
                            [
                                'summary'      => $summary,
                                'insights'     => $insights,
                                'hash'         => $summaryHash,
                                'model'        => config('services.groq.model') ?? 'groq',
                                'generated_at' => now(),
                                'source'       => $source,
                            ]
                        );
                    } catch (\Throwable $e) {
                        // non-fatal
                    }

                    Cache::put($aiCacheKey, $aiBlock, ($source === 'groq') ? 86400 : 300);
                }
            }

            return [
                'treemapSeries' => [['data' => $treemapData]],
                'tableData'     => $tableData,
                'cardData'      => [
                    'nationalThreatLevel'   => $nationalThreatLevel,
                    'totalTrackedIncidents' => (int) ($totalTrackedIncidents ?? 0),
                    'topThreatGroups'       => $topThreatGroups,
                    'totalFatalities'       => (int) ($totalFatalities ?? 0),
                ],
                'trendSeries' => ['labels' => $trendLabels, 'data' => $trendData],
                'aiInsights'  => $aiBlock['insights'] ?? [],
                'aiMeta'      => $aiBlock['meta']     ?? ['source' => 'unknown'],
            ];
        });

        return response()->json($payload);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Private helpers
    // ──────────────────────────────────────────────────────────────────────────

    private function getRiskCategoryFromLevel($level): string
    {
        return match ((int) $level) {
            1 => 'Low',
            2 => 'Medium',
            3 => 'High',
            4 => 'Very High',
            default => 'N/A',
        };
    }

    /**
     * Pre-loads top risk per zone for a specific year+indicator in ONE query.
     * Replaces the old pattern that fired one query per zone inside a map() callback.
     */
    private function getTopRiskPerZoneForYear(int $year, ?string $indicatorFilter): array
    {
        $stateToZone = [];
        foreach ($this->geopoliticalZones as $zone => $states) {
            foreach ($states as $state) {
                $stateToZone[mb_strtolower(trim($state))] = $zone;
            }
        }

        $q = tbldataentry::selectRaw('location, riskindicators, COUNT(*) as cnt')
            ->where('yy', $year);

        if ($indicatorFilter) {
            $q->where('riskindicators', $indicatorFilter);
        }

        $rows = $q->groupBy('location', 'riskindicators')->get();

        $zoneRiskCounts = [];
        foreach ($rows as $row) {
            $zone = $stateToZone[mb_strtolower(trim($row->location))] ?? null;
            if (!$zone) continue;
            $zoneRiskCounts[$zone][$row->riskindicators] = ($zoneRiskCounts[$zone][$row->riskindicators] ?? 0) + $row->cnt;
        }

        $result = [];
        foreach ($zoneRiskCounts as $zone => $risks) {
            arsort($risks);
            $result[$zone] = array_key_first($risks) ?? 'N/A';
        }

        return $result;
    }

    private function buildInsightSummary(
        string $indexType,
        int $year,
        array  $tableData,
        array $trendLabels,
        array $trendData,
        array  $activeRegions,
        string $prominentRisks
    ): array {
        $fmt2 = fn($n) => (float) number_format((float) $n, 2, '.', '');
        $fmt1 = fn($n) => (float) number_format((float) $n, 1, '.', '');

        $topStates = collect($tableData)
            ->sortByDesc(fn($r) => (float) ($r['risk_score'] ?? 0))
            ->take(5)
            ->map(fn($r) => [
                'state'         => (string) ($r['state'] ?? 'N/A'),
                'risk_score'    => $fmt2($r['risk_score'] ?? 0),
                'rank_current'  => (int) ($r['rank_current'] ?? 0),
                'rank_previous' => $r['rank_previous'] ?? '-',
                'status'        => (string) ($r['status'] ?? 'Stable'),
                'incidents'     => (int) ($r['incidents'] ?? 0),
            ])
            ->values()
            ->all();

        $labels  = collect($trendLabels)->map(fn($x) => (int) $x)->values();
        $idxThis = $labels->search($year);
        $idxPrev = $labels->search($year - 1);

        $thisDeaths = ($idxThis !== false) ? (int) ($trendData[$idxThis] ?? 0) : 0;
        $prevDeaths = ($idxPrev !== false) ? (int) ($trendData[$idxPrev] ?? 0) : 0;

        $yoyPct = ($prevDeaths > 0)
            ? (($thisDeaths - $prevDeaths) / $prevDeaths) * 100
            : (($thisDeaths > 0) ? 100.0 : 0.0);

        $topZones = collect($activeRegions)->take(2)->map(fn($z) => [
            'zone'        => (string) ($z['zone'] ?? 'N/A'),
            'total_deaths' => (int) ($z['total_deaths'] ?? 0),
            'top_risk'    => (string) ($z['top_risk'] ?? 'N/A'),
        ])->values()->all();

        return [
            'index_type'          => $indexType,
            'year'                => $year,
            'top_states'          => $topStates,
            'top_zones'           => $topZones,
            'prominent_risks'     => $prominentRisks,
            'national_fatalities' => [
                'this_year'        => $thisDeaths,
                'previous_year'    => $prevDeaths,
                'yoy_change_pct'   => $fmt1($yoyPct),
            ],
        ];
    }

    private function buildZoneImpact(int $year, ?string $indicatorFilter): array
    {
        $stateToZone = [];
        foreach ($this->geopoliticalZones as $zone => $states) {
            foreach ($states as $state) {
                $stateToZone[mb_strtolower(trim($state))] = $zone;
            }
        }

        $rows = tbldataentry::query()
            ->where('yy', $year)
            ->when($indicatorFilter, fn($q) => $q->where('riskindicators', $indicatorFilter))
            ->selectRaw('location as state, SUM(Casualties_count) as fatalities')
            ->groupBy('location')
            ->get();

        $zones = [];
        foreach ($rows as $r) {
            $zone = $stateToZone[mb_strtolower(trim((string) $r->state))] ?? null;
            if (!$zone) continue;
            $zones[$zone] = ($zones[$zone] ?? 0) + (int) ($r->fatalities ?? 0);
        }

        $out = [];
        foreach ($zones as $zone => $fatalities) {
            $out[] = ['zone' => $zone, 'fatalities' => $fatalities];
        }

        return $out;
    }
}
