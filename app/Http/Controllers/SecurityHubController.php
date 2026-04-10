<?php

namespace App\Http\Controllers;

use App\Models\tbldataentry;
use App\Models\StateNeighbourhoods;
use App\Models\DataInsights;
use App\Models\NewReport;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SecurityHubController extends Controller
{
    // =========================================================================
    // IMPACT LABEL — single source of truth
    //
    // Mirrors the logic that was previously duplicated inline in the view loop.
    // Used by both fetchActiveAlerts() and the incidents loop so badge colours
    // are always consistent between the alerts section and the table below it.
    // =========================================================================

    private function resolveImpactLabel(
        ?int    $casualties,
        ?int    $injuries,
        ?string $riskFactor,
        ?string $indicator,
        ?string $impactLevel
    ): array {
        $c = (int) ($casualties ?? 0);
        $i = (int) ($injuries   ?? 0);

        if (
            (($riskFactor === 'Violent Threats' || $indicator === 'Political Protest') && $c > 10)
            || $c > 10
            || $i > 10
        ) {
            return ['label' => 'Critical', 'class' => 'bg-red-800'];
        }

        if (
            (($riskFactor === 'Violent Threats' || $indicator === 'Political Protest') && $c > 2)
            || ($c > 5 && $c < 10)
            || ($i > 5 && $i < 10)
            || $impactLevel === 'High'
        ) {
            return ['label' => 'High', 'class' => 'bg-red-600'];
        }

        if ($c === 1 || $i > 2 || $impactLevel === 'Medium') {
            return ['label' => 'Medium', 'class' => 'bg-orange-500'];
        }

        return ['label' => 'Low', 'class' => 'bg-emerald-500'];
    }

    // =========================================================================
    // ACTIVE ALERTS — Breaking News rows for the cards section
    //
    // Source:  tblweeklydataentry WHERE news = 'Yes'
    //          joined to tbldataentry on eventid for the requested columns.
    //
    // The "Breaking News" toggle in the admin panel (/admin/incidents) sets
    // news = 'Yes' / 'No' on tblweeklydataentry. That flag is the single
    // admin-controlled switch that makes a row appear here.
    //
    // Columns used (per brief):
    //   eventdateToUse   date of the incident (Y-m-d → formatted on the way out)
    //   add_notes        main alert summary shown on card + modal description
    //   location         state name
    //   lga              local government area
    //   impact           raw stored value: Low | Medium | High
    //   riskindicators   specific risk type (Kidnapping, Armed Robbery, etc.)
    //
    // Additional columns pulled for a richer modal:
    //   caption          headline / card title
    //   riskfactors      used by resolveImpactLabel() for Critical detection
    //   Casualties_count used by resolveImpactLabel()
    //   Injuries_count   used by resolveImpactLabel()
    //   content          longer weekly narrative for modal body fallback
    //   associated_risks risk assessment text for modal
    //   link1            source link shown in modal
    //
    // Cache: 10 min (600 s).  Cleared automatically on expiry; a newly toggled
    // Breaking News row will appear on the next cache miss.
    // =========================================================================

    private function fetchActiveAlerts(): \Illuminate\Support\Collection
    {
        return Cache::remember('news_active_alerts', 600, function () {

            $rows = DB::table('tblweeklydataentry as w')
                ->join('tbldataentry as e', 'e.eventid', '=', 'w.eventid')
                ->where('w.news', 'Yes')
                ->select([
                    'e.eventid',
                    'e.eventdateToUse',
                    'e.location',
                    'e.lga',
                    'e.caption',
                    'e.add_notes',
                    'e.riskfactors',
                    'e.riskindicators',
                    'e.impact',
                    'e.Casualties_count',
                    'e.Injuries_count',
                    'e.associated_risks',
                    'w.content',          // weekly narrative (optional richer modal body)
                    'w.link1',            // source link
                ])
                ->orderByDesc('e.eventdateToUse')
                ->limit(8)
                ->get();

            return $rows->map(function ($row) {

                // ── Date ──────────────────────────────────────────────────────
                // eventdateToUse is stored as Y-m-d (no time component).
                // We display just the date — no invented time value.
                try {
                    $row->formatted_date = filled($row->eventdateToUse)
                        ? Carbon::parse($row->eventdateToUse)->format('M d, Y')
                        : 'Date unavailable';
                } catch (\Throwable $e) {
                    $row->formatted_date = 'Date unavailable';
                }

                // ── Impact badge ──────────────────────────────────────────────
                // tbldataentry.impact stores 'Low'|'Medium'|'High' (set at upload).
                // resolveImpactLabel() may elevate it to 'Critical' based on
                // casualties / risk-factor logic — same as the table below.
                $resolved = $this->resolveImpactLabel(
                    (int) ($row->Casualties_count ?? 0),
                    (int) ($row->Injuries_count   ?? 0),
                    $row->riskfactors,
                    $row->riskindicators,
                    $row->impact          // stored Low|Medium|High acts as impact_level
                );

                $row->impact_label = $resolved['label'];
                $row->impact_class = $resolved['class'];

                // ── Card title ────────────────────────────────────────────────
                // caption is the headline written at upload time.
                // Fall back to a truncated add_notes if caption is empty.
                $row->card_title = filled($row->caption)
                    ? $row->caption
                    : Str::limit($row->add_notes ?? 'Security Alert', 72);

                // ── Location display ──────────────────────────────────────────
                // Show "State, LGA" on the card; LGA alone if state is missing.
                $row->location_display = trim(
                    collect([$row->location, $row->lga])
                        ->filter(fn($v) => filled($v))
                        ->implode(', ')
                ) ?: 'Nigeria';

                // ── Modal description ─────────────────────────────────────────
                // add_notes is the primary description for both card and modal.
                // w.content (weekly_summary at upload) is used as a richer
                // fallback if present — it can hold a longer narrative.
                $row->modal_description = filled($row->content)
                    ? $row->content
                    : ($row->add_notes ?? '');

                // ── Header sentence fragment ──────────────────────────────────
                // Used by the section heading to build a dynamic context sentence.
                // Format: "Kidnapping in Zamfara" / "Armed Robbery in Lagos, Surulere"
                // If riskindicators is blank, fall back to riskfactors.
                $indicator = filled($row->riskindicators)
                    ? $row->riskindicators
                    : ($row->riskfactors ?? 'Security Incident');

                $row->header_fragment = $indicator . ' in ' . $row->location_display;

                return $row;
            });
        });
    }

    // =========================================================================
    // INDEX
    // =========================================================================

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

        // ── Active alerts (Breaking News = 'Yes') ─────────────────────────────
        // Fetched outside the region-filtered query so alerts from all regions
        // always show — the cards section is not filtered by region.
        $activeAlerts = $this->fetchActiveAlerts();

        // ── Incident query (paginated table) ─────────────────────────────────
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

        // Batch-load StateNeighbourhoods — eliminates N+1
        $neighbourhoodIds = $rawPage->getCollection()
            ->pluck('neighbourhood')
            ->filter()
            ->unique()
            ->values();

        $neighbourhoods = StateNeighbourhoods::whereIn('id', $neighbourhoodIds)
            ->pluck('neighbourhood_name', 'id');

        $incidents = $rawPage->through(function ($incident) use ($neighbourhoods) {
            $incident->proper_lga = $neighbourhoods->get($incident->neighbourhood)
                ?? $incident->lga
                ?? 'Unknown';

            $incident->display_risk = filled($incident->associated_risks)
                ? $incident->associated_risks
                : $incident->riskindicators;

            $resolved = $this->resolveImpactLabel(
                $incident->Casualties_count,
                $incident->Injuries_count,
                $incident->riskfactors,
                $incident->riskindicators,
                $incident->impact_level ?? null
            );

            $incident->impact_label = $resolved['label'];
            $incident->impact_class = $resolved['class'];

            return $incident;
        });

        // ── Dashboard stats (cached 5 min) ────────────────────────────────────
        $statsKey = 'hub_stats:' . $startDate->format('Ymd') . ':' . ($request->region ?? 'all');
        $stats = Cache::remember($statsKey, 300, function () use ($startDate, $endDate, $request, $regionMap) {
            $statsQuery = tbldataentry::selectRaw('
                COUNT(*) as total_incidents,
                SUM(CASE WHEN Casualties_count > 5 OR victim > 3 THEN 1 ELSE 0 END) as high_risk_alerts,
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

        // ── Featured report (cached 30 min per tier) ──────────────────────────
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
            'activeAlerts'   => $activeAlerts,   // Collection of Breaking News rows
        ]);
    }

    /**
     * Legacy download route.
     * @deprecated Use route('reports.download', $report->id) instead.
     */
    /**
     * Public alert detail page — /security-alert/{eventid}
     *
     * Shown when users click "View Full Alert Details" on the announcement
     * banner. Pulls the full incident from both tables and renders a
     * dedicated page. Falls back to a 404 if the eventid is unknown.
     *
     * If no eventid is linked in the banner (manual headline mode), the
     * banner links to /news instead (handled in layout.blade.php).
     */
    public function showAlert(string $eventid)
    {
        $incident = DB::table('tbldataentry as e')
            ->leftJoin('tblweeklydataentry as w', 'w.eventid', '=', 'e.eventid')
            ->where('e.eventid', $eventid)
            ->select([
                'e.eventid',
                'e.caption',
                'e.location',
                'e.lga',
                'e.riskfactors',
                'e.riskindicators',
                'e.impact',
                'e.eventdateToUse',
                'e.Casualties_count',
                'e.Injuries_count',
                'e.add_notes',
                'e.associated_risks',
                'e.business_advisory',
                'e.affected_industry',
                'e.latitude',
                'e.longitude',
                'w.content as weekly_summary',
                'w.impact_rationale',
                'w.link1 as source_link',
            ])
            ->first();

        abort_if(!$incident, 404, 'Alert not found.');

        // Format date
        try {
            $incident->formatted_date = $incident->eventdateToUse
                ? Carbon::parse($incident->eventdateToUse)->format('l, F j, Y')
                : null;
        } catch (\Exception $e) {
            $incident->formatted_date = null;
        }

        // Resolve impact badge
        $resolved = $this->resolveImpactLabel(
            (int) ($incident->Casualties_count ?? 0),
            (int) ($incident->Injuries_count   ?? 0),
            $incident->riskfactors,
            $incident->riskindicators,
            $incident->impact
        );
        $incident->impact_label = $resolved['label'];
        $incident->impact_class = $resolved['class'];

        // Grab the current announcement for the banner so it stays visible
        // on this page too
        $announcement = Cache::get('site_announcement');

        return view('security-alert', compact('incident', 'announcement'));
    }

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
