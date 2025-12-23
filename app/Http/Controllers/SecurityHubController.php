<?php

namespace App\Http\Controllers;

use App\Models\tbldataentry;
use App\Models\StateNeighbourhoods;
use Illuminate\Http\Request;
use Carbon\Carbon;

class SecurityHubController extends Controller
{
    public function index(Request $request)
    {
        $endDate = Carbon::now();
        $startDate = Carbon::now()->subDays(7);

        $incidents = tbldataentry::orderBy('eventdateToUse', 'desc')
            ->paginate(10)
            ->through(function ($incident) {
                $incident->proper_lga = StateNeighbourhoods::find($incident->neighbourhood)?->neighbourhood_name
                                        ?? $incident->lga
                                        ?? 'Unknown';

            $incident->display_risk = filled($incident->associated_risks)
                                    ? $incident->associated_risks
                                    : $incident->riskindicators;

                $casualties = $incident->Casualties_count ?? 0;
                $victims = $incident->victim ?? 0;
                $injuries = $incident->Injuries_count ?? 0;
                $riskFactor = $incident->riskfactors;
                $indicator = $incident->riskindicators;

                // Determine Label and Color based on your historical scale
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


        $stats = tbldataentry::selectRaw("
                COUNT(*) as total_incidents,
                SUM(CASE WHEN Casualties_count > 0 OR victim > 0 THEN 1 ELSE 0 END) as high_risk_alerts,
                COUNT(DISTINCT location) as states_affected
            ")
            ->whereBetween('eventdateToUse', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->first();

        return view('news', [
            'incidents' => $incidents,
            'totalIncidents' => $stats->total_incidents ?? 0,
            'highRiskAlerts' => $stats->high_risk_alerts ?? 0,
            'statesAffected' => $stats->states_affected ?? 0
        ]);
    }
}
