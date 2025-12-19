<?php

namespace App\Http\Controllers;

use App\Models\tbldataentry;
use Illuminate\Http\Request;
use App\Models\CorrectionFactorForStates;
use Carbon\Carbon;
use App\Models\tblriskindicators;
use Illuminate\Support\Facades\DB; // Ensure DB is imported

class HomeNewController extends Controller
{

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

    public function getStateRiskReports(Request $request)
    {
        // ----------------------------
        // 1. Fetch aggregated state-level data
        // ----------------------------
        $data = tbldataentry::selectRaw('location, COUNT(*) as total_incidents, SUM(Casualties_count) as total_deaths, SUM(victim) as total_victims')
                                ->groupBy('location')
                                ->get();

        // ----------------------------
        // 2. Calculate state risk reports
        // ----------------------------
        $stateRiskReports = $this->calculateStateRisk($data);

        // ----------------------------
        // 3. Filter high-risk states
        // ----------------------------
        $highRiskStates = collect($stateRiskReports)->filter(function ($report) {
            return in_array($report['risk_level'], [3, 4]);
        });
        $highRiskStateCount = $highRiskStates->count();
        $top3HighRiskStates = $highRiskStates
            ->sortByDesc('normalized_ratio')  // sort descending
            ->take(3)                         // take the first 3
            ->pluck('location')               // get the location names
            ->implode(', ');                  // convert to comma-separated string

        // ----------------------------
        // 4. Trending Risk Factors
        // ----------------------------
        $trendingRiskFactors = tbldataentry::selectRaw('riskindicators, COUNT(*) as frequency, SUM(victim) as total_victims, SUM(Casualties_count) as total_casualties')
            ->groupBy('riskindicators')
            ->orderByDesc('frequency')
            ->take(4)
            ->get();

        // ----------------------------
        // 5. Recent incidents (last 24h)
        // ----------------------------
        $now = \Carbon\Carbon::now();
        $recentIncidentsCount = tbldataentry::where('audittimecreated', '>=', $now->subDay())->count();

        // Latest audited time
        $latestIncidentTime = tbldataentry::latest('audittimecreated')->first();
        $auditedTime = $latestIncidentTime ? \Carbon\Carbon::parse($latestIncidentTime->audittimecreated)->format('h:i:s A') : 'N/A';

        // ----------------------------
        // 6. Total incidents for a particular year (2024)
        // ----------------------------
        $totalIncidents = tbldataentry::where('yy', 2024)->count();

        // ----------------------------
        // 7. Current Threat Level calculation
        // ----------------------------
        $highestNormalized = collect($stateRiskReports)->max('normalized_ratio');

        if ($highestNormalized <= 25) {
            $currentThreatLevel = 'LOW';
        } elseif ($highestNormalized <= 50) {
            $currentThreatLevel = 'ELEVATED';
        } elseif ($highestNormalized <= 75) {
            $currentThreatLevel = 'HIGH';
        } else {
            $currentThreatLevel = 'CRITICAL';
        }

        // Assessment scope
        $assessmentScope = $highRiskStateCount >= 5 ? 'Nationwide Assessment' : 'Regional Assessment';

        // Valid until: 24 hours from latest incident
        $validUntil = $latestIncidentTime
            ? Carbon::parse($latestIncidentTime->audittimecreated)->addHours(24)->format('M d, Y h:i A')
            : 'N/A';

        // Key concerns: top 3 trending risk factors
        $keyConcerns = implode(', ', $trendingRiskFactors->pluck('riskindicators')->take(3)->toArray());

        $riskData = $this->riskIndex($request);

        // =========================================================================
        // --- NEW ADDITION START: Prepare Data for Homepage Calculator Dropdowns ---
        // =========================================================================

        // 1. Get List of States (for Calculator dropdown)
        // We use tbldataentry distinct locations to ensure we have data for them
        $calculatorStates = tbldataentry::select('location')
            ->distinct()
            ->orderBy('location')
            ->pluck('location');

        // 2. Get List of Industries (for Calculator dropdown)
        // Fetching from your specific 'business_risk_industries' table
        try {
            $calculatorIndustries = DB::table('business_risk_industries')
                ->orderBy('name')
                ->pluck('name');
        } catch (\Exception $e) {
            // Fallback if table doesn't exist yet
            $calculatorIndustries = ['Oil & Gas', 'Banking', 'Telecoms', 'Manufacturing', 'FMCG', 'Logistics'];
        }
        // =========================================================================
        // --- NEW ADDITION END ---
        // =========================================================================

        // ----------------------------
        // 8. Pass all data to the homex view
        // ----------------------------
        return view('home', array_merge(  // Merging all data
            compact(
                'highRiskStateCount',
                'top3HighRiskStates',
                'trendingRiskFactors',
                'recentIncidentsCount',
                'auditedTime',
                'totalIncidents',
                'currentThreatLevel',
                'assessmentScope',
                'validUntil',
                'keyConcerns',
                // --- ADDED NEW VARIABLES HERE ---
                'calculatorStates',
                'calculatorIndustries'
            ),
            $riskData // Merging the data from riskIndex, including 'locations'
        ));
    }

