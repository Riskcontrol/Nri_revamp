<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AdvisoryLevel;
use App\Models\tblstatepopulation;
use App\Models\tbldataentry;
use App\Models\tblweeklydataentry;
use App\Models\DataInsights;
use App\Models\DataInsightsCategory;
use App\Models\tbllga;
use App\Models\StateNeighbourhoods;
use Carbon\Carbon;
use DateTime;
use DatePeriod;
use DateInterval;
use App\Models\EmergencyContacts;
use App\Models\PoliceCPAndEmergency;
use App\Models\CorrectionFactorForStates;
use Illuminate\Support\Facades\DB;


class LocationIntelligenceController extends Controller
{




    public function index(Request $request, $state = null)
    {

        // Check if state parameter is provided, if not check the request's search parameter, default to 'Lagos'
        // Determine the search query from the request or default to an empty string
        $searchQuery = $state ?? $request->input('search', '');

        // Initialize the stateName as null
        $stateName = null;

        // Check in tblstatepopulation table
        if (!empty($searchQuery)) {
            $stateName = tblstatepopulation::where('state', '=', $searchQuery)->value('state');
        }

        // If not found in tblstatepopulation, check in tbllga
        if ($stateName == null || empty($stateName)) {
            $stateName = tbllga::where('LGA', '=', $searchQuery)->value('state');
        }

        // If not found in tbllga, check in StateNeighbourhoods
        if ($stateName == null || empty($stateName)) {
            $stateName = StateNeighbourhoods::where('neighbourhood_name', '=', $searchQuery)->value('state');

            if ($stateName == null || empty($stateName)) {
                $findneighbourhood = DB::table('state_neighbourhoods')->where('neighbourhood_name', '=', $searchQuery)->value('id');
                $stateName = tbldataentry::where('neighbourhood', '=', $findneighbourhood)->value('location');
            }
        }

        // Handle invalid search query for POST requests
        if ($request->isMethod('post') && (empty($searchQuery) || !tblstatepopulation::where('state', '=', $searchQuery)->exists())) {
            // dd(2);
            return redirect()->back()->with(['error' => 'This is not a valid Nigerian state. Please check your spelling']);
        }


            $data = tblstatepopulation::where('state', $stateName)->first();
            $lgaNumber = tbllga::where('State', $stateName)->count();

            $levels = AdvisoryLevel::orderBy('level', 'asc')->get();

            $maxYear = tbldataentry::where('riskfactors','Violent Threats')
                        ->max('eventyear');//replacing date('Y')
            $maxMonth = tbldataentry::where('riskfactors','Violent Threats')
                    ->where('eventyear', $maxYear)
                    ->max('eventmonth');
            $currentMonthName =    Carbon::createFromFormat('!m', $maxMonth)->format('F');
        //  $reports = DB::table('tbldataentry AS t1')






        session()->put('lastSearch', $request->input('search'));
        $emergencyContact = EmergencyContacts::where('state', $stateName)->first();
        $stateData = tblstatepopulation::where('state', $stateName)->first();

        //added for news insights
        $dataInsights = DataInsights::where('state','LIKE', "%$stateName%")->orderBy('created_at', 'desc')->paginate(12);
        $categories = DataInsightsCategory::orderBy('created_at', 'asc')->get();
        $states = tblstatepopulation::orderBy('state', 'asc')->get();
        // Subquery to get the most recent created_at for each category_id
        // $cp =  PoliceCPAndEmergency::where('state', 'LIKE', "%$stateName%")->get();
        $cp = PoliceCPAndEmergency::where('state', 'LIKE', "%$stateName%")
            ->whereNotNull('emergency')
            ->where('emergency', '!=', '')
            ->first();

        if (!$cp) {
            $cp = PoliceCPAndEmergency::where('state', 'LIKE', "%$stateName%")
                ->first();
        }
        $lgaNames = tbllga::where('State', $stateName)
            ->pluck('LGA') // Directly get the 'LGA' column as a collection
            ->implode(', '); // Convert the collection to a comma-separated string
        // Convert the collection to a comma-separated string

        // important and hidden
        // $stateRanking = $this->getStateRankings($stateName);

        //start for ChartJS
        $incidentCount = tbldataentry::where('location', $stateName)
        ->where('yy', '>=', 2018)
        ->count();

        $deathCount = tbldataentry::where('location', $stateName)
            ->where('yy', '>=', 2018)
            ->sum('Casualties_count');

        $victimCount = tbldataentry::where('location', $stateName)
            ->where('yy', '>=', 2018)
            ->sum('victim');


        // Filter years starting from 2018
        $years = tbldataentry::where('location', $stateName)
                    ->where('yy', '>=', 2018)
                    ->distinct()
                    ->orderBy('yy', 'asc')
                    ->pluck('yy');

        // Prepare yearly data starting from 2018
        $yearlyData = [];
        foreach ($years as $year) {
            $yearlyData[] = [
                'year' => $year,
                'deaths' => tbldataentry::where('location', $stateName)
                                ->where('yy', $year)
                                ->sum('Casualties_count'),
                'victims' => tbldataentry::where('location', $stateName)
                                ->where('yy', $year)
                                ->sum('victim')
            ];
        }

        // Prepare risk incidents by year starting from 2018
        $riskIncidentsByYear = [];
        foreach ($years as $year) {
            $riskFactors = tbldataentry::where('location', $stateName)
                            ->where('yy', $year)
                            ->selectRaw('riskfactors, COUNT(*) as count')
                            ->groupBy('riskfactors')
                            ->pluck('count', 'riskfactors')
                            ->toArray();
            $riskIncidentsByYear[$year] = $riskFactors;
        }
        // End of ChartJS


        $riskAnalysis = $this->presentVsPreviousRisk($stateName);

        // Access the returned values
        $riskDifference = $riskAnalysis['riskDifference'];
        $riskID = $riskAnalysis['riskId'];

        // dd($riskId);
        $isAuthenticated = auth()->check();
        session()->put('data-middleware', "yes");
        session()->put('data-is-authenticated', "auth.check");

        // Get today's date
        $date = Carbon::now();
        $selectedDay = $date->day;
        $currentMonthAbbreviation = $date->format('M');
        $currentYear = $date->year;

        // Construct a corrected date string and parse it
        $myCorrectedDate = $currentMonthAbbreviation . ' ' . $selectedDay . ', ' . $currentYear;
        $dateTime = DateTime::createFromFormat('M d, Y', $myCorrectedDate);
        $endDate = $dateTime->format('Y-m-d');

        // Calculate start date (7 days before today)
        $duration = 7;
        $startDate = Carbon::createFromFormat('Y-M-d', $currentYear . '-' . $currentMonthAbbreviation . '-' . $selectedDay)
                        ->subDays($duration)
                        ->format('Y-m-d');



        $filteredWeeklyData = tbldataentry::where('location', $stateName)
        ->whereBetween(DB::raw("STR_TO_DATE(eventdateToUse, '%Y-%m-%d')"), [$startDate, $endDate])
        ->where(function ($query) {
            $query->whereIn('riskindicators', ['Industrial Accidents', 'Epidemic', 'Infrastructural Damage','Infrastructural Sabotage','Labour Disputes','Economic Sabotage', 'Political Instability'])
                  ->orWhere('impact', 'High')
                  ->orWhere('Casualties_count', '>', 5)
                  ->orWhere('victim', '>', 5);
                //   ->orWhere('riskfactors', 'Violent Threats');
        })
        ->orderBy('eventdateToUse', 'desc')
        ->get();



        $incidents = TblDataEntry::where('location', $stateName)
                        ->where('yy', date('Y'))
                    ->orderBy('id', 'desc')
                                ->take(12)
                                ->get();

        // Group incidents into sets of 3 for the slider
        $chunkSize =  2;
        $brc = new BusinessRiskController();
        if ($brc->isSmallDevice()) {
            $chunkSize =  1;
        }
        // Group incidents into sets of 3 for the slider
        $incidentGroups = $incidents->chunk($chunkSize);
        // print_r($filteredWeeklyData);

        return view('locationIntelligence', compact('filteredWeeklyData','emergencyContact','cp','stateData','stateName', 'levels', 'dataInsights', 'categories', 'states','lgaNumber','lgaNames', 'incidentCount', 'deathCount', 'victimCount', 'yearlyData', 'riskIncidentsByYear','riskID','riskDifference','isAuthenticated','incidentGroups'));
        // 'stateRanking' important
        //   'riskID',   'presentViolentThreats', 'previousMaxMonth', 'showPreviousMonth', 'previousViolentThreatsCount','reports',        //  ,'statelatitude','statelongitude'
    }
    public function presentVsPreviousRisk($stateName)
    {
        // Get current date components
        $currentDate = now();
        $currentMonth = $currentDate->month;
        $currentYear = $currentDate->year;
        $currentDay = $currentDate->day;

        // Calculate previous month and year
        $previousMonth = $currentMonth - 1;
        $previousYear = $currentYear;

        // Handle January edge case
        if ($previousMonth === 0) {
            $previousMonth = 12;
            $previousYear--;
        }

        // Instantiate YearlyRiskIndex
        $yearlyRisk = new YearlyRiskIndexController();
        // Fetch data for the previous month (from day 1 to current day of the previous month)
        $previousMonthData = $this->getDataByStateWithFilters($previousYear,$previousMonth,1,$previousYear,$previousMonth,$currentDay);
        $currentMonthData = $this->getDataByStateWithFilters($currentYear,$currentMonth,1,$currentYear,$currentMonth,$currentDay);
        // dd($currentMonthData);
        // Perform calculations on the previous month's data
        $previousMonth = $yearlyRisk->calculateData($previousMonthData);
        // Perform calculations on the previous month's data
        $currentMonth = $yearlyRisk->calculateData($currentMonthData);
        // dd($currentMonth);
        // Extract the riskId for the current month
        // $currentRiskIds = array_column($previousMonth, 'risk_level');
        // dd($currentRiskIds); // return all data
        // Find the riskId for the target location
        $previousRiskID = collect($previousMonth)->firstWhere('location', $stateName)['risk_level'] ?? null;
        $currentRiskID = collect($currentMonth)->firstWhere('location', $stateName)['risk_level'] ?? null;
        // Determine risk difference
        // print_r($currentRiskID);
        $riskDifference = null;
        if ($previousRiskID > $currentRiskID) {
            $riskDifference = "Improving";
        } elseif ($previousRiskID < $currentRiskID) {
            $riskDifference = "Escalating";
        } else {
            $riskDifference = "No Change";
        }

        // Return both the risk difference and currentRiskID
        return [
            'riskDifference' => $riskDifference,
            'riskId' => $currentRiskID,
        ];


    }

public function getDataByStateWithFilters($maxYear, $maxMonth, $eventDay, $maxYearEnd = null, $maxMonthEnd = null, $eventDayEnd = null)
{

    // Construct the start date
    $startDate = sprintf(
        '%04d-%02d-%02d',
        $maxYear,
        $maxMonth !== null ? $maxMonth : 1, // Fallback to 1 only if $maxMonth is null
        $eventDay !== null ? $eventDay : 1  // Fallback to 1 only if $eventDay is null
    );


    // Construct the end date
    $endDate = $maxYearEnd
        ? sprintf('%04d-%02d-%02d', $maxYearEnd, $maxMonthEnd ?: 12, $eventDayEnd ?: 31)
        : sprintf('%04d-%02d-%02d', $maxYear, $maxMonthEnd ?: $maxMonth ?: 12, $eventDayEnd ?: $eventDay ?: 31);

    // Debugging Dates (optional)
    // Uncomment this line to debug the date ranges
    // dd(['startDate' => $startDate, 'endDate' => $endDate]);
    // print_r( $startDate . " .... ". $endDate . "<br/>");

    // Query to fetch data by state with filters
    return DB::table(DB::raw('(SELECT DISTINCT location FROM tbldataentry) as locations'))
        ->leftJoin('tbldataentry', function ($join) use ($startDate, $endDate) {
            $join->on('locations.location', '=', 'tbldataentry.location')
                 ->whereBetween('eventdateToUse', [$startDate, $endDate])
                 ->where('tbldataentry.riskfactors', '=', 'Violent Threats'); // Add condition for riskfactors
        })
        ->select(
            'locations.location',
            DB::raw('COALESCE(SUM(tbldataentry.Casualties_count), 0) as sum_casualties'),
            DB::raw('COALESCE(SUM(tbldataentry.victim), 0) as sum_victims'),
            DB::raw('COALESCE(COUNT(tbldataentry.location), 0) as incident_count')
        )
        ->groupBy('locations.location')
        ->orderBy('locations.location', 'ASC')
        ->get();
}











