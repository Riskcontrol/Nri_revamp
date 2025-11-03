<?php

namespace App\Http\Controllers;
use App\Models\tbldataentry;
use Illuminate\Http\Request;
use App\Models\CorrectionFactorForStates;
use Carbon\Carbon;
use App\Models\tblriskindicators;
use Illuminate\Support\Facades\DB;



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
            'keyConcerns'
        ),
        $riskData // Merging the data from riskIndex, including 'locations'
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

    // Alternatively, you can order by severity (e.g., number of victims, casualties)
    // If you want to rank by severity as well, you can modify the query to prioritize total_victims or total_casualties.

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

    // session()->put('data-middleware', "yes");

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
            // print_r("Incident  $incidentCount Victim $victimCount Death $deathThreatsCount and $item->location . All Incident $AllIncidentCount all victim $AllvictimCount all death $AlldeathThreatsCount <br/>");


            // $riskLevel = $this->determineRiskLevel($totalRatio);

            // Debugging Information (Optional)
            // print_r("<br/>$item->location - Incident Count: $incidentCount, Total Incidents: $AllIncidentCount, Incident Ratio: $incidentRatio. Victim Count: $victimCount, Total Victims: $AllvictimCount, Victim Ratio: $victimRatio. Death Count: $deathThreatsCount, Total Deaths: $AlldeathThreatsCount, Death Ratio: $deathThreatsRatio. Total Ratio: $totalRatio. <br/>");

            // Add the calculated data to the reports array
            $reports[$item->location] = [
                'location' => $item->location,
                'incident_count' => $incidentCount,
                'sum_victims' => $victimCount,
                'sum_casualties' => $deathThreatsCount,
                'total_ratio' => $totalRatio,
                // print_r($totalRatio),
                // 'risk_level' => $riskLevel,
            ];
            // print_r( $item->location . " ". $totalRatio . "<br/>");

        }
        // Debug before normalization
        // foreach ($reports as $report) {
            // print_r("Before Normalization - Location: {$report['location']}, Total Ratio: {$report['total_ratio']}<br/>");
        // }

        // print_r("all incidents " . $AllIncidentCount ." all victim ". $AllvictimCount . " all death ". $AlldeathThreatsCount);

        // Normalize the total ratios if there are any reports
        $totalSum = array_sum(array_column($reports, 'total_ratio'));
        // with normalization
        if ($totalSum != 0) {
            foreach ($reports as &$report) {
                $report['total_ratio'] = ($report['total_ratio'] / $totalSum) * 100;
                // print_r($report['total_ratio'] ." new normalized value <br/>");
            }
        }
        //without normalization

        // Recalculate risk level based on normalized ratios
        foreach($reports as &$report) {
            $report['risk_level'] = $this->determineRiskLevel($report['total_ratio']);
            // print_r("With normalization " . " ". $report['total_ratio'] . "<br/>");
        }

        // Sort reports by total_ratio in descending order
        uasort($reports, function ($a, $b) {
            return $b['total_ratio'] <=> $a['total_ratio'];
        });
        // print_r($reports ." reports <br/>");
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
        // $startDate = date('d F Y', strtotime($maxYear . "-" . ($maxMonth ? $maxMonth : '01') . "-" . ($eventday ? $eventday : '01')));
        // $endDate = $maxYearEnd ? date('d F Y', strtotime(($maxMonthEnd ? $maxYearEnd . "-" . $maxMonthEnd : $maxYear . "-" . ($maxMonthEnd ? $maxMonthEnd : '12')) . "-" . ($eventdayEnd ? $eventdayEnd : '31'))) :
        //         date('d F Y', strtotime(($maxMonthEnd ? $maxYear . "-" . $maxMonthEnd : ($maxMonth ? $maxYear . "-" . $maxMonth : $maxYear . '-12')) . "-" . ($eventdayEnd ? $eventdayEnd : ($eventday ? $eventday : '31'))));

        // $startDate = $maxYear . "-" . ($maxMonth ? $maxMonth : '01') . "-" . ($eventday ? $eventday : '01');
        // $endDate = $maxYearEnd ? ( ($maxYearEnd ? $maxYearEnd : $maxYear ). "-" . ($maxMonthEnd ? $maxMonthEnd : '12') . "-" . ($eventdayEnd ? $eventdayEnd : '28')) :
        //             $maxYear . "-" . ($maxMonthEnd ? $maxMonthEnd : ($maxMonth ? $maxMonth : '12')) . "-" . ($eventdayEnd ? $eventdayEnd : ($eventday ? $eventday : '28'));//$startDate;
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
