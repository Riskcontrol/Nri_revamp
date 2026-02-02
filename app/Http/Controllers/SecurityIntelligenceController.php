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

class SecurityIntelligenceController extends Controller
{

    use CalculatesRisk;

    private $geopoliticalZones = [
        'North West' => ['Jigawa', 'Kaduna', 'Kano', 'Katsina', 'Kebbi', 'Sokoto', 'Zamfara'],
        'North East' => ['Adamawa', 'Bauchi', 'Borno', 'Gombe', 'Taraba', 'Yobe'],
        'North Central' => ['Benue', 'FCT', 'Kogi', 'Kwara', 'Nasarawa', 'Niger', 'Plateau'],
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
    public function getRiskData(Request $request)
    {
        $selectedYear  = (int) $request->input('year', now()->year);
        $selectedIndex = (string) $request->input('index_type', 'Composite Risk Index');

        $riskIndicator = $this->riskMapping[$selectedIndex] ?? 'All';

        // ✅ Composite uses a different engine; cache must not collide
        $mode = ($selectedIndex === 'Composite Risk Index') ? 'composite_rf' : 'indicator_engine';
        $cacheKey = "risk_data_{$selectedYear}_{$selectedIndex}_{$mode}";

        return Cache::remember($cacheKey, 3600, function () use ($selectedYear, $selectedIndex, $riskIndicator) {

            $indicatorFilter = ($riskIndicator === 'All') ? null : $riskIndicator;

            /**
             * -----------------------------
             * 1) CURRENT + PREVIOUS REPORTS
             * -----------------------------
             * Composite => OLD algorithm (riskfactors-based)
             * Others    => NEW engine (indicators-based)
             */
            if ($selectedIndex === 'Composite Risk Index') {

                // ✅ OLD composite algorithm (riskfactors + factor weights + corrections)
                $currentComposite = collect($this->calculateCompositeIndexByRiskFactors($selectedYear));
                $prevComposite    = collect($this->calculateCompositeIndexByRiskFactors($selectedYear - 1));

                // Ensure ranking works (highest first)
                $sortedCurrent = $currentComposite->sortDesc();
                $sortedPrev    = $prevComposite->sortDesc();

                // Total tracked incidents (all incidents in that year)
                $totalTrackedIncidents = (int) tbldataentry::where('yy', $selectedYear)->count();

                // Total fatalities (all fatalities in that year)
                $totalFatalities = (int) tbldataentry::where('yy', $selectedYear)->sum('Casualties_count');

                // Build "reports" arrays matching your existing structure
                $stateRiskReportsCurrent = $sortedCurrent->map(function ($score, $state) {
                    $score = (float) $score;
                    return [
                        'location' => $state,
                        // optional: set incident_count properly; we fill later via query
                        'incident_count' => 0,
                        'normalized_ratio' => round($score, 2),
                        'risk_level' => $this->determineBusinessRiskLevel($score),
                        'year' => now()->year,
                    ];
                })->values()->all();

                $stateRiskReportsPrev = $sortedPrev->map(function ($score, $state) {
                    $score = (float) $score;
                    return [
                        'location' => $state,
                        'incident_count' => 0,
                        'normalized_ratio' => round($score, 2),
                        'risk_level' => $this->determineBusinessRiskLevel($score),
                        'year' => now()->year,
                    ];
                })->values()->all();

                // ✅ Fill incident_count for composite (batch query)
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

                // ✅ NEW engine (indicators-based)
                $stateDataCurrent = $this->buildIndicatorAggregates($selectedYear, $indicatorFilter);
                $totalFatalities = (int) $stateDataCurrent->sum('total_deaths');
                $stateRiskReportsCurrent = $this->calculateStateRiskFromIndicators($stateDataCurrent);

                $previousYear = $selectedYear - 1;
                $stateDataPrev = $this->buildIndicatorAggregates($previousYear, $indicatorFilter);
                $stateRiskReportsPrev = $this->calculateStateRiskFromIndicators($stateDataPrev);

                $sortedCurrent = collect($stateRiskReportsCurrent)->sortByDesc('normalized_ratio');
                $totalTrackedIncidents = (int) $sortedCurrent->sum('incident_count');
            }

            // From here down, keep your existing logic exactly, but use the variables above
            $sortedReports = collect($stateRiskReportsCurrent)->sortByDesc('normalized_ratio');

            // 3) SORTING + RANKING (previous year ranks)
            $prevYearRanks = collect($stateRiskReportsPrev)
                ->sortByDesc('normalized_ratio')
                ->values()
                ->map(function ($report, $index) {
                    return [
                        'rank' => $index + 1,
                        'score' => $report['normalized_ratio'],
                        'location' => $report['location'],
                    ];
                })
                ->keyBy('location');

            $highestRiskReport = $sortedReports->first();

            $nationalThreatLevel = $highestRiskReport
                ? $this->getRiskCategoryFromLevel($highestRiskReport['risk_level'])
                : 'Low';

            // 4) TOP THREAT GROUPS (keep your original logic, but for Composite don't filter indicators)
            $topThreatGroups = 'N/A';
            try {
                $topThreatGroupsQuery = tbldataentry::join('attack_group', 'tbldataentry.attack_group_name', '=', 'attack_group.id')
                    ->select('attack_group.name', DB::raw('COUNT(*) as occurrences'))
                    ->where('tbldataentry.yy', $selectedYear)
                    ->where('attack_group.name', '!=', 'Others')
                    ->groupBy('attack_group.name')
                    ->orderByDesc('occurrences')
                    ->take(5);

                // ✅ Only apply indicator filter for non-composite indices
                if ($selectedIndex !== 'Composite Risk Index' && $indicatorFilter) {
                    $topThreatGroupsQuery->where('tbldataentry.riskindicators', $indicatorFilter);
                }

                $results = $topThreatGroupsQuery->get();
                if ($results->isNotEmpty()) {
                    $topThreatGroups = $results->pluck('name')->implode(', ');
                }
            } catch (\Exception $e) {
                Log::error("Failed to get Top Threat Groups: " . $e->getMessage());
            }

            // 5) FORMAT TREEMAP + TABLE
            $treemapData = [];
            $tableData = $sortedReports->values()->map(function ($report, $index) use ($prevYearRanks, &$treemapData) {
                $stateName = $report['location'];
                $currentRank = $index + 1;

                $prevData = $prevYearRanks->get($stateName);
                $previousRank = $prevData['rank'] ?? '-';

                $status = 'Stable';

                if ($prevData) {
                    $prevScore = $prevData['score'];
                    $currentScore = $report['normalized_ratio'];

                    if ($currentScore > $prevScore) {
                        $status = 'Escalating';
                    } elseif ($currentScore < $prevScore) {
                        $status = 'Improving';
                    }
                }

                $treemapData[] = [
                    'x' => $stateName,
                    'y' => $report['normalized_ratio'],
                ];

                return [
                    'state' => $stateName,
                    'risk_score' => round($report['normalized_ratio'], 2),
                    'risk_level' => $this->getRiskCategoryFromLevel($report['risk_level']),
                    'rank_current' => $currentRank,
                    'rank_previous' => $previousRank,
                    'status' => $status,
                    'incidents' => $report['incident_count'],
                ];
            });

            // 6) FATALITIES TREND
            $trendYears = range(2018, $selectedYear);

            $fatalityTrendQuery = tbldataentry::selectRaw('yy, SUM(Casualties_count) as total_deaths')
                ->whereIn('yy', $trendYears);

            // ✅ Only apply indicator filter for non-composite indices
            if ($selectedIndex !== 'Composite Risk Index' && $indicatorFilter) {
                $fatalityTrendQuery->where('riskindicators', $indicatorFilter);
            }

            $fatalityTrend = $fatalityTrendQuery
                ->groupBy('yy')
                ->orderBy('yy', 'asc')
                ->get()
                ->keyBy('yy');

            $trendLabels = [];
            $trendData = [];
            foreach ($trendYears as $y) {
                $trendLabels[] = (string) $y;
                $trendData[] = $fatalityTrend->has($y) ? (int) $fatalityTrend[$y]->total_deaths : 0;
            }

            return response()->json([
                'treemapSeries' => [['data' => $treemapData]],
                'tableData' => $tableData,
                'cardData' => [
                    'nationalThreatLevel' => $nationalThreatLevel,
                    'totalTrackedIncidents' => (int) ($totalTrackedIncidents ?? 0),
                    'topThreatGroups' => $topThreatGroups,
                    'totalFatalities' => (int) ($totalFatalities ?? 0),
                ],
                'trendSeries' => [
                    'labels' => $trendLabels,
                    'data' => $trendData,
                ],
            ]);
        });
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
}
