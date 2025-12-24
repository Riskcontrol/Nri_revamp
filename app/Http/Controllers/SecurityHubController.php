<?php

namespace App\Http\Controllers;

use App\Models\tbldataentry;
use App\Models\StateNeighbourhoods;
use App\Models\DataInsights;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;


class SecurityHubController extends Controller
{
   public function index(Request $request)
{
    // 1. Define the 7-day window
    $endDate = Carbon::now();
    $startDate = Carbon::now()->subDays(7);

    // 2. Fetch Incidents - FIX: Added whereBetween to filter the list
    $incidents = tbldataentry::whereBetween('eventdateToUse', [
            $startDate->format('Y-m-d'),
            $endDate->format('Y-m-d')
        ])
        ->orderBy('eventdateToUse', 'desc')
        ->paginate(10)
        ->through(function ($incident) {
            // Neighbourhood lookup logic
            $incident->proper_lga = StateNeighbourhoods::find($incident->neighbourhood)?->neighbourhood_name
                                    ?? $incident->lga
                                    ?? 'Unknown';

            // Risk fallback logic
            $incident->display_risk = filled($incident->associated_risks)
                                    ? $incident->associated_risks
                                    : $incident->riskindicators;

            // Severity Variables
            $casualties = $incident->Casualties_count ?? 0;
            $victims = $incident->victim ?? 0;
            $injuries = $incident->Injuries_count ?? 0;
            $riskFactor = $incident->riskfactors;
            $indicator = $incident->riskindicators;

            // Determine Impact Label and Class
            if (($riskFactor == "Violent Threats" || $indicator == "Political Protest") && $casualties > 10 || $casualties > 10 || $injuries > 10) {
                $incident->impact_label = 'Very High';
                $incident->impact_class = 'bg-red-800';
            } elseif (($riskFactor == "Violent Threats" || $indicator == "Political Protest") && $casualties > 2 || ($casualties > 5 && $casualties < 10) || ($injuries > 5 && $injuries < 10) || $incident->impact_level == "High") {
                $incident->impact_label = 'High';
                $incident->impact_class = 'bg-red-600';
            } elseif ($casualties == 1 || $injuries > 2 || $incident->impact_level == "Medium") {
                $incident->impact_label = 'Medium';
                $incident->impact_class = 'bg-orange-500';
            } else {
                $incident->impact_label = 'Low';
                $incident->impact_class = 'bg-emerald-500';
            }

            return $incident;
        });

    // 3. Dashboard Stats
    $stats = tbldataentry::selectRaw("
            COUNT(*) as total_incidents,
            SUM(CASE WHEN Casualties_count > 0 OR victim > 0 THEN 1 ELSE 0 END) as high_risk_alerts,
            COUNT(DISTINCT location) as states_affected
        ")
        ->whereBetween('eventdateToUse', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
        ->first();

        $Insights = DataInsights::with('category')->latest()->take(4)->get();


    return view('news', [
        'incidents' => $incidents,
        'totalIncidents' => $stats->total_incidents ?? 0,
        'highRiskAlerts' => $stats->high_risk_alerts ?? 0,
        'statesAffected' => $stats->states_affected ?? 0,
        'Insights'=>$Insights
    ]);
}



public function downloadReport()
{
    $filename = 'NIGERIA-RISK-INDEX.pdf';
    $path = storage_path("app/public/reports/{$filename}");

    // Safety check to prevent 404 errors
    if (!file_exists($path)) {
        return redirect()->back()->with('error', 'The security report is currently being updated. Please try again shortly.');
    }

 return response()->file($path, [
        'Content-Type' => 'application/pdf',
        'Content-Disposition' => 'inline; filename="NIGERIA-RISK-INDEX.pdf"'
    ]);
}
}
