<?php

namespace App\Http\Controllers;
use App\Models\tbldataentry;
use App\Models\CorrectionFactorForStates;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Http\Controllers\Traits\CalculatesRisk;
use Illuminate\Support\Facades\DB;


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

  public function getOverview()
    {
        $year = now()->year;

        // 1. Total Incidents (Card)
        $totalIncidents = tbldataentry::where('yy', $year)->count();

        // 2. State Data (Raw)
        $stateData = tbldataentry::selectRaw('location, COUNT(*) as total_incidents, SUM(Casualties_count) as total_deaths, SUM(victim) as total_victims, MAX(yy) as yy')
                            ->where('yy', $year)
                            ->groupBy('location')
                            ->get();

        // 3. Top 5 States (List)
        $top5States = $stateData->sortByDesc('total_incidents')->take(5);

        // 4. Active Risk Zones (Card)
        $stateRiskReports = $this->calculateStateRisk($stateData);
        $highRiskStates = collect($stateRiskReports)->filter(function ($report) {
            return in_array($report['risk_level'], [3, 4]);
        });
        $activeRiskZones = $highRiskStates->count();

        // 5. Prominent Risks (Card)
        $trendingRiskFactors = tbldataentry::selectRaw('riskindicators, COUNT(*) as frequency')
            ->where('yy', $year)
            ->groupBy('riskindicators')
            ->orderByDesc('frequency')
            ->take(4)
            ->get();
        $prominentRisks = $trendingRiskFactors->pluck('riskindicators')->implode(', ');


        $zoneData = [];
        foreach ($stateData as $state) {
            $zone = $this->getGeopoliticalZone($state->location);
            if ($zone === 'Unknown') continue;
            if (!isset($zoneData[$zone])) {
                $zoneData[$zone] = ['zone' => $zone, 'total_incidents' => 0, 'total_deaths' => 0, 'total_victims' => 0];
            }
            $zoneData[$zone]['total_incidents'] += $state->total_incidents;
            $zoneData[$zone]['total_deaths'] += $state->total_deaths;
            $zoneData[$zone]['total_victims'] += $state->total_victims;
        }

        $sortedZones = collect($zoneData)->sortByDesc('total_incidents');
        $topActiveRegionsData = $sortedZones->take(6); // For the card

        $activeRegions = $topActiveRegionsData->map(function ($regionData) use ($year) {
            $statesInZone = $this->getStatesForZone($regionData['zone']);
            $topRisk = 'N/A';
            if (!empty($statesInZone)) {
                $topRisk = tbldataentry::select('riskindicators')
                    ->where('yy', $year)->whereIn('location', $statesInZone)
                    ->groupBy('riskindicators')->orderByRaw('COUNT(*) DESC')
                    ->take(1)->value('riskindicators');
            }
            $regionData['top_risk'] = $topRisk ?? 'N/A';
            return $regionData;
        });

        // 7. Line Chart Data
        $incidentData = tbldataentry::selectRaw('yy, COUNT(*) as incident_count')
            ->where('yy', '>=', 2018)->groupBy('yy')->orderBy('yy', 'asc')
            ->get()->keyBy('yy');

        $chartLabels = [];
        $chartData = [];
        foreach (range(2018, $year) as $chartYear) {
            $chartLabels[] = $chartYear;
            $chartData[] = $incidentData->get($chartYear)->incident_count ?? 0;
        }

        // 8. NEW: Bar Chart Data (Incidents by Region)
        // We use $sortedZones which we already have
        $barChartLabels = $sortedZones->pluck('zone');
        $barChartData = $sortedZones->pluck('total_incidents');

// --- 9. UPDATED: Pie Chart Data (Top 6 + Others) ---
        $allRiskIndicators = tbldataentry::selectRaw('riskindicators, COUNT(*) as frequency')
            ->where('yy', $year)
            ->groupBy('riskindicators')
            ->orderByDesc('frequency')
            ->get();

        $top6Indicators = $allRiskIndicators->take(6);
        $totalTop6Count = $top6Indicators->sum('frequency');
        $totalIndicatorCount = $allRiskIndicators->sum('frequency'); // Sum of all indicators
        $otherCount = $totalIndicatorCount - $totalTop6Count;

        $pieChartLabels = $top6Indicators->pluck('riskindicators');
        $pieChartData = $top6Indicators->pluck('frequency');



        // 10. Return all data
        return view('securityIntelligence', compact(
            'totalIncidents',
            'activeRiskZones',
            'prominentRisks',
            'activeRegions',
            'top5States',
            'chartLabels',
            'chartData',
            'barChartLabels',  // <-- Add new
            'barChartData',    // <-- Add new
            'pieChartLabels',  // <-- Add new
            'pieChartData'     // <-- Add new
        ));
    }



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

    /**
     * NEW: Helper function to get all states for a given zone.
     */
    private function getStatesForZone(string $zone): array
    {
        return $this->geopoliticalZones[$zone] ?? [];
    }


    private $riskMapping = [
        'Terrorism Index' => 'Terrorism',
        'Kidnapping Index' => 'Kidnapping',
        'Composite Risk Index' => 'All'
    ];

