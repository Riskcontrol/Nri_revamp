<?php

namespace App\Http\Controllers;
use App\Models\tbldataentry;
use App\Models\CorrectionFactorForStates; // <-- You will need to import this model
use Illuminate\Http\Request;
use Carbon\Carbon; // <-- You may need this if not already imported

class SecurityIntelligenceController extends Controller
{

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

        // 6. Active Regions (Card) & Bar Chart Data
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
        $topActiveRegionsData = $sortedZones->take(2); // For the card

        $activeRegions = $topActiveRegionsData->map(function ($regionData) use ($year) {
            // ... (your existing logic for finding top risk)
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

        // Add the 'Others' slice if there are any left
        if ($otherCount > 0) {
            $pieChartLabels->push('Others');
            $pieChartData->push($otherCount);
        }


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


    public function calculateStateRisk($data, $stateName = null)
    {
        $reports = [];

        // Fetch the correction factors
        $correctionFactors = CorrectionFactorForStates::all()->keyBy('state');

        // Calculate the overall totals for all states (these are the national totals)
        $AllIncidentCount = $data->sum('total_incidents');
        $AllvictimCount = $data->sum('total_victims');
        $AlldeathThreatsCount = $data->sum('total_deaths');

        // Initialize total ratios sum for normalization
        $totalRatiosSum = 0;

        // First pass: calculate the ratios and accumulate the total ratios for normalization
        foreach ($data as &$item) {
            // Get the correction factors for the state
            $state = $item->location;
            $correction = $correctionFactors->get($state);

            $incidentCorrection = $correction ? $correction->incident_correction : 1;
            $victimCorrection = $correction ? $correction->victim_correction : 1;
            $casualtyCorrection = $correction ? $correction->casualty_correction : 1;

            $incidentCount = $item->total_incidents;
            $victimCount = $item->total_victims;
            $deathThreatsCount = $item->total_deaths;

            $incidentRatio = $AllIncidentCount != 0 ? ($incidentCount / $AllIncidentCount) * 25 * $incidentCorrection : 0;
            $victimRatio = $AllvictimCount != 0 ? ($victimCount / $AllvictimCount) * 35 * $victimCorrection : 0;
            $deathThreatsRatio = $AlldeathThreatsCount != 0 ? ($deathThreatsCount / $AlldeathThreatsCount) * 40 * $casualtyCorrection : 0;

            // Calculate the total ratio for each state
            $totalRatio = $incidentRatio + $victimRatio + $deathThreatsRatio;

            // Accumulate the total ratios sum
            $totalRatiosSum += $totalRatio;

            $reports[$item->location] = [
                'location' => $item->location,
                'incident_count' => $incidentCount,
                'sum_victims' => $victimCount,
                'sum_casualties' => $deathThreatsCount,
                'total_ratio' => $totalRatio,
                'year' => $item->yy, // <-- This is why we added MAX(yy)
            ];
        }

        // Second pass: now normalize the ratios for each state
        foreach ($reports as &$report) {
            $totalRatio = $report['total_ratio'];
            $normalizedRatio = $totalRatiosSum != 0 ? ($totalRatio / $totalRatiosSum) * 100 : 0;
            $report['normalized_ratio'] = $normalizedRatio;

            // Assign risk level based on the normalized ratio
            // You will need to make sure the determineBusinessRiskLevel function
            // is also available in this controller.
            // $report['risk_level'] = $this->determineBusinessRiskLevel($normalizedRatio);

            // For now, I'll add a placeholder if that function doesn't exist yet
            // You should replace this with your actual function call
            if (method_exists($this, 'determineBusinessRiskLevel')) {
                 $report['risk_level'] = $this->determineBusinessRiskLevel($normalizedRatio);
            } else {
                // Placeholder logic if function is missing
                if ($normalizedRatio > 7) $report['risk_level'] = 4;
                elseif ($normalizedRatio > 5) $report['risk_level'] = 3;
                elseif ($normalizedRatio > 2) $report['risk_level'] = 2;
                else $report['risk_level'] = 1;
            }
        }

        // Sort the reports by location name (state) and return them
        $reports = collect($reports)->sortBy('location')->values()->all();

        return $reports;
    }

  public function determineBusinessRiskLevel($totalRatio) {
        if ($totalRatio >=0 AND $totalRatio <= 1.7) {
            return 1;
        } elseif ($totalRatio  > 1.7 AND $totalRatio <= 2.8) {
            return 2;
        } elseif ($totalRatio > 2.8 AND $totalRatio <= 7) {
            return 3;
        } else {
            return 4;
        }
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

}
