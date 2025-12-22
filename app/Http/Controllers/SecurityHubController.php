<?php

namespace App\Http\Controllers;

use App\Models\tbldataentry;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SecurityHubController extends Controller
{
    public function index(Request $request)
    {
        // 1. Define the 7-day window for the dashboard stats
        $endDate = Carbon::now();
        $startDate = Carbon::now()->subDays(7);

        // 2. Fetch Dashboard Statistics
        $stats = tbldataentry::selectRaw("
                COUNT(*) as total_incidents,
                SUM(CASE WHEN Casualties_count > 0 OR victim > 0 THEN 1 ELSE 0 END) as high_risk_alerts,
                COUNT(DISTINCT location) as states_affected
            ")
            ->whereBetween('eventdateToUse', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->first();

        // 3. Fetch Paginated Incidents for the Table
        // Mapping: state -> location, neighborhood -> lga, date -> eventdateToUse
        $incidents = tbldataentry::select(
                'id',
                'location',
                'lga',
                'eventdateToUse',
                'add_notes', // Used for 'Incident' column
                'riskindicators',        // Used for 'Associated Risk'
                'Casualties_count',      // For calculating 'Impact'
                'victim',
                'associated_risks'              // For calculating 'Impact'
            )
            ->orderBy('eventdateToUse', 'desc')
            ->paginate(20);

        return view('news', [
            'incidents' => $incidents,
            'totalIncidents' => $stats->total_incidents ?? 0,
            'highRiskAlerts' => $stats->high_risk_alerts ?? 0,
            'statesAffected' => $stats->states_affected ?? 0
        ]);
    }
}