public function getRiskData(Request $request)
{
    // === 1. GET DATA FOR CURRENT YEAR ===
    $selectedYear = $request->input('year', now()->year);
    $selectedIndex = $request->input('index_type', 'Composite Risk Index'); // Default to all
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

    // === 2. GET DATA FOR PREVIOUS YEAR (FOR COMPARISON) ===
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


    // === 3. FORMAT DATA AND CALCULATE RANKS ===

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

    $currentYearStates = collect($stateRiskReportsCurrent)->pluck('location')->sort()->values();
    $prevYearStates = $prevYearRanks->keys()->sort()->values();

// --- ⬇️ START: MODIFIED CARD DATA LOGIC (Section 3.5) ⬇️ ---

        $sortedReports = collect($stateRiskReportsCurrent)->sortByDesc('normalized_ratio');

        // Card 1: Get the National Threat Level
        $highestRiskReport = $sortedReports->first();
        $nationalThreatLevel = $highestRiskReport ? $this->getRiskCategoryFromLevel($highestRiskReport['risk_level']) : 'Low';

        // Card 2: Get the Total Incidents AND Trend
        $totalTrackedIncidents = $sortedReports->sum('incident_count');

        // --- NEW TREND CALCULATION ---
        $previousYearTotalIncidents = collect($stateRiskReportsPrev)->sum('incident_count');
        $incidentTrendDifference = $totalTrackedIncidents - $previousYearTotalIncidents;

        $incidentTrendStatus = 'Stable';
        if ($incidentTrendDifference > 0) {
            $incidentTrendStatus = 'Escalating';
        } elseif ($incidentTrendDifference < 0) {
            $incidentTrendStatus = 'Improving';
        }
        // --- END OF NEW CALCULATION ---

        // Card 3: Get the Top 5 "Active Threat Groups"
        try {
            $topThreatGroupsQuery = tbldataentry::join('attack_group', 'tbldataentry.attack_group_name', '=', 'attack_group.id') // <-- CHECK THIS JOIN COLUMN
                ->select('attack_group.name', DB::raw('COUNT(*) as occurrences'))
                ->where('tbldataentry.yy', $selectedYear)
                ->whereRaw('LOWER(attack_group.name) != ?', ['others'])
                ->groupBy('attack_group.name')
                ->orderByDesc('occurrences')
                ->take(5);

            if ($riskIndicator !== 'All') {
                $topThreatGroupsQuery->where('tbldataentry.riskindicators', $riskIndicator);
            }

            $topThreatGroups = $topThreatGroupsQuery->get()->pluck('name')->implode(', ');
        } catch (\Exception $e) {
            Log::error("Failed to get Top Threat Groups: " . $e->getMessage());
            $topThreatGroups = "Error: Check join column";
        }

        // --- ⬆️ END: MODIFIED CARD DATA LOGIC ⬆️ ---


    $treemapData = [];
    $tableData = collect($stateRiskReportsCurrent)
        ->sortByDesc('normalized_ratio')
        ->values() // Reset keys
        ->map(function ($report, $index) use ($prevYearRanks, $selectedYear, $previousYear, &$treemapData) {
            $stateName = $report['location'];
            $currentRank = $index + 1;
            $prevData = $prevYearRanks->get($stateName);

            // Get previous rank, default to a high number (e.g., 50) if no data
            $previousRank = $prevData['rank'] ?? 50;

            // Determine status
            $status = 'Stable';
            if ($currentRank < $previousRank) {
                $status = 'Escalating'; // Rank 1 is worse than Rank 10
            } elseif ($currentRank > $previousRank) {
                $status = 'Improving';
            }

            // A. Data for the Treemap
            $treemapData[] = [
                'x' => $stateName,
                'y' => $report['normalized_ratio'] // Use the normalized score
            ];

            // B. Data for the Table
            return [
                'state' => $stateName,
                'risk_score' => round($report['normalized_ratio'], 2),
                'risk_level' => $this->getRiskCategoryFromLevel($report['risk_level']), // Helper function
                'rank_current' => $currentRank,
                'rank_previous' => $prevData ? $previousRank : 'N/A', // Show N/A if no data
                'status' => $status,
                'incidents' => $report['incident_count']
            ];
        });

    // ---

    // === 4. RETURN COMPREHENSIVE JSON ===
    return response()->json([
        // This is for your Apex Treemap
        'treemapSeries' => [
            ['data' => $treemapData]
        ],
        // This is for your new table
        'tableData' => $tableData,

        'cardData' => [
                'nationalThreatLevel' => $nationalThreatLevel,
                'totalTrackedIncidents' => $totalTrackedIncidents,
                'topThreatGroups' => $topThreatGroups ?: 'N/A' ,
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
