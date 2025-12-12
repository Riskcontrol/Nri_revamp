<?php

namespace App\Http\Controllers;

use App\Models\tbldataentry;
use App\Models\CorrectionFactorForStates;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Http\Controllers\Traits\CalculatesRisk;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
                $zoneData[$zone] = ['zone' => $zone, 'total_incidents' => 0];
            }
            $zoneData[$zone]['total_incidents'] += $state->total_incidents;
        }
        $sortedZones = collect($zoneData)->sortByDesc('total_incidents');

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

        // 8. Line Chart Data
        $incidentData = tbldataentry::selectRaw('yy, COUNT(*) as incident_count')
            ->where('yy', '>=', $startYear)->groupBy('yy')->orderBy('yy', 'asc')->get()->keyBy('yy');

        $trendLabels = [];
        $trendData = [];
        foreach (range($startYear, $currentYear) as $chartYear) {
            $trendLabels[] = $chartYear;
            $trendData[] = $incidentData->get($chartYear)->incident_count ?? 0;
        }

        // 9. Pie Chart Data
        $regionChartLabels = $sortedZones->pluck('zone')->values();
        $regionChartData = $sortedZones->pluck('total_incidents')->values();

        // 10. Bar Chart Data (Indicators) - MODIFIED: Removed "Others" logic
        $allRiskIndicators = tbldataentry::selectRaw('riskindicators, COUNT(*) as frequency')
            ->where('yy', '>=', $startYear)
            ->groupBy('riskindicators')
            ->orderByDesc('frequency')
            ->take(6) // Simply take the top 6 directly
            ->get();

        $riskIndicatorLabels = $allRiskIndicators->pluck('riskindicators')->toArray();
        $riskIndicatorData = $allRiskIndicators->pluck('frequency')->toArray();

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
            'stateChangeData'
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
        // ... (Your existing getRiskData logic) ...
        // Note: I kept getRiskData as is since the request was specifically about the "Hot Zones" box html provided.
        // If you wanted Active Threat Groups reduced to 2 as well, verify that section.

        $selectedYear = $request->input('year', now()->year);
        $selectedIndex = $request->input('index_type', 'Composite Risk Index');
        $riskIndicator = $this->riskMapping[$selectedIndex] ?? 'All';

        $baseQueryCurrent = tbldataentry::where('yy', $selectedYear)
                                        ->whereNotNull('location')
                                        ->where('location', '!=', '');

        if ($riskIndicator !== 'All') {
            $baseQueryCurrent->where('riskindicators', $riskIndicator);
        }

        $stateDataCurrent = $baseQueryCurrent
            ->selectRaw('location, COUNT(*) as total_incidents, SUM(Casualties_count) as total_deaths, SUM(victim) as total_victims, MAX(yy) as yy')
            ->groupBy('location')
            ->get();

        $stateRiskReportsCurrent = $this->calculateStateRisk($stateDataCurrent);

        $previousYear = $selectedYear - 1;
        $baseQueryPrev = tbldataentry::where('yy', $previousYear)
                                        ->whereNotNull('location')
                                        ->where('location', '!=', '');

        if ($riskIndicator !== 'All') {
            $baseQueryPrev->where('riskindicators', $riskIndicator);
        }

        $stateDataPrev = $baseQueryPrev
            ->selectRaw('location, COUNT(*) as total_incidents, SUM(Casualties_count) as total_deaths, SUM(victim) as total_victims, MAX(yy) as yy')
            ->groupBy('location')
            ->get();

        $stateRiskReportsPrev = $this->calculateStateRisk($stateDataPrev);

        $prevYearRanks = collect($stateRiskReportsPrev)
            ->sortByDesc('normalized_ratio')
            ->values()
            ->map(function ($report, $index) {
                return [
                    'rank' => $index + 1,
                    'score' => $report['normalized_ratio'],
                    'location' => $report['location']
                ];
            })
            ->keyBy('location');

        $sortedReports = collect($stateRiskReportsCurrent)->sortByDesc('normalized_ratio');
        $highestRiskReport = $sortedReports->first();
        $nationalThreatLevel = $highestRiskReport ? $this->getRiskCategoryFromLevel($highestRiskReport['risk_level']) : 'Low';
        $totalTrackedIncidents = $sortedReports->sum('incident_count');
        $previousYearTotalIncidents = collect($stateRiskReportsPrev)->sum('incident_count');
        $incidentTrendDifference = $totalTrackedIncidents - $previousYearTotalIncidents;

        $incidentTrendStatus = 'Stable';
        if ($incidentTrendDifference > 0) {
            $incidentTrendStatus = 'Escalating';
        } elseif ($incidentTrendDifference < 0) {
            $incidentTrendStatus = 'Improving';
        }

        $topThreatGroups = 'N/A';
        try {
            $topThreatGroupsQuery = tbldataentry::join('attack_group', 'tbldataentry.attack_group_name', '=', 'attack_group.id')
                ->select('attack_group.name', DB::raw('COUNT(*) as occurrences'))
                ->where('tbldataentry.yy', $selectedYear)
                ->where('attack_group.name', '!=', 'Others')
                ->groupBy('attack_group.name')
                ->orderByDesc('occurrences')
                ->take(5);

            if ($riskIndicator !== 'All') {
                $topThreatGroupsQuery->where('tbldataentry.riskindicators', $riskIndicator);
            }

            $results = $topThreatGroupsQuery->get();
            if ($results->isNotEmpty()) {
                $topThreatGroups = $results->pluck('name')->implode(', ');
            }
        } catch (\Exception $e) {
            Log::error("Failed to get Top Threat Groups: " . $e->getMessage());
        }

        $treemapData = [];
        $tableData = collect($stateRiskReportsCurrent)
            ->sortByDesc('normalized_ratio')
            ->values()
            ->map(function ($report, $index) use ($prevYearRanks, &$treemapData) {
                $stateName = $report['location'];
                $currentRank = $index + 1;
                $prevData = $prevYearRanks->get($stateName);
                $previousRank = $prevData['rank'] ?? 50;

                $status = 'Stable';
                if ($currentRank < $previousRank) {
                    $status = 'Escalating';
                } elseif ($currentRank > $previousRank) {
                    $status = 'Improving';
                }

                $treemapData[] = [
                    'x' => $stateName,
                    'y' => $report['normalized_ratio']
                ];

                return [
                    'state' => $stateName,
                    'risk_score' => round($report['normalized_ratio'], 2),
                    'risk_level' => $this->getRiskCategoryFromLevel($report['risk_level']),
                    'rank_current' => $currentRank,
                    'rank_previous' => $prevData ? $previousRank : 'N/A',
                    'status' => $status,
                    'incidents' => $report['incident_count']
                ];
            });

        return response()->json([
            'treemapSeries' => [['data' => $treemapData]],
            'tableData' => $tableData,
            'cardData' => [
                'nationalThreatLevel' => $nationalThreatLevel,
                'totalTrackedIncidents' => $totalTrackedIncidents,
                'topThreatGroups' => $topThreatGroups,
                'incidentTrendDifference' => $incidentTrendDifference,
                'incidentTrendStatus' => $incidentTrendStatus
            ]
        ]);
    }

    private function getRiskCategoryFromLevel($level)
    {
        switch ($level) {
            case 1: return 'Low';
            case 2: return 'Medium';
            case 3: return 'High';
            case 4: return 'Critical';
            default: return 'N/A';
        }
    }
}
