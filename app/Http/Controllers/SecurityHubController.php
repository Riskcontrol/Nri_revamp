<?php

namespace App\Http\Controllers;

use App\Models\tbldataentry;
use App\Models\StateNeighbourhoods;
use App\Models\DataInsights;
use App\Models\NewReport;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class SecurityHubController extends Controller
{
    public function index(Request $request)
    {
        $endDate   = Carbon::now();
        $startDate = Carbon::now()->subDays(7);

        $regionMap = [
            'North West'    => ['Jigawa', 'Kaduna', 'Kano', 'Katsina', 'Kebbi', 'Sokoto', 'Zamfara'],
            'North East'    => ['Adamawa', 'Bauchi', 'Borno', 'Gombe', 'Taraba', 'Yobe'],
            'North Central' => ['Federal Capital Territory', 'Benue', 'Kogi', 'Kwara', 'Nassarawa', 'Niger', 'Plateau'],
            'South West'    => ['Ekiti', 'Lagos', 'Ogun', 'Ondo', 'Osun', 'Oyo'],
            'South East'    => ['Abia', 'Anambra', 'Ebonyi', 'Enugu', 'Imo'],
            'South South'   => ['Akwa Ibom', 'Bayelsa', 'Cross River', 'Delta', 'Edo', 'Rivers'],
        ];

        // ── Incident query ────────────────────────────────────────────────────
        $query = tbldataentry::query()->whereBetween('eventdateToUse', [
            $startDate->format('Y-m-d'),
            $endDate->format('Y-m-d'),
        ]);

        $query->when($request->filled('region'), function ($q) use ($request, $regionMap) {
            $selectedRegion = $request->region;
            if (isset($regionMap[$selectedRegion])) {
                return $q->whereIn('location', $regionMap[$selectedRegion]);
            }
        });

        $rawPage = $query->orderBy('eventdateToUse', 'desc')->paginate(10)->withQueryString();

        // ── FIX: Batch-load StateNeighbourhoods → eliminates N+1 (was 10 queries → 1)
        $neighbourhoodIds = $rawPage->getCollection()
            ->pluck('neighbourhood')
            ->filter()
            ->unique()
            ->values();

        $neighbourhoods = StateNeighbourhoods::whereIn('id', $neighbourhoodIds)
            ->pluck('neighbourhood_name', 'id');

        $incidents = $rawPage->through(function ($incident) use ($neighbourhoods) {
            // Resolved in PHP from pre-loaded map — zero extra queries
            $incident->proper_lga = $neighbourhoods->get($incident->neighbourhood)
                ?? $incident->lga
                ?? 'Unknown';

            $incident->display_risk = filled($incident->associated_risks)
                ? $incident->associated_risks
                : $incident->riskindicators;

            $casualties = $incident->Casualties_count ?? 0;
            $injuries   = $incident->Injuries_count   ?? 0;
            $riskFactor = $incident->riskfactors;
            $indicator  = $incident->riskindicators;

            if (($riskFactor == 'Violent Threats' || $indicator == 'Political Protest') && $casualties > 10 || $casualties > 10 || $injuries > 10) {
                $incident->impact_label = 'Critical';
                $incident->impact_class = 'bg-red-800';
            } elseif (($riskFactor == 'Violent Threats' || $indicator == 'Political Protest') && $casualties > 2 || ($casualties > 5 && $casualties < 10) || ($injuries > 5 && $injuries < 10) || $incident->impact_level == 'High') {
                $incident->impact_label = 'High';
                $incident->impact_class = 'bg-red-600';
            } elseif ($casualties == 1 || $injuries > 2 || $incident->impact_level == 'Medium') {
                $incident->impact_label = 'Medium';
                $incident->impact_class = 'bg-orange-500';
            } else {
                $incident->impact_label = 'Low';
                $incident->impact_class = 'bg-emerald-500';
            }

            return $incident;
        });

        // ── Dashboard stats (cached 5 min — changes as new incidents come in) ─
        $statsKey  = 'hub_stats:' . $startDate->format('Ymd') . ':' . ($request->region ?? 'all');
        $stats = Cache::remember($statsKey, 300, function () use ($startDate, $endDate, $request, $regionMap) {
            $statsQuery = tbldataentry::selectRaw('
                COUNT(*) as total_incidents,
                SUM(CASE WHEN Casualties_count > 0 OR victim > 0 THEN 1 ELSE 0 END) as high_risk_alerts,
                COUNT(DISTINCT location) as states_affected
            ')->whereBetween('eventdateToUse', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);

            if ($request->filled('region') && isset($regionMap[$request->region])) {
                $statsQuery->whereIn('location', $regionMap[$request->region]);
            }

            return $statsQuery->first();
        });

        // ── Latest insights (cached 1 h) ──────────────────────────────────────
        $Insights = Cache::remember('hub_latest_insights', 3600, function () {
            return DataInsights::with('category')->latest()->take(4)->get();
        });

        // ── Featured report (cached 30 min per tier) ───────────────────────────
        $user     = auth()->user();
        $userTier = $user ? (int) $user->tier : 0;

        $featuredReport = Cache::remember("hub_featured_report:{$userTier}", 1800, function () use ($userTier) {
            $report = NewReport::where('is_published', true)
                ->where('min_tier', '<=', max($userTier, 1))
                ->latest()
                ->first();

            if (!$report) {
                $report = NewReport::where('is_published', true)->latest()->first();
            }

            return $report;
        });

        return view('news', [
            'incidents'      => $incidents,
            'regionMap'      => $regionMap,
            'totalIncidents' => $stats->total_incidents  ?? 0,
            'highRiskAlerts' => $stats->high_risk_alerts ?? 0,
            'statesAffected' => $stats->states_affected  ?? 0,
            'Insights'       => $Insights,
            'featuredReport' => $featuredReport,
        ]);
    }

    /**
     * Legacy download route — kept for backward compatibility.
     * @deprecated Use route('reports.download', $report->id) instead.
     */
    public function downloadReport()
    {
        $filename = 'NIGERIA-RISK-INDEX.pdf';
        $path     = storage_path("app/public/reports/{$filename}");

        if (!file_exists($path)) {
            return redirect()->back()->with('error', 'The security report is currently being updated. Please try again shortly.');
        }

        return response()->file($path, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="NIGERIA-RISK-INDEX.pdf"',
        ]);
    }
}
