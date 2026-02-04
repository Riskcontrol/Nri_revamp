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
        'North West' => ['Jigawa', 'Kaduna', 'Kano', 'Katsina', 'Kebbi', 'Sokoto', 'Zamfara'],
        'North East' => ['Adamawa', 'Bauchi', 'Borno', 'Gombe', 'Taraba', 'Yobe'],
        'North Central' => ['Benue', 'Federal Capital Territory', 'Kogi', 'Kwara', 'Nasarawa', 'Niger', 'Plateau'],
        'South West' => ['Ekiti', 'Lagos', 'Ogun', 'Ondo', 'Osun', 'Oyo'],
        'South East' => ['Abia', 'Anambra', 'Ebonyi', 'Enugu', 'Imo'],
        'South South' => ['Akwa Ibom', 'Bayelsa', 'Cross River', 'Delta', 'Edo', 'Rivers'],
    ];

    private $riskMapping = [
        'Terrorism Index' => 'Terrorism',
        'Kidnapping Index' => 'Kidnapping',
        'Composite Risk Index' => 'All'
    ];

    public function getOverview()
    {
        // 1. Define Date Range
        $startYear = 2018;
        $currentYear = now()->year;

        // 2. Total Incidents (Cumulative)
        $totalIncidents = tbldataentry::where('yy', '>=', $startYear)->count();

        // 3. Total Fatalities
        $totalDeaths = tbldataentry::where('yy', '>=', $startYear)->sum('Casualties_count');

        // 4. State Data (Cumulative)
        $stateData = tbldataentry::selectRaw('location, COUNT(*) as total_incidents, SUM(Casualties_count) as total_deaths, SUM(victim) as total_victims, MAX(yy) as yy')
            ->where('yy', '>=', $startYear)
            ->groupBy('location')
            ->get();

        // 5. Top 5 States
        $top5States = $stateData->sortByDesc('total_incidents')->take(5);

        // --- Compare Previous Year vs Current Year for Top 5 ---
        $top5LocationNames = $top5States->pluck('location')->toArray();
        $previousYear = $currentYear - 1;

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
        $stateChangeData = [];

        foreach ($top5States as $state) {
            $loc = $state->location;
            $startCount = $baselineData[$loc] ?? 0;
            $endCount = $currentData[$loc] ?? 0;

            if ($startCount > 0) {
                $percentChange = (($endCount - $startCount) / $startCount) * 100;
            } elseif ($endCount > 0) {
                $percentChange = 100;
            } else {
                $percentChange = 0;
            }

            $stateChangeLabels[] = $loc;
            $stateChangeData[] = round($percentChange, 1);
        }

        // 6. Prominent Risks
        $trendingRiskFactors = tbldataentry::selectRaw('riskindicators, COUNT(*) as frequency')
            ->where('yy', '>=', $startYear)
            ->groupBy('riskindicators')
            ->orderByDesc('frequency')
            ->take(4)
            ->get();
        $prominentRisks = $trendingRiskFactors->pluck('riskindicators')->implode(', ');

        // 7. Zone Data
        $zoneData = [];
        foreach ($stateData as $state) {
            $zone = $this->getGeopoliticalZone($state->location);
            if ($zone === 'Unknown') continue;

            if (!isset($zoneData[$zone])) {
                // Initialize with 'total_deaths'
                $zoneData[$zone] = ['zone' => $zone, 'total_deaths' => 0];
            }
            // Add state deaths to zone total
            $zoneData[$zone]['total_deaths'] += $state->total_deaths;
        }
        // Sort zones by highest fatalities
        $sortedZones = collect($zoneData)->sortByDesc('total_deaths');

        $activeRegions = $sortedZones->take(2)->map(function ($regionData) use ($startYear) {
            $statesInZone = $this->getStatesForZone($regionData['zone']);
            $topRisk = 'N/A';
            if (!empty($statesInZone)) {
                $topRisk = tbldataentry::select('riskindicators')
                    ->where('yy', '>=', $startYear)->whereIn('location', $statesInZone)
                    ->groupBy('riskindicators')->orderByRaw('COUNT(*) DESC')->take(1)->value('riskindicators');
            }
            $regionData['top_risk'] = $topRisk ?? 'N/A';
            return $regionData;
        });

        // 8. Line Chart Data (Updated to Track FATALITIES)
        $fatalityData = tbldataentry::selectRaw('yy, SUM(Casualties_count) as total_deaths') // Summing deaths
            ->where('yy', '>=', $startYear)
            ->groupBy('yy')
            ->orderBy('yy', 'asc')
            ->get()
            ->keyBy('yy');

        $trendLabels = [];
        $trendData = [];
        foreach (range($startYear, $currentYear) as $chartYear) {
            $trendLabels[] = $chartYear;
            $trendData[] = $fatalityData->get($chartYear)->total_deaths ?? 0; // Using total_deaths
        }

        // 9. Pie Chart Data
        $regionChartLabels = $sortedZones->pluck('zone')->values();
        $regionChartData = $sortedZones->pluck('total_deaths')->values();

        // 10. Bar Chart Data (Indicators) - MODIFIED: Removed "Others" logic
        $allRiskIndicators = tbldataentry::selectRaw('riskindicators, COUNT(*) as frequency')
            ->where('yy', '>=', $startYear)
            ->groupBy('riskindicators')
            ->orderByDesc('frequency')
            ->take(6) // Simply take the top 6 directly
            ->get();

        $riskIndicatorLabels = $allRiskIndicators->pluck('riskindicators')->toArray();
        $riskIndicatorData = $allRiskIndicators->pluck('frequency')->toArray();

        // 1. Identify Top 5 Recurring Risks (by volume)
        $topRisks = tbldataentry::select('riskindicators', DB::raw('COUNT(*) as count'))
            ->where('yy', '>=', $startYear)
            ->groupBy('riskindicators')
            ->orderByDesc('count')
            ->take(5)
            ->pluck('riskindicators'); // e.g., ['Kidnapping', 'Terrorism', 'Banditry'...]

        // 2. Get Breakdown of States for these Risks
        $rawContributionData = tbldataentry::select('riskindicators', 'location', DB::raw('COUNT(*) as count'))
            ->where('yy', '>=', $startYear)
            ->whereIn('riskindicators', $topRisks)
            ->groupBy('riskindicators', 'location')
            ->get();

        // 3. Process Data for Chart.js (Group small states into "Others")
        $contributionDatasets = [];
        $riskLabels = $topRisks->toArray();
        $stateColors = [
            '#3B82F6',
            '#EF4444',
            '#10B981',
            '#F59E0B',
            '#8B5CF6',
            '#6B7280' // Blue, Red, Green, Yellow, Purple, Gray
        ];

        // We need to pivot the data: Rows = Risks, Cols = States
        $pivotData = [];
        foreach ($rawContributionData as $row) {
            $pivotData[$row->riskindicators][$row->location] = $row->count;
        }

        // Identify top contributing states across ALL these risks to keep colors consistent
        $topStatesOverall = tbldataentry::select('location', DB::raw('COUNT(*) as count'))
            ->where('yy', '>=', $startYear)
            ->whereIn('riskindicators', $topRisks)
            ->groupBy('location')
            ->orderByDesc('count')
            ->take(5)
            ->pluck('location')
            ->toArray();

        // Build Datasets for the Top 5 States
        foreach ($topStatesOverall as $index => $state) {
            $data = [];
            foreach ($riskLabels as $risk) {
                // Calculate percentage or raw count (Raw count is usually better for stacked bars, let Chart.js handle tooltip %)
                $count = $pivotData[$risk][$state] ?? 0;
                $data[] = $count;
            }

            $contributionDatasets[] = [
                'label' => $state,
                'data' => $data,
                'backgroundColor' => $stateColors[$index] ?? '#ccc',
            ];
        }

        // Build "Others" Dataset
        $othersData = [];
        foreach ($riskLabels as $risk) {
            $totalRiskCount = $rawContributionData->where('riskindicators', $risk)->sum('count');
            $topStatesCount = 0;
            foreach ($topStatesOverall as $state) {
                $topStatesCount += $pivotData[$risk][$state] ?? 0;
            }
            $othersData[] = $totalRiskCount - $topStatesCount;
        }

        $contributionDatasets[] = [
            'label' => 'Others',
            'data' => $othersData,
            'backgroundColor' => '#9CA3AF', // Gray for others
        ];

        return view('securityIntelligence', compact(
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
        ));
    }

    // ... (Helper functions and getRiskData remain unchanged) ...
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
    public function getRiskData(Request $request, SecurityInsightGenerator $insightGen)
    {
        $selectedYear  = (int) $request->input('year', now()->year);
        $selectedIndex = (string) $request->input('index_type', 'Composite Risk Index');

        $riskIndicator   = $this->riskMapping[$selectedIndex] ?? 'All';
        $indicatorFilter = ($riskIndicator === 'All') ? null : $riskIndicator;

        $mode = ($selectedIndex === 'Composite Risk Index') ? 'composite_rf' : 'indicator_engine';

        // Cache key for the computed risk payload (chart/table/etc.)
        $cacheKey = 'risk_data:' . $selectedYear . ':' . md5($selectedIndex . '|' . ($indicatorFilter ?? 'all') . '|' . $mode);

        $payload = Cache::remember($cacheKey, 3600, function () use (
            $selectedYear,
            $selectedIndex,
            $indicatorFilter,
            $mode,
            $insightGen
        ) {
            // -----------------------------
            // 1) Build CURRENT + PREVIOUS reports
            // -----------------------------
            if ($selectedIndex === 'Composite Risk Index') {
                $currentComposite = collect($this->calculateCompositeIndexByRiskFactors($selectedYear));
                $prevComposite    = collect($this->calculateCompositeIndexByRiskFactors($selectedYear - 1));

                $sortedCurrent = $currentComposite->sortDesc();
                $sortedPrev    = $prevComposite->sortDesc();

                $totalTrackedIncidents = (int) tbldataentry::where('yy', $selectedYear)->count();
                $totalFatalities       = (int) tbldataentry::where('yy', $selectedYear)->sum('Casualties_count');

                $stateRiskReportsCurrent = $sortedCurrent->map(function ($score, $state) use ($selectedYear) {
                    $score = (float) $score;
                    return [
                        'location'         => $state,
                        'incident_count'   => 0,
                        'normalized_ratio' => round($score, 2),
                        'risk_level'       => $this->determineBusinessRiskLevel($score),
                        'year'             => $selectedYear,
                    ];
                })->values()->all();

                $stateRiskReportsPrev = $sortedPrev->map(function ($score, $state) use ($selectedYear) {
                    $score = (float) $score;
                    return [
                        'location'         => $state,
                        'incident_count'   => 0,
                        'normalized_ratio' => round($score, 2),
                        'risk_level'       => $this->determineBusinessRiskLevel($score),
                        'year'             => $selectedYear - 1,
                    ];
                })->values()->all();

                $incidentCounts = tbldataentry::selectRaw('TRIM(location) as location, COUNT(*) as cnt')
                    ->where('yy', $selectedYear)
                    ->groupBy(DB::raw('TRIM(location)'))
                    ->pluck('cnt', 'location');

                $stateRiskReportsCurrent = collect($stateRiskReportsCurrent)->map(function ($r) use ($incidentCounts) {
                    $loc = $r['location'];
                    $r['incident_count'] = (int) ($incidentCounts[$loc] ?? 0);
                    return $r;
                })->all();
            } else {
                $stateDataCurrent = $this->buildIndicatorAggregates($selectedYear, $indicatorFilter);
                $totalFatalities  = (int) $stateDataCurrent->sum('total_deaths');
                $stateRiskReportsCurrent = $this->calculateStateRiskFromIndicators($stateDataCurrent);

                $previousYear = $selectedYear - 1;
                $stateDataPrev = $this->buildIndicatorAggregates($previousYear, $indicatorFilter);
                $stateRiskReportsPrev = $this->calculateStateRiskFromIndicators($stateDataPrev);

                $sortedCurrent = collect($stateRiskReportsCurrent)->sortByDesc('normalized_ratio');
                $totalTrackedIncidents = (int) $sortedCurrent->sum('incident_count');
            }

            $sortedReports = collect($stateRiskReportsCurrent)->sortByDesc('normalized_ratio');

            // -----------------------------
            // 2) prev year ranks
            // -----------------------------
            $prevYearRanks = collect($stateRiskReportsPrev)
                ->sortByDesc('normalized_ratio')
                ->values()
                ->map(function ($report, $index) {
                    return [
                        'rank'     => $index + 1,
                        'score'    => $report['normalized_ratio'],
                        'location' => $report['location'],
                    ];
                })
                ->keyBy('location');

            $highestRiskReport = $sortedReports->first();
            $nationalThreatLevel = $highestRiskReport
                ? $this->getRiskCategoryFromLevel($highestRiskReport['risk_level'])
                : 'Low';

            // -----------------------------
            // 3) top threat groups
            // -----------------------------
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
                Log::error("Failed to get Top Threat Groups: " . $e->getMessage());
            }

            // -----------------------------
            // 4) treemap + table
            // -----------------------------
            $treemapData = [];
            $tableData = $sortedReports->values()->map(function ($report, $index) use ($prevYearRanks, &$treemapData) {
                $stateName   = $report['location'];
                $currentRank = $index + 1;

                $prevData      = $prevYearRanks->get($stateName);
                $previousRank  = $prevData['rank'] ?? '-';

                $status = 'Stable';
                if ($prevData) {
                    $prevScore    = $prevData['score'];
                    $currentScore = $report['normalized_ratio'];
                    if ($currentScore > $prevScore) $status = 'Escalating';
                    elseif ($currentScore < $prevScore) $status = 'Improving';
                }

                $treemapData[] = ['x' => $stateName, 'y' => $report['normalized_ratio']];

                return [
                    'state'         => $stateName,
                    'risk_score'    => round($report['normalized_ratio'], 2),
                    'risk_level'    => $this->getRiskCategoryFromLevel($report['risk_level']),
                    'rank_current'  => $currentRank,
                    'rank_previous' => $previousRank,
                    'status'        => $status,
                    'incidents'     => $report['incident_count'],
                ];
            });

            // -----------------------------
            // 5) fatalities trend
            // -----------------------------
            $trendYears = range(2018, $selectedYear);

            $fatalityTrendQuery = tbldataentry::selectRaw('yy, SUM(Casualties_count) as total_deaths')
                ->whereIn('yy', $trendYears);

            if ($selectedIndex !== 'Composite Risk Index' && $indicatorFilter) {
                $fatalityTrendQuery->where('riskindicators', $indicatorFilter);
            }

            $fatalityTrend = $fatalityTrendQuery
                ->groupBy('yy')
                ->orderBy('yy', 'asc')
                ->get()
                ->keyBy('yy');

            $trendLabels = [];
            $trendData   = [];
            foreach ($trendYears as $y) {
                $trendLabels[] = (string) $y;
                $trendData[]   = $fatalityTrend->has($y) ? (int) $fatalityTrend[$y]->total_deaths : 0;
            }

            // -----------------------------
            // 6) AI INSIGHTS (fixed)
            // -----------------------------
            $indexType = $selectedIndex;

            $zoneDeaths = $this->buildZoneImpact($selectedYear, $indicatorFilter);

            $activeRegions = collect($zoneDeaths)
                ->sortByDesc('fatalities')
                ->take(2)
                ->map(function ($z) use ($selectedYear, $indicatorFilter) {
                    $statesInZone = $this->getStatesForZone($z['zone'] ?? '');
                    $topRisk = 'N/A';

                    if (!empty($statesInZone)) {
                        $topRiskQuery = tbldataentry::query()
                            ->select('riskindicators')
                            ->where('yy', $selectedYear)
                            ->whereIn('location', $statesInZone);

                        if ($indicatorFilter) $topRiskQuery->where('riskindicators', $indicatorFilter);

                        $topRisk = $topRiskQuery
                            ->groupBy('riskindicators')
                            ->orderByRaw('COUNT(*) DESC')
                            ->value('riskindicators') ?? 'N/A';
                    }

                    return [
                        'zone'        => $z['zone'] ?? 'N/A',
                        'total_deaths' => (int) ($z['fatalities'] ?? 0),
                        'top_risk'    => $topRisk,
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
                $topThreatGroups
            );

            // fingerprint for storage/cache
            $latestAudit = tbldataentry::where('yy', $selectedYear)->max('audittimecreated');
            $summaryHash = hash('sha256', json_encode($summary));

            $aiCacheKey = 'ai_insights:' . $selectedYear . ':' . md5(
                $indexType . '|' . ($indicatorFilter ?? 'all') . '|' . $mode . '|' . $summaryHash . '|' . ($latestAudit ?? 'na')
            );

            // 6A) DB FIRST
            try {
                $q = SecurityInsight::where('year', $selectedYear)
                    ->where('index_type', $indexType)
                    ->where('hash', $summaryHash);

                if ($indicatorFilter === null) $q->whereNull('indicator');
                else $q->where('indicator', $indicatorFilter);

                $row = $q->first();

                if ($row && !empty($row->insights)) {
                    $rowSource = $row->source ?? 'unknown';
                    $aiBlock = [
                        'insights' => $row->insights,
                        'meta' => [
                            'source' => $rowSource,
                            'cached' => true,
                            'model'  => $row->model ?? null,
                        ],
                    ];
                }
            } catch (\Throwable $e) {
                \Log::error('DB_INSIGHT_LOOKUP_FAILED', [
                    'year' => $selectedYear,
                    'index_type' => $indexType,
                    'error' => $e->getMessage(),
                ]);
            }

            // 6B) CACHE / GENERATE IF NOT FOUND IN DB
            if (!isset($aiBlock)) {
                // Try cache
                $cached = Cache::get($aiCacheKey);
                if (is_array($cached) && isset($cached['insights'], $cached['meta'])) {
                    $aiBlock = $cached;
                } else {
                    // call groq
                    $result  = $insightGen->generateWithMeta($indexType, $selectedYear, $summary);
                    $insights = $result['insights'] ?? [];
                    $aiMeta   = $result['meta'] ?? [];
                    $source   = $aiMeta['source'] ?? 'unknown';

                    $aiBlock = [
                        'insights' => $insights,
                        'meta'     => $aiMeta,
                    ];

                    // SAVE TO DB (always, but store source so you can distinguish later)
                    try {
                        SecurityInsight::updateOrCreate(
                            [
                                'year'       => $selectedYear,
                                'index_type'  => $indexType,
                                'indicator'   => $indicatorFilter,
                            ],
                            [
                                'summary'     => $summary,
                                'insights'    => $insights,
                                'hash'        => $summaryHash,
                                'model'       => config('services.groq.model') ?? 'groq',
                                'generated_at' => now(),
                                'source'      => $source, // make sure column exists
                            ]
                        );
                    } catch (\Throwable $e) {
                        // ignore storage errors
                    }

                    // CACHE RULE:
                    // - cache groq for long
                    // - cache fallback briefly so transient failures don't lock you into fallback for 24h
                    $ttlSeconds = ($source === 'groq') ? 86400 : 300; // 24h vs 5min (tune to taste)
                    Cache::put($aiCacheKey, $aiBlock, $ttlSeconds);
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
                'trendSeries' => [
                    'labels' => $trendLabels,
                    'data'   => $trendData,
                ],
                'aiInsights' => $aiBlock['insights'] ?? [],
                'aiMeta'     => $aiBlock['meta'] ?? ['source' => 'unknown'],
            ];
        });

        return response()->json($payload);
    }





    private function getRiskCategoryFromLevel($level)
    {
        switch ($level) {
            case 1:
                return 'Low';
            case 2:
                return 'Medium';
            case 3:
                return 'High';
            case 4:
                return 'Very High';
            default:
                return 'N/A';
        }
    }


    private function buildInsightSummary(
        string $indexType,
        int $year,
        array $tableData,
        array $trendLabels,
        array $trendData,
        array $activeRegions,
        string $prominentRisks
    ): array {
        // Top states (from tableData)
        $topStates = collect($tableData)
            ->sortByDesc('risk_score')
            ->take(5)
            ->map(fn($r) => [
                'state' => $r['state'],
                'risk_score' => $r['risk_score'],
                'rank_current' => $r['rank_current'],
                'rank_previous' => $r['rank_previous'],
                'status' => $r['status'],
                'incidents' => $r['incidents'],
            ])
            ->values()
            ->all();

        // YOY fatalities % change (national)
        $labels = collect($trendLabels)->map(fn($x) => (int)$x)->values();
        $idxThis = $labels->search($year);
        $idxPrev = $labels->search($year - 1);

        $thisDeaths = ($idxThis !== false) ? (int)($trendData[$idxThis] ?? 0) : 0;
        $prevDeaths = ($idxPrev !== false) ? (int)($trendData[$idxPrev] ?? 0) : 0;

        $yoyPct = ($prevDeaths > 0)
            ? round((($thisDeaths - $prevDeaths) / $prevDeaths) * 100, 1)
            : (($thisDeaths > 0) ? 100.0 : 0.0);

        // Top zones (activeRegions already based on fatalities in your getOverview)
        $topZones = collect($activeRegions)->take(2)->map(fn($z) => [
            'zone' => $z['zone'] ?? 'N/A',
            'total_deaths' => $z['total_deaths'] ?? 0,
            'top_risk' => $z['top_risk'] ?? 'N/A',
        ])->values()->all();

        return [
            'index_type' => $indexType,
            'year' => $year,
            'top_states' => $topStates,
            'top_zones' => $topZones,
            'prominent_risks' => $prominentRisks,
            'national_fatalities' => [
                'this_year' => $thisDeaths,
                'previous_year' => $prevDeaths,
                'yoy_change_pct' => $yoyPct,
            ],
        ];
    }

    private function buildZoneImpact(int $year, ?string $indicatorFilter): array
    {
        // Map states to zones using your $this->geopoliticalZones array
        $stateToZone = [];
        foreach ($this->geopoliticalZones as $zone => $states) {
            foreach ($states as $state) {
                $stateToZone[mb_strtolower(trim($state))] = $zone;
            }
        }

        // Pull state-level totals
        $rows = tbldataentry::query()
            ->where('yy', $year)
            ->when($indicatorFilter, function ($q) use ($indicatorFilter) {
                // Adjust this column name if your DB uses something else
                $q->where('riskindicators', $indicatorFilter);
            })
            ->selectRaw("location as state, SUM(Casualties_count) as fatalities")
            ->groupBy('location')
            ->get();

        // Aggregate into zones
        $zones = [];
        foreach ($rows as $r) {
            $stateKey = mb_strtolower(trim((string) $r->state));
            $zone = $stateToZone[$stateKey] ?? null;
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