    // =========================================================================
    // --- NEW ADDITION START: Calculator API Logic ---
    // =========================================================================
    public function calculateHomepageRisk(Request $request)
    {
        // 1. Get Inputs
        $state = $request->state;
        $industry = $request->industry;
        $measures = $request->input('measures', []); // Array of checked boxes ['cctv', 'personnel']

        // Default to current year, or use 2024/2025 as needed
        $year = now()->year;

        // 2. Query your 'business_risk_data' Table
        // We look for incidents matching this specific State + Industry
        $query = DB::table('business_risk_data')
            ->where('location', $state)
            ->where('year', $year);

        // Flexible match for Industry (handles "Oil & Gas" vs "Oil and Gas")
        $query->where(function($q) use ($industry) {
            $q->where('industry', $industry)
              ->orWhere('industry', 'LIKE', "%$industry%");
        });

        $incidents = $query->get();

        // 3. Calculate Base Score (Weighted by Severity)
        $rawScore = 0;
        foreach ($incidents as $incident) {
            // Check 'level' or 'impact' column depending on your DB structure
            // Normalize to lowercase for safe comparison
            $severity = strtolower($incident->level ?? $incident->impact ?? 'low');

            if (str_contains($severity, 'high') || str_contains($severity, 'critical')) {
                $rawScore += 20; // High impact adds more risk
            } elseif (str_contains($severity, 'medium')) {
                $rawScore += 10;
            } else {
                $rawScore += 5;
            }
        }

        // Cap raw risk at 100
        $baseRisk = min($rawScore, 100);

        // 4. Apply Mitigation Logic (The "Calculator" Part)
        $reduction = 0;
        if (in_array('personnel', $measures)) $reduction += 0.20; // Guards reduce risk by 20%
        if (in_array('cctv', $measures))      $reduction += 0.10; // CCTV reduces by 10%
        if (in_array('access', $measures))    $reduction += 0.10; // Access Control reduces by 10%
        if (in_array('protocols', $measures)) $reduction += 0.05; // SOPs reduce by 5%

        // Cap total mitigation at 50% max
        $totalReduction = min($reduction, 0.50);

        // Final adjusted score
        $finalScore = round($baseRisk * (1 - $totalReduction));

        // 5. Determine Label
        $label = match(true) {
            $finalScore >= 75 => 'Critical',
            $finalScore >= 50 => 'High',
            $finalScore >= 25 => 'Medium',
            default => 'Low'
        };

        return response()->json([
            'success' => true,
            'base_risk' => $baseRisk,
            'final_score' => $finalScore,
            'label' => $label,
            'savings' => round($baseRisk - $finalScore), // Points saved by mitigation
            'incident_count' => $incidents->count()
        ]);
    }
    // =========================================================================
    // --- NEW ADDITION END ---
    // =========================================================================


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
                'year' => $item->yy,
            ];
        }

        // Second pass: now normalize the ratios for each state
        foreach ($reports as &$report) {
            $totalRatio = $report['total_ratio'];
            $normalizedRatio = $totalRatiosSum != 0 ? ($totalRatio / $totalRatiosSum) * 100 : 0;
            $report['normalized_ratio'] = $normalizedRatio;

            // Assign risk level based on the normalized ratio
            $report['risk_level'] = $this->determineBusinessRiskLevel($normalizedRatio);
        }

        // Sort the reports by location name (state) and return them
        $reports = collect($reports)->sortBy('location')->values()->all();

        return $reports;
    }

    public function getTrendingRiskFactors(Request $request)
    {
        // Query to get risk factors and count their frequency across incidents
        $trendingRiskFactors = tbldataentry::selectRaw('riskindicators, COUNT(*) as frequency, SUM(victim) as total_victims, SUM(Casualties_count) as total_casualties')
            ->groupBy('riskindicators') // Group by the risk factors
            ->orderByDesc('frequency') // Order by frequency (most frequent first)
            ->take(5) // Get top 3 trending risk factors (can adjust this as needed)
            ->get();

        // Pass the trending risk factors to the view
        return view('homex', compact('trendingRiskFactors'));
    }


    public function riskIndex(Request $request, $risk = null)
    {
        // Set default values and process the data
        $maxYear = $this->getMaxYear() - 1;
        $maxMonth = $this->getMaxMonth($maxYear);
        $yearSelected = $request->year;
        session(['yearSelected' => $yearSelected]);
        $indicatorName = "Nigeria Violence Index";
        $violentRiskindicators  = "";

        if ($risk) {
            $indicatorName = $risk . " Index";
            $violentRiskindicators = $this->getViolentIndicators();
        } else {
            $violentRiskindicators = $this->getViolentIndicators();
        }

        // Override indicators if provided in the request
        if ($request->input('riskindicator') AND $request->input('riskindicator') != 'violence-index') {
            $violentRiskindicators = $request->input('riskindicator');
            $indicatorName = $violentRiskindicators;
        }

        // Get data based on the determined indicators
        $dataByState = $this->getDataByStateWithoutMonth($maxYear, $violentRiskindicators);
        $allStateData = $this->getAllStateDataWithoutMonth($maxYear, $violentRiskindicators);
        $dataByState = $this->calculateData($dataByState);
        $currentMonthName = $this->formatMonthName($maxMonth);

        $previousYear = $maxYear - 1;
        $PreviousYearDataByState = $this->getDataByStateWithoutMonth($previousYear, $violentRiskindicators);
        $PreviousYearAllStateData = $this->getAllStateDataWithoutMonth($previousYear, $violentRiskindicators);
        $PreviousYearDataByState = $this->calculateData($PreviousYearDataByState);

        $maxMonthEnd = $this->getMaxMonth($maxYear);
        $maxYearEnd = $this->getMaxYear();
        $previousMaxYearEnd = $maxYearEnd - 1;
        $eventdayEnd = date('d');

        $searchDuration = $this->getStartEndTime($maxYear, "01", "01", $maxYearEnd, $maxMonthEnd, $eventdayEnd);
        $previousSearchDuration = $this->getStartEndTime($previousYear, 01, 01, $previousMaxYearEnd, 12, 31);

        // Leaflet part
        $locations = $this->leafletData($maxYear, $violentRiskindicators);

        $limit = 8; // Adjust the number of states you want to display (e.g., top 10)
        $dataByState = collect($dataByState)->sortByDesc('normalized_ratio')->take($limit);

        // Return the data to be used in another function or view
        return [
            'indicatorName' => $indicatorName,
            'dataByState' => $dataByState,
            'maxYear' => $maxYear,
            'previousYear' => $previousYear,
            'maxMonth' => $maxMonth,
            'maxMonthEnd' => $maxMonthEnd,
            'searchDuration' => $searchDuration,
            'previousSearchDuration' => $previousSearchDuration,
            'maxYearEnd' => $maxYearEnd,
            'eventdayEnd' => $eventdayEnd,
            'currentMonthName' => $currentMonthName,
            'violentRiskindicators' => $violentRiskindicators,
            'PreviousYearDataByState' => $PreviousYearDataByState,
            'locations' => $locations
        ];
    }

    public function getMaxYear()
    {
        return tbldataentry::where('riskfactors', 'Violent Threats')->max('eventyear');
    }

    public function getMaxMonth($maxYear)
    {
        return tbldataentry::where('riskfactors', 'Violent Threats')
                            ->where('eventyear', $maxYear)
                            ->max('eventmonth');
    }

    public function getViolentIndicators()
    {
        return Tblriskindicators::where('factors', 'Violent Threats')->pluck('indicators')->toArray();
    }

    public function getDataByStateWithoutMonth($maxYear, $indicators = null)
    {
        // Ensure $indicators is not null and is a flat array
        if (is_null($indicators)) {
            $indicators = []; // Default to an empty array if null
        } elseif ($indicators instanceof \Illuminate\Support\Collection) {
            $indicators = $indicators->toArray(); // Convert collection to array
        } elseif (!is_array($indicators)) {
            $indicators = [$indicators]; // Convert single value to array
        } else {
            // Flatten the array in case of nested arrays
            $indicators = $indicators; //array_flatten($indicators);
        }
        return DB::table(DB::raw('(SELECT DISTINCT location FROM tbldataentry) as locations'))
            ->leftJoin('tbldataentry', function ($join) use ($maxYear, $indicators) {
                $join->on('locations.location', '=', 'tbldataentry.location')
                    ->whereIn('tbldataentry.riskindicators', $indicators)
                    ->where('tbldataentry.eventyear', $maxYear);
            })
            ->select('locations.location',
                DB::raw('COALESCE(SUM(tbldataentry.Casualties_count), 0) as sum_casualties'),
                DB::raw('COALESCE(SUM(tbldataentry.victim), 0) as sum_victims'),
                DB::raw('COALESCE(COUNT(tbldataentry.location), 0) as incident_count'))
            ->groupBy('locations.location')
            ->orderBy('locations.location', 'asc')
            ->get();
    }


    public function getAllStateDataWithoutMonth($maxYear, $indicators = null)
    {
        // Ensure $indicators is not null and is a flat array
        if (is_null($indicators)) {
            $indicators = []; // Default to an empty array if null
        } elseif ($indicators instanceof \Illuminate\Support\Collection) {
            $indicators = $indicators->toArray(); // Convert collection to array
        } elseif (!is_array($indicators)) {
            $indicators = [$indicators]; // Convert single value to array
        } else {
            // Flatten the array in case of nested arrays
            $indicators = $indicators; //array_flatten($indicators);
        }
        return tbldataentry::select(DB::raw('SUM(Casualties_count) as sum_casualties'), DB::raw('SUM(victim) as sum_victims'), DB::raw('COUNT(*) as incident_count'))
            ->whereIn('riskindicators', $indicators)
            ->where('eventyear', $maxYear)
            ->first();
    }

    public function calculateData($data)
    {

        $AllIncidentCount = $AllvictimCount = $AlldeathThreatsCount = 0;
        $reports = [];


        // Fetch correction factors for all states
        $correctionFactors = CorrectionFactorForStates::all()->keyBy('state');

        // Aggregate total counts for incidents, victims, and casualties
        foreach ($data as $item) {
            $AllIncidentCount += $item->incident_count ?? 0;
            $AllvictimCount += $item->sum_victims ?? 0;
            $AlldeathThreatsCount += $item->sum_casualties ?? 0;
        }

        foreach ($data as &$item) {


            $incidentCount = $item->incident_count ?? 0;
            $victimCount = $item->sum_victims ?? 0;
            $deathThreatsCount = $item->sum_casualties ?? 0;



            // Fetch correction factors for the state
            $correctionFactor = $correctionFactors->get($item->location);
            $incidentCorrection = $correctionFactor->incident_correction ?? 1; // Default to 1 if no correction factor
            $victimCorrection = $correctionFactor->victim_correction ?? 1;
            $deathCorrection = $correctionFactor->death_correction ?? 1;

            // Apply correction factors to the ratios
            $incidentRatio = $AllIncidentCount != 0 ? (($incidentCount / $AllIncidentCount) * 25 * $incidentCorrection) : 0;
            $victimRatio = $AllvictimCount != 0 ? (($victimCount / $AllvictimCount) * 35 * $victimCorrection) : 0;
            $deathThreatsRatio = $AlldeathThreatsCount != 0 ? (($deathThreatsCount / $AlldeathThreatsCount) * 40 * $deathCorrection) : 0;

            $totalRatio = $incidentRatio + $victimRatio + $deathThreatsRatio;

            // Add the calculated data to the reports array
            $reports[$item->location] = [
                'location' => $item->location,
                'incident_count' => $incidentCount,
                'sum_victims' => $victimCount,
                'sum_casualties' => $deathThreatsCount,
                'total_ratio' => $totalRatio,
            ];
        }

        // Normalize the total ratios if there are any reports
        $totalSum = array_sum(array_column($reports, 'total_ratio'));
        // with normalization
        if ($totalSum != 0) {
            foreach ($reports as &$report) {
                $report['total_ratio'] = ($report['total_ratio'] / $totalSum) * 100;
            }
        }
        //without normalization

        // Recalculate risk level based on normalized ratios
        foreach($reports as &$report) {
            $report['risk_level'] = $this->determineRiskLevel($report['total_ratio']);
        }

        // Sort reports by total_ratio in descending order
        uasort($reports, function ($a, $b) {
            return $b['total_ratio'] <=> $a['total_ratio'];
        });

        // Assign rankings based on total_ratio
        $rank = 1;
        $previousValue = null;
        $currentRank = 1;

        foreach ($reports as &$report) {
            if ($previousValue === $report['total_ratio']) {
                // Assign the same rank for equal total_ratios
                $report['ranking'] = $currentRank;
            } else {
                // Update the rank for a new total_ratio
                $currentRank = $rank;
                $report['ranking'] = $currentRank;
                $previousValue = $report['total_ratio'];
            }
            $rank++;
        }

        // Sort reports by location alphabetically and re-index
        $reports = collect($reports)->sortBy('location')->values()->all();
        // dd($reports);
        return $reports;
    }

    public function formatMonthName($maxMonth)
    {
        return Carbon::createFromFormat('!m', $maxMonth)->format('F');
    }

    public function determineRiskLevel($totalRatio) {

        if ($totalRatio <= 0.8) {
            // print_r(1 . " $totalRatio <br/>");
            return 1;
        } elseif ($totalRatio <= 4 && $totalRatio > 0.8) {
            // print_r(2 . " $totalRatio <br/>");
            return 2;
        } elseif ($totalRatio <= 8 && $totalRatio > 4) {
            // print_r(3 . " $totalRatio <br/>");
            return 3;
        } elseif ($totalRatio <= 15  && $totalRatio > 8) {
            //  print_r(4 . " $totalRatio <br/>");
            return 4;
        } elseif($totalRatio > 15) {
            //  print_r(5 . " $totalRatio <br/>");
            return 5;
        }else{
            return 0;
        }
    }

    public function getStartEndTime($maxYear, $maxMonth, $eventday, $maxYearEnd, $maxMonthEnd, $eventdayEnd) {

        $startDate = trim($maxYear . "-" . ($maxMonth ? $maxMonth : '01') . "-" . ($eventday ? $eventday : '01'));
        $endDate = $maxYearEnd ? (trim(($maxYearEnd ? $maxYearEnd : $maxYear)) . "-" . ($maxMonthEnd ? $maxMonthEnd : '12') . "-" . ($eventdayEnd ? $eventdayEnd : '28')) :
                    ($maxYear . "-" . ($maxMonthEnd ? $maxMonthEnd : ($maxMonth ? $maxMonth : '12')) . "-" . ($eventdayEnd ? $eventdayEnd : ($eventday ? $eventday : '28')));

        // print_r("bad ".$endDate);
        $dateDuration =  date('d F Y',strtotime($startDate)) . ' - ' . date('d F Y',strtotime($endDate));

        return $dateDuration;
    }

    public function leafletData($maxYear)
    {
        // Build query based on filters
        $dataQuery = tbldataentry::query();

        // Filter by year if provided
        if (!empty($maxYear)) {
            $dataQuery->where('yy', $maxYear);
        }

        // Fetch and format the data
        $locations = $dataQuery->get()->map(function ($entry) {
            return [
                'state' => $entry->location,
                'caption' => $entry->caption,
                'latitude' => $entry->latitude,
                'longitude' => $entry->longitude,
                // 'radius' => $entry->incident_count * 500, // Scale radius dynamically
                'color' => 'red', // Assign dynamic color
                'riskindicators' => $entry->riskindicators, // Include risk indicator
                'impact' => $entry->impact
            ];
        });

        return $locations;
    }

}