    public function getStateRankings($stateName = null)
{
    // Get current year
    $currentYear = now()->year;

    // Get total sums for the current year data
    $totalData = tbldataentry::where('yy', $currentYear)
        ->where('riskfactors', 'Violent Threats')
        ->selectRaw('SUM(victim) as total_victims, SUM(Casualties_count) as total_deaths, COUNT(*) as total_incidents')
        ->first();

    // Check if current year data exists, if not fallback to previous years
    if (!$totalData || !$totalData->total_victims || !$totalData->total_deaths || !$totalData->total_incidents) {
        $latestYearWithData = tbldataentry::where('riskfactors', 'Violent Threats')->orderBy('yy', 'desc')->value('yy');

        // Use total data from the latest available year
        $totalData = tbldataentry::where('yy', $latestYearWithData)
            ->where('riskfactors', 'Violent Threats')
            ->selectRaw('SUM(victim) as total_victims, SUM(Casualties_count) as total_deaths, COUNT(*) as total_incidents')
            ->first();

        if (!$totalData) {
            return "No data available for any year.";
        }

        $currentYear = $latestYearWithData; // Update the year to fallback year
    }

    // Fetch correction factors for all states
    $correctionFactors = CorrectionFactorForStates::all()->keyBy('state');

    // Fetch state data
    // $stateData = tbldataentry::where('yy', $currentYear)
    //     ->where('riskfactors', 'Violent Threats')
    //     ->select('location')
    //     ->selectRaw('SUM(victim) as state_victims, SUM(Casualties_count) as state_deaths, COUNT(*) as state_incidents')
    //     ->groupBy('location')
    //     ->get();
    $stateData = DB::table(DB::raw('(SELECT DISTINCT location FROM tbldataentry) as all_locations'))
    ->leftJoin('tbldataentry as data', function ($join) use ($currentYear) {
        $join->on('all_locations.location', '=', 'data.location')
             ->where('data.yy', '=', $currentYear)
             ->where('data.riskfactors', '=', 'Violent Threats');
    })
    ->select('all_locations.location')
    ->selectRaw('COALESCE(SUM(data.victim), 0) as state_victims')
    ->selectRaw('COALESCE(SUM(data.Casualties_count), 0) as state_deaths')
    ->selectRaw('COALESCE(COUNT(data.location), 0) as state_incidents')
    ->groupBy('all_locations.location')
    ->get();



    // Aggregate totals
    $AllIncidentCount = $totalData->total_incidents;
    $AllvictimCount = $totalData->total_victims;
    $AlldeathThreatsCount = $totalData->total_deaths;

    // Prepare state objects with scores
    $states = $stateData->map(function ($state) use ($AllIncidentCount, $AllvictimCount, $AlldeathThreatsCount, $correctionFactors) {
        $incidentCount = $state->state_incidents ?? 0;
        $victimCount = $state->state_victims ?? 0;
        $deathThreatsCount = $state->state_deaths ?? 0;

        // Fetch correction factors
        $correctionFactor = $correctionFactors->get($state->location);
        $incidentCorrection = $correctionFactor->incident_correction ?? 1;
        $victimCorrection = $correctionFactor->victim_correction ?? 1;
        $deathCorrection = $correctionFactor->death_correction ?? 1;

        // Calculate ratios
        $incidentRatio = $AllIncidentCount != 0 ? (($incidentCount / $AllIncidentCount) * 25 * $incidentCorrection) : 0;
        $victimRatio = $AllvictimCount != 0 ? (($victimCount / $AllvictimCount) * 35 * $victimCorrection) : 0;
        $deathThreatsRatio = $AlldeathThreatsCount != 0 ? (($deathThreatsCount / $AlldeathThreatsCount) * 40 * $deathCorrection) : 0;

        // Calculate total score
        $score = $incidentRatio + $victimRatio + $deathThreatsRatio;

        return (object)[
            'location' => $state->location,
            'incident_count' => $incidentCount,
            'victim_count' => $victimCount,
            'death_count' => $deathThreatsCount,
            'score' => $score,
        ];
    });

    // Normalize scores
    $totalScoreSum = $states->sum('score');
    if ($totalScoreSum > 0) {
        $states = $states->map(function ($state) use ($totalScoreSum) {
            $state->score = ($state->score / $totalScoreSum) * 100;
            return $state;
        });
    }

    // Sort states by normalized scores
    $sortedStates = $states->sortByDesc('score')->values();

    // Assign ranks
    $currentRank = 1;
    $previousScore = null;

    foreach ($sortedStates as $index => $state) {
        if ($previousScore !== null && $state->score === $previousScore) {
            // Same rank for states with identical scores
            $state->rank = $currentRank;
        } else {
            // Assign a new rank and update the current rank
            $state->rank = $currentRank;
            $currentRank = $index + 1;
            $previousScore = $state->score;
        }
    }

    // Return data for a specific state if provided
    if ($stateName) {
        $specificState = $sortedStates->firstWhere('location', $stateName);

        // if (!$specificState) {
        //     return "State '$stateName' not found in the database.";
        // }
        // dd($specificState);
        return $specificState; // Return as an object
    }

    // Return all states if no specific state is provided
    return $sortedStates;
}








}
