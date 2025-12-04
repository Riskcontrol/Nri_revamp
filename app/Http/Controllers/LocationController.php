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

public function getTotalIncident($state)
{

    $year = now()->year;
    $availableYears = range(now()->year, 2018);
    $states =StateInsight::all();

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


$now = now(); // Carbon instance of current date
$pairs = collect(range(0, 11))
    ->map(fn($i) => $now->copy()->subMonths($i))
    ->reverse() // Order from oldest to newest month
    ->values();

$wanted = $pairs->map(fn($d) => [
    'yy' => (int)$d->format('Y'),
    'month_pro' => $d->format('M'),
    'label' => $d->format('M') . ' ' . $d->format('Y'),
])->values();

// Map the raw data to an associative array for easy lookup
$monthlyDataRaw = tbldataentry::selectRaw('yy, month_pro, COUNT(*) as total_incidents')
    ->whereRaw('LOWER(location) = ?', [strtolower($state)])
    ->where(function($q) use ($wanted) {
        foreach ($wanted as $w) {
            $q->orWhere(function($qq) use ($w){
                $qq->where('yy', $w['yy'])->where('month_pro', $w['month_pro']);
            });
        }
    })
    ->groupBy('yy', 'month_pro')
    ->get()
    ->keyBy(function($row){
        return $row->month_pro.' '.$row->yy;
    });

// Prepare the final labels (ensuring the correct 12-month order)
$chartLabels = $wanted->pluck('label')->values()->toArray();

// Prepare the final counts (using the correct labels to ensure correct order)
$incidentCounts = $wanted->pluck('label')->map(function($lbl) use ($monthlyDataRaw) {
    // Look up the count using the generated label as the key
    $count = $monthlyDataRaw[$lbl]->total_incidents ?? 0;
    return (int)$count;
})->values()->toArray();


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
   $recentIncidents = tbldataentry::select('lga','add_notes','riskindicators','impact','datecreated')
        ->whereRaw('LOWER(location) = ?', [strtolower($state)])
        ->orderBy('datecreated', 'desc') // Order in the database
        ->limit(5) // Get only 5
        ->get();

    // Sort incidents by datecreated (we'll use Carbon to compare dates)
    $recentIncidents = $recentIncidents->sortByDesc(function($incident) {
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


    return view('locationIntelligence', compact('total_incidents', 'availableYears', 'year', 'state', 'mostFrequentRisk','chartLabels','incidentCounts','topRiskLabels','topRiskCounts', 'yearLabels','yearCounts', 'attackLabels','attackCounts', 'mostAffectedLGA','recentIncidents','getStates','stateCrimeIndexScore','crimeTable','automatedInsights'));
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



  $recentIncidents = tbldataentry::select('lga','add_notes','riskindicators','impact','datecreated') // <-- ADDED 'datecreated'

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


    $months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    $now = now(); // Carbon
    $pairs = collect(range(0,11))
        ->map(fn($i) => $now->copy()->subMonths($i))
        ->reverse()
        ->values();

    $wanted = $pairs->map(fn($d) => [
        'yy' => (int)$d->format('Y'),
        'month_pro' => $months[(int)$d->format('n') - 1],
        'label' => $months[(int)$d->format('n') - 1] . ' ' . $d->format('Y'),
    ])->values();

    $monthlyData = tbldataentry::selectRaw('yy, month_pro, COUNT(*) as total_incidents')
        ->whereRaw('LOWER(location) = ?', [strtolower($state)])
        ->where(function($q) use ($wanted) {
            foreach ($wanted as $w) {
                $q->orWhere(function($qq) use ($w){
                    $qq->where('yy', $w['yy'])->where('month_pro', $w['month_pro']);
                });
            }
        })
        ->groupBy('yy','month_pro')
        ->get()
        ->keyBy(function($row){
            return $row->month_pro.' '.$row->yy;
        });

    $chartLabels = $wanted->pluck('label')->values();
    $incidentCounts = $chartLabels->map(fn($lbl) => (int)($monthlyData[$lbl]->total_incidents ?? 0))->values();

    // B) Year series from 2018
    $yearlyData = tbldataentry::selectRaw('yy, COUNT(*) as total_incidents')
        ->whereRaw('LOWER(location) = ?', [strtolower($state)])
        ->where('yy', '>=', 2018)
        ->groupBy('yy')
        ->orderBy('yy','asc')
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

}


