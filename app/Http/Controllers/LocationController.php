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

    private function getStateCorrectionFactors($state)
    {

        $factors = CorrectionFactorForStates::where('state', $state)->first();

        if (!$factors) {
            return (object)[
                'incident_correction' => 1,
                'victim_correction' => 1,
                'casualty_correction' => 1,
            ];
        }
        return $factors;
    }

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


        $currentMonth = date('m');
        $currentYear = date('Y');


        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

        // If user selected current year, show up to current month. Otherwise show all 12.
        $endMonth = ((int)$year === (int)now()->year) ? (int)now()->format('n') : 12;

        $wantedMonths = collect(range(1, $endMonth))
            ->map(fn($m) => $months[$m - 1])
            ->values();

        // Query counts for that year, grouped by month_pro
        $monthlyData = tbldataentry::selectRaw('month_pro, COUNT(*) as total_incidents')
            ->whereRaw('LOWER(location) = ?', [strtolower($state)])
            ->where('yy', (int)$year)
            ->whereIn('month_pro', $wantedMonths->all())
            ->groupBy('month_pro')
            ->get()
            ->keyBy('month_pro');

        // X labels are the months for that year
        $chartLabels = $wantedMonths->values();

        // Y values aligned to the labels (missing months = 0)
        $incidentCounts = $wantedMonths->map(function ($mp) use ($monthlyData) {
            return (int)($monthlyData[$mp]->total_incidents ?? 0);
        })->values();



        // Fetch the top 5 most frequent risk indicators and their counts
        $topRiskIndicators = tbldataentry::select('riskindicators', DB::raw('COUNT(*) as occurrences'))
            ->whereRaw('LOWER(location) = ?', [strtolower($state)]) // Filter by state
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

            // --- ADD THIS LINE to ignore the 'Others' category ---
            ->whereRaw('LOWER(motives_specific.name) != ?', ['others'])

            ->groupBy('motives_specific.name')
            ->orderByDesc('occurrences')
            ->take(5) // This now gets the Top 5 *specific* motives
            ->get();

        $motiveLabels = $motiveData->pluck('name')->toArray();
        $motiveCounts = $motiveData->pluck('occurrences')->toArray();


        $attackData = tbldataentry::join('attack_group', 'tbldataentry.attack_group_name', '=', 'attack_group.id')
            ->select('attack_group.name', DB::raw('COUNT(*) as occurrences'))
            ->whereRaw('LOWER(tbldataentry.location) = ?', [strtolower($state)])
            ->where('tbldataentry.yy', $year)

            // --- ADD THIS LINE to ignore the 'Others' category ---
            ->whereRaw('LOWER(attack_group.name) != ?', ['others'])

            ->groupBy('attack_group.name')
            ->orderByDesc('occurrences')
            ->take(5) // This now gets the Top 5 *specific* motives
            ->get();

        $attackLabels = $attackData->pluck('name')->toArray();
        $attackCounts = $attackData->pluck('occurrences')->toArray();

        // dd($attackData->toArray());


        // Get the most frequent LGA for the given state
        $mostAffectedLGA = tbldataentry::select('lga', DB::raw('COUNT(*) as occurrences'))
            ->whereRaw('LOWER(location) = ?', [strtolower($state)])
            ->where('yy', $year)
            ->where('lga', '!=', '')
            ->groupBy('lga')
            ->orderByDesc('occurrences')
            ->first();



        // Fetch the most recent 5 incidents for the given state
        $recentIncidents = tbldataentry::select('lga', 'add_notes', 'riskindicators', 'impact', 'datecreated')
            ->whereRaw('LOWER(location) = ?', [strtolower($state)])
            ->orderBy('datecreated', 'desc') // Order in the database
            ->limit(5) // Get only 5
            ->get();

        // Sort incidents by datecreated (we'll use Carbon to compare dates)
        $recentIncidents = $recentIncidents->sortByDesc(function ($incident) {
            return \Carbon\Carbon::createFromFormat('M d, Y', $incident->datecreated)->timestamp;
        });


        $recentIncidents = $recentIncidents->take(5);

        $getStates = StateInsight::pluck('state');

        $crimeIndicators = $this->getCrimeIndexIndicators();

        $stateCrimeIndexScore = 0;
        $crimeTable = [];

        if ($crimeIndicators->isNotEmpty()) {

            // --- Get Correction Factors for THIS state ---
            $correction = $this->getStateCorrectionFactors($state);

            // --- Get STATE-SPECIFIC Totals (for Crime only) ---
            $stateCrimeTotals = tbldataentry::selectRaw('COUNT(*) as total_incidents, SUM(Casualties_count) as total_deaths, SUM(victim) as total_victims')
                ->whereRaw('LOWER(location) = ?', [strtolower($state)])
                ->where('yy', $year)
                ->whereIn('riskindicators', $crimeIndicators)
                ->first();

            // --- Get NATIONAL Totals (for Crime only) ---
            $nationalCrimeTotals = tbldataentry::selectRaw('COUNT(*) as total_incidents, SUM(Casualties_count) as total_deaths, SUM(victim) as total_victims')
                ->where('yy', $year)
                ->whereIn('riskindicators', $crimeIndicators)
                ->first();

            // --- Manually Calculate the State's Risk Score ---
            $incidentRatio = $nationalCrimeTotals->total_incidents != 0 ?
                ($stateCrimeTotals->total_incidents / $nationalCrimeTotals->total_incidents) * 25 * $correction->incident_correction : 0;

            $victimRatio = $nationalCrimeTotals->total_victims != 0 ?
                ($stateCrimeTotals->total_victims / $nationalCrimeTotals->total_victims) * 35 * $correction->victim_correction : 0;

            $deathThreatsRatio = $nationalCrimeTotals->total_deaths != 0 ?
                ($stateCrimeTotals->total_deaths / $nationalCrimeTotals->total_deaths) * 40 * $correction->casualty_correction : 0;

            $stateCrimeIndexScore = round($incidentRatio + $victimRatio + $deathThreatsRatio, 2);

            // --- Get STATE-SPECIFIC breakdown for the table (Current Year) ---
            $currentStateIndicators = tbldataentry::select('riskindicators', DB::raw('COUNT(*) as incident_count'))
                ->whereRaw('LOWER(location) = ?', [strtolower($state)])
                ->where('yy', $year)
                ->whereIn('riskindicators', $crimeIndicators)
                ->groupBy('riskindicators')
                ->get()
                ->keyBy('riskindicators');

            // --- Get STATE-SPECIFIC breakdown for the table (Previous Year) ---
            $previousStateIndicators = tbldataentry::select('riskindicators', DB::raw('COUNT(*) as incident_count'))
                ->whereRaw('LOWER(location) = ?', [strtolower($state)])
                ->where('yy', $year - 1) // Previous year
                ->whereIn('riskindicators', $crimeIndicators)
                ->groupBy('riskindicators')
                ->get()
                ->keyBy('riskindicators');

            // --- Build the final table ---
            foreach ($crimeIndicators as $indicator) {
                $currentCount = $currentStateIndicators->get($indicator)->incident_count ?? 0;
                $previousCount = $previousStateIndicators->get($indicator)->incident_count ?? 0;

                if ($currentCount > 0) { // Only show indicators with data
                    $status = 'Stable';
                    if ($currentCount > $previousCount) {
                        $status = 'Escalating';
                    } elseif ($currentCount < $previousCount) {
                        $status = 'Improving';
                    }

                    $crimeTable[] = [
                        'indicator_name' => $indicator,
                        'incident_count' => $currentCount,
                        'previous_year_count' => $previousCount,
                        'status' => $status,
                    ];
                }
            }
            $crimeTable = collect($crimeTable)->sortByDesc('incident_count')->values()->all();
        }

        // --- 3. START INSIGHT GENERATION ---
        $trendInsights = $this->calculateTrendInsights($state, $year);
        $lethalityInsight = $this->calculateLethalityInsights($state, $year);
        $forecastInsight = $this->calculateForecast($state);

        // Combine into one array and remove nulls
        $automatedInsights = [
            $trendInsights['velocity'] ?? null,
            $trendInsights['emerging'] ?? null,
            $lethalityInsight,
            $forecastInsight
        ];
        $automatedInsights = array_values(array_filter($automatedInsights));
        // --- END INSIGHT GENERATION ---

        $rankingData = $this->calculateRankAndScore($state, $year);
        $stateCrimeIndexScore = $rankingData['score'];
        $stateRank = $rankingData['rank'];
        $stateRankOrdinal = $rankingData['ordinal'];


        return view('locationIntelligence', compact('total_incidents', 'availableYears', 'year', 'state', 'mostFrequentRisk', 'chartLabels', 'incidentCounts', 'topRiskLabels', 'topRiskCounts', 'yearLabels', 'yearCounts', 'attackLabels', 'attackCounts', 'mostAffectedLGA', 'recentIncidents', 'getStates', 'stateCrimeIndexScore', 'crimeTable', 'automatedInsights', 'stateRank', 'stateRankOrdinal'));
    }



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



        $recentIncidents = tbldataentry::select('lga', 'add_notes', 'riskindicators', 'impact', 'datecreated') // <-- ADDED 'datecreated'

            ->whereRaw('LOWER(location) = ?', [strtolower($state)])

            // To ensure you get the most recent, you should also order by datecreated DESC

            ->orderBy('datecreated', 'desc')

            ->limit(5)

            ->get();
        // Top 5 risks
        $topRiskIndicators = tbldataentry::select('riskindicators', DB::raw('COUNT(*) as occurrences'))
            ->whereRaw('LOWER(location) = ?', [strtolower($state)])
            ->where('yy', $year)
            ->groupBy('riskindicators')
            ->orderByDesc('occurrences')
            ->take(5)
            ->get();

        $topRiskLabels = $topRiskIndicators->pluck('riskindicators')->values();
        $topRiskCounts = $topRiskIndicators->pluck('occurrences')->values();


        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

        $endMonth = ((int)$year === (int)now()->year) ? (int)now()->format('n') : 12;

        $wantedMonths = collect(range(1, $endMonth))
            ->map(fn($m) => $months[$m - 1])
            ->values();

        $monthlyDataRaw = tbldataentry::selectRaw('month_pro, COUNT(*) as total_incidents')
            ->whereRaw('LOWER(location) = ?', [strtolower($state)])
            ->where('yy', (int)$year)
            ->whereIn('month_pro', $wantedMonths->all())
            ->groupBy('month_pro')
            ->get()
            ->keyBy('month_pro');

        $chartLabels = $wantedMonths->values()->toArray();

        $incidentCounts = $wantedMonths->map(function ($mp) use ($monthlyDataRaw) {
            return (int)($monthlyDataRaw[$mp]->total_incidents ?? 0);
        })->values()->toArray();

        // B) Year series from 2018
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

            // --- ADD THIS LINE to ignore the 'Others' category ---
            ->whereRaw('LOWER(attack_group.name) != ?', ['others'])

            ->groupBy('attack_group.name')
            ->orderByDesc('occurrences')
            ->take(5) // This now gets the Top 5 *specific* motives
            ->get();

        $attackLabels = $attackData->pluck('name')->toArray();
        $attackCounts = $attackData->pluck('occurrences')->toArray();

        // Get the list of all crime indicators
        $crimeIndicators = $this->getCrimeIndexIndicators();

        $stateCrimeIndexScore = 0;
        $crimeTable = [];

        if ($crimeIndicators->isNotEmpty()) {


            $correction = $this->getStateCorrectionFactors($state);


            $stateCrimeTotals = tbldataentry::selectRaw('COUNT(*) as total_incidents, SUM(Casualties_count) as total_deaths, SUM(victim) as total_victims')
                ->whereRaw('LOWER(location) = ?', [strtolower($state)])
                ->where('yy', $year)
                ->whereIn('riskindicators', $crimeIndicators)
                ->first();

            // Get NATIONAL Totals (for Crime only)
            $nationalCrimeTotals = tbldataentry::selectRaw('COUNT(*) as total_incidents, SUM(Casualties_count) as total_deaths, SUM(victim) as total_victims')
                ->where('yy', $year)
                ->whereIn('riskindicators', $crimeIndicators)
                ->first();

            // Manually Calculate the State's Risk Score
            $incidentRatio = $nationalCrimeTotals->total_incidents != 0 ?
                ($stateCrimeTotals->total_incidents / $nationalCrimeTotals->total_incidents) * 25 * $correction->incident_correction : 0;

            $victimRatio = $nationalCrimeTotals->total_victims != 0 ?
                ($stateCrimeTotals->total_victims / $nationalCrimeTotals->total_victims) * 35 * $correction->victim_correction : 0;

            $deathThreatsRatio = $nationalCrimeTotals->total_deaths != 0 ?
                ($stateCrimeTotals->total_deaths / $nationalCrimeTotals->total_deaths) * 40 * $correction->casualty_correction : 0;

            $stateCrimeIndexScore = round($incidentRatio + $victimRatio + $deathThreatsRatio, 2);

            // Get STATE-SPECIFIC breakdown for the table (Current Year)
            $currentStateIndicators = tbldataentry::select('riskindicators', DB::raw('COUNT(*) as incident_count'))
                ->whereRaw('LOWER(location) = ?', [strtolower($state)])
                ->where('yy', $year)
                ->whereIn('riskindicators', $crimeIndicators)
                ->groupBy('riskindicators')
                ->get()
                ->keyBy('riskindicators');

            // Get STATE-SPECIFIC breakdown for the table (Previous Year)
            $previousStateIndicators = tbldataentry::select('riskindicators', DB::raw('COUNT(*) as incident_count'))
                ->whereRaw('LOWER(location) = ?', [strtolower($state)])
                ->where('yy', $year - 1) // Previous year
                ->whereIn('riskindicators', $crimeIndicators)
                ->groupBy('riskindicators')
                ->get()
                ->keyBy('riskindicators');

            // Build the final table
            foreach ($crimeIndicators as $indicator) {
                $currentCount = $currentStateIndicators->get($indicator)->incident_count ?? 0;
                $previousCount = $previousStateIndicators->get($indicator)->incident_count ?? 0;

                if ($currentCount > 0) { // Only show indicators with data
                    $status = 'Stable';
                    if ($currentCount > $previousCount) {
                        $status = 'Escalating';
                    } elseif ($currentCount < $previousCount) {
                        $status = 'Improving';
                    }

                    $crimeTable[] = [
                        'indicator_name' => $indicator,
                        'incident_count' => $currentCount,
                        'previous_year_count' => $previousCount,
                        'status' => $status,
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
            $forecastInsight
        ];
        $automatedInsights = array_values(array_filter($automatedInsights));

        $rankingData = $this->calculateRankAndScore($state, $year);

        return response()->json([
            'total_incidents' => $total_incidents,
            'mostFrequentRisk' => $mostFrequentRisk,
            'mostAffectedLGA'  => $mostAffectedLGA,
            'recentIncidents'  => $recentIncidents,
            'chartLabels'     => $chartLabels,
            'incidentCounts'  => $incidentCounts,
            'topRiskLabels'   => $topRiskLabels,
            'topRiskCounts'   => $topRiskCounts,
            'yearLabels'      => $yearLabels,
            'yearCounts'      => $yearCounts,
            'attackLabels'    => $attackLabels,
            'attackCounts'    => $attackCounts,
            'stateCrimeIndexScore' => $stateCrimeIndexScore,
            'stateRank' => $rankingData['rank'],
            'stateRankOrdinal' => $rankingData['ordinal'],
            'crimeTable'           => $crimeTable,
            'automatedInsights' => $automatedInsights
        ]);
    }
    public function getTotalIncidentsOnly($state, $year)
    {
        // Count all records for the given state.
        $total_incidents = tbldataentry::whereRaw('LOWER(location) = ?', [strtolower($state)])
            ->where('yy', $year)
            ->count();

        // Return a JSON response containing just the incident count.
        return response()->json([
            'total_incidents' => $total_incidents,
        ]);
    }

    public function getIncidentLocations(Request $request, $state, $year)
    {
        // Fetches all incidents for the state/year that are 'High' impact
        // and have valid latitude/longitude.
        $locations = tbldataentry::select('lga_lat', 'lga_long', 'lga', 'add_notes', 'impact', 'datecreated')
            ->whereRaw('LOWER(location) = ?', [strtolower($state)])
            ->where('yy', $year)

            // --- IMPORTANT ---
            // I'm assuming 'High' is the text in your impact column.
            // Change this if your value is different (e.g., 'Severe')
            ->where('impact', 'High')

            // Ensure we only get valid coordinates
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
            ->where('riskindicators', '!=', '') // Ensure not-empty
            ->groupBy('riskindicators')
            ->orderByDesc('occurrences')
            ->take(5)
            ->get();

        return response()->json([
            'labels' => $topRiskIndicators->pluck('riskindicators')->values(),
            'counts' => $topRiskIndicators->pluck('occurrences')->values(),
        ]);
    }

    /*
    * Get incident counts grouped by LGA for a specific state and year
    */
    public function getLgaIncidentCounts($state, $year)
    {
        $lgaCounts = tbldataentry::select('lga', DB::raw('COUNT(*) as incident_count'))
            ->whereRaw('LOWER(location) = ?', [strtolower($state)])
            ->where('yy', $year)
            ->where('lga', '!=', '') // Ensure LGA is not empty
            ->groupBy('lga')
            ->get()
            // Key the result by LGA name for easy JavaScript lookup
            ->pluck('incident_count', 'lga');

        return response()->json($lgaCounts);
    }

    // Add this to LocationController.php

    public function getComparisonRiskCounts(Request $request)
    {
        $state = $request->input('state');
        $year = $request->input('year');
        // We receive the labels displayed on the chart
        $indicators = $request->input('indicators');

        if (!$state || !$year || empty($indicators)) {
            return response()->json(['counts' => []]);
        }

        // Fetch counts ONLY for the requested indicators
        $data = tbldataentry::select('riskindicators', DB::raw('COUNT(*) as occurrences'))
            ->whereRaw('LOWER(location) = ?', [strtolower($state)])
            ->where('yy', $year)
            ->whereIn('riskindicators', $indicators)
            ->groupBy('riskindicators')
            ->get()
            ->pluck('occurrences', 'riskindicators'); // Key by name: ['Kidnapping' => 50, 'Robbery' => 10]

        // Align the data to match the exact order of the input indicators
        // If the state has 0 for a specific indicator, return 0 instead of null
        $orderedCounts = [];
        foreach ($indicators as $ind) {
            $orderedCounts[] = $data[$ind] ?? 0;
        }

        return response()->json([
            'counts' => $orderedCounts
        ]);
    }

    // --- Helper Method to Calculate Score & Rank for ALL States ---
    private function calculateRankAndScore($targetState, $year)
    {
        $crimeIndicators = $this->getCrimeIndexIndicators();

        if ($crimeIndicators->isEmpty()) {
            return ['score' => 0, 'rank' => 'N/A', 'ordinal' => '', 'total_states' => 0];
        }

        // 1. Get National Totals
        $nat = tbldataentry::selectRaw('COUNT(*) as i, SUM(Casualties_count) as d, SUM(victim) as v')
            ->where('yy', $year)
            ->whereIn('riskindicators', $crimeIndicators)
            ->first();

        if (!$nat || $nat->i == 0) {
            return ['score' => 0, 'rank' => 'N/A', 'ordinal' => '', 'total_states' => 0];
        }

        // 2. Get Totals for ALL States Grouped
        $statesData = tbldataentry::selectRaw('LOWER(location) as loc, COUNT(*) as i, SUM(Casualties_count) as d, SUM(victim) as v')
            ->where('yy', $year)
            ->whereIn('riskindicators', $crimeIndicators)
            ->groupBy(DB::raw('LOWER(location)'))
            ->get()
            ->keyBy('loc');

        // 3. Get All Correction Factors
        $factors = CorrectionFactorForStates::all()->mapWithKeys(function ($item) {
            return [strtolower($item->state) => $item];
        });

        // 4. Calculate Score for EVERY State
        // We use StateInsight to ensure we iterate all valid 36+1 states
        $allStates = StateInsight::pluck('state')->map(fn($s) => strtolower($s))->unique();
        $scores = [];

        foreach ($allStates as $stateName) {
            $data = $statesData->get($stateName);
            $fact = $factors->get($stateName);

            $i_count = $data->i ?? 0;
            $v_count = $data->v ?? 0;
            $d_count = $data->d ?? 0;

            // Default factor to 1 if missing
            $f_i = $fact->incident_correction ?? 1;
            $f_v = $fact->victim_correction ?? 1;
            $f_d = $fact->casualty_correction ?? 1;

            // Avoid division by zero
            $nat_v = $nat->v ?: 1;
            $nat_d = $nat->d ?: 1;

            $score = (($i_count / $nat->i) * 25 * $f_i) +
                (($v_count / $nat_v) * 35 * $f_v) +
                (($d_count / $nat_d) * 40 * $f_d);

            $scores[$stateName] = round($score, 2);
        }

        // 5. Sort High to Low (Higher score = Rank 1)
        arsort($scores);

        // 6. Find Rank of Target
        $rank = 1;
        $targetScore = 0;
        $targetLower = strtolower($targetState);

        foreach ($scores as $s => $val) {
            if ($s === $targetLower) {
                $targetScore = $val;
                break;
            }
            $rank++;
        }

        // Add ordinal suffix (st, nd, rd, th)
        $ordinal = 'th';
        if (!in_array(($rank % 100), [11, 12, 13])) {
            switch ($rank % 10) {
                case 1:
                    $ordinal = 'st';
                    break;
                case 2:
                    $ordinal = 'nd';
                    break;
                case 3:
                    $ordinal = 'rd';
                    break;
            }
        }

        return [
            'score' => $targetScore,
            'rank' => $rank,
            'ordinal' => $ordinal,
            'total_states' => count($scores)
        ];
    }
}
