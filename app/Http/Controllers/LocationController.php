<?php

namespace App\Http\Controllers;
use App\Models\tbldataentry;
use App\Models\StateInsight;
use App\Models\tblriskindicators;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LocationController extends Controller
{
public function getTotalIncident($state)
{

    $year = now()->year;
    $availableYears = range(now()->year, 2018);
    $states =StateInsight::all();

    $total_incidents = tbldataentry::whereRaw('LOWER(location) = ?', [strtolower($state)])
            ->where('yy', $year) // <-- ADDED YEAR FILTER
            ->count();

    // Get the most frequent risk indicator and its associated risk factor
    $mostFrequentRisk = tbldataentry::select('riskindicators', DB::raw('COUNT(*) as occurrences'))
                                    ->whereRaw('LOWER(location) = ?', [strtolower($state)])
                                    ->where('yy', $year)
                                    ->groupBy('riskindicators')
                                    ->orderByDesc('occurrences')
                                    ->take(2)
                                    ->get();

    // Get the current month and year
    $currentMonth = date('m'); // Current month (numeric, e.g., 10 for October)
    $currentYear = date('Y');  // Current year (e.g., 2025)

    // ... inside public function getTotalIncident($state)

// List of month names (for reference)
$months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

// Calculate the last 12 months dynamically using Carbon (Same logic as getStateData)
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


  // C) Motive series (Using motives_specific table)
    $motiveData = tbldataentry::join('motives_specific', 'tbldataentry.motive_specific', '=', 'motives_specific.id')
            ->select('motives_specific.name', DB::raw('COUNT(*) as occurrences'))
            ->whereRaw('LOWER(tbldataentry.location) = ?', [strtolower($state)]) // Prefixed location for safety
            ->where('tbldataentry.yy', $year)
            ->groupBy('motives_specific.name')
            ->orderByDesc('occurrences')
            ->get();

    $motiveLabels = $motiveData->pluck('name')->toArray();
    $motiveCounts = $motiveData->pluck('occurrences')->toArray();


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
                                    ->get();

    // Sort incidents by datecreated (we'll use Carbon to compare dates)
    $recentIncidents = $recentIncidents->sortByDesc(function($incident) {
        return \Carbon\Carbon::createFromFormat('M d, Y', $incident->datecreated)->timestamp;
    });

    // Get only the first 5 records (most recent)
    $recentIncidents = $recentIncidents->take(5);

$getStates = StateInsight::pluck('state');


    return view('locationIntelligence', compact('total_incidents', 'availableYears', 'year', 'state', 'mostFrequentRisk','chartLabels','incidentCounts','topRiskLabels','topRiskCounts', 'yearLabels','yearCounts', 'motiveLabels','motiveCounts', 'mostAffectedLGA','recentIncidents','getStates'));
}



public function getStateData(Request $request, $state, $year)
{
    // $data = tbldataentry::selectRaw('location, COUNT(*) as total_incidents, SUM(Casualties_count) as total_deaths, SUM(victim) as total_victims, riskindicators')
    //     ->whereRaw('LOWER(location) = ?', [strtolower($state)])
    //     ->groupBy('location', 'riskindicators')
    //     ->get();

    // $total_incidents = $data->sum('total_incidents');
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


  // Recent 5 (datecreated is text; we sort in PHP in SSR, but here we’ll just take “latest-looking” via created order if you have it.

    // If not, keep this and accept that order may be imperfect — or return all and sort on client.)

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

    // ===== Add the THREE chart series your page needs =====

    // A) Last 12 months (labels + counts)
    // Use your working logic here. Example skeleton:
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

    // C) Motive series
   $motiveData = tbldataentry::join('motives_specific', 'tbldataentry.motive_specific', '=', 'motives_specific.id')
            ->select('motives_specific.name', DB::raw('COUNT(*) as occurrences'))
            ->whereRaw('LOWER(tbldataentry.location) = ?', [strtolower($state)]) // Prefixed location for safety
            ->where('tbldataentry.yy', $year)
            ->groupBy('motives_specific.name')
            ->orderByDesc('occurrences')
            ->get();

    $motiveLabels = $motiveData->pluck('name')->values();
    $motiveCounts = $motiveData->pluck('occurrences')->values();

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
        'motiveLabels'    => $motiveLabels,
        'motiveCounts'    => $motiveCounts,
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


