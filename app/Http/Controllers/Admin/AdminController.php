<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DataImport;
use App\Models\DataInsights;
use App\Models\DataInsightsCategory;
use App\Models\EnterpriseAccessRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class AdminController extends Controller
{
    // =========================================================================
    // DASHBOARD
    // =========================================================================

    public function index()
    {
        $currentYear  = Carbon::now()->year;
        $currentMonth = Carbon::now()->month;
        $lastMonth    = Carbon::now()->subMonth();

        $totalIncidents = DB::table('tbldataentry')->count();

        $incidentsThisMonth = DB::table('tbldataentry')
            ->where('eventyear', $currentYear)->where('eventmonth', $currentMonth)->count();

        $incidentsLastMonth = DB::table('tbldataentry')
            ->where('eventyear', $lastMonth->year)->where('eventmonth', $lastMonth->month)->count();

        $incidentDelta = $incidentsLastMonth > 0
            ? round((($incidentsThisMonth - $incidentsLastMonth) / $incidentsLastMonth) * 100, 1)
            : null;

        $totalUsers        = User::count();
        $newUsersThisMonth = User::whereYear('created_at', $currentYear)->whereMonth('created_at', $currentMonth)->count();
        $totalInsights     = DataInsights::count();
        $totalImports      = DataImport::count();
        $lastImport        = DataImport::orderBy('created_at', 'desc')->first();

        $pendingEnterpriseRequests = EnterpriseAccessRequest::where('status', 'pending')->count();

        $monthlyTrend = DB::table('tbldataentry')
            ->select(DB::raw('eventmonth as month'), DB::raw('count(*) as total'))
            ->where('eventyear', $currentYear)
            ->groupBy('eventmonth')->orderBy('eventmonth')
            ->pluck('total', 'month')->toArray();

        $sparklineData = [];
        for ($m = 1; $m <= 12; $m++) {
            $sparklineData[] = $monthlyTrend[$m] ?? 0;
        }

        $topStates = DB::table('tbldataentry')
            ->select(DB::raw('TRIM(location) as state'), DB::raw('count(*) as total'))
            ->where('eventyear', $currentYear)->whereNotNull('location')->where('location', '!=', '')
            ->groupBy(DB::raw('TRIM(location)'))->orderByDesc('total')->limit(5)->get();

        $topFactors = DB::table('tbldataentry')
            ->select(DB::raw('TRIM(riskfactors) as factor'), DB::raw('count(*) as total'))
            ->where('eventyear', $currentYear)->whereNotNull('riskfactors')->where('riskfactors', '!=', '')
            ->groupBy(DB::raw('TRIM(riskfactors)'))->orderByDesc('total')->limit(5)->get();

        $maxFactor     = $topFactors->max('total') ?: 1;
        $recentImports = DataImport::orderBy('created_at', 'desc')->limit(5)->get();

        $recentIncidents = DB::table('tblweeklydataentry')
            ->select(['eventid', 'caption', 'location', 'riskfactor', 'dyear', 'datecorrected', 'Casualties_count'])
            ->where('news', 'No')->orderBy('ID', 'desc')->limit(8)->get();

        // ── Announcement banner current state ─────────────────────────────────
        $announcement = Cache::get('site_announcement');

        return view('admin.dashboard', compact(
            'totalIncidents',
            'incidentsThisMonth',
            'incidentDelta',
            'totalUsers',
            'newUsersThisMonth',
            'totalInsights',
            'totalImports',
            'lastImport',
            'pendingEnterpriseRequests',
            'sparklineData',
            'topStates',
            'topFactors',
            'maxFactor',
            'recentImports',
            'recentIncidents',
            'currentYear',
            'announcement'
        ));
    }

    // =========================================================================
    // USER MANAGEMENT
    // =========================================================================

    // Valid tier values for the application
    private const VALID_TIERS = [
        1 => 'Tier 1 — Free',
        2 => 'Tier 2 — Standard',
        3 => 'Tier 3 — Premium',
    ];

    public function users()
    {
        $users = User::orderBy('created_at', 'desc')->paginate(15);
        return view('admin.users.index', compact('users'));
    }

    public function destroyUser(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }
        $user->delete();
        return back()->with('success', 'User deleted successfully.');
    }

    /**
     * Update a user's tier level (admin only — protected by 'admin' middleware on the route group).
     *
     * POST /admin/users/{user}/tier
     */
    public function updateUserTier(Request $request, User $user)
    {
        $request->validate([
            'tier' => ['required', 'integer', 'in:' . implode(',', array_keys(self::VALID_TIERS))],
        ]);

        $oldTier = $user->tier;
        $newTier = (int) $request->tier;

        $user->update(['tier' => $newTier]);

        Log::info('[AdminController] User tier updated', [
            'admin_id'  => auth()->id(),
            'user_id'   => $user->id,
            'user_email' => $user->email,
            'old_tier'  => $oldTier,
            'new_tier'  => $newTier,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success'  => true,
                'message'  => "Tier updated to " . (self::VALID_TIERS[$newTier] ?? "Tier {$newTier}") . ".",
                'new_tier' => $newTier,
                'tier_label' => self::VALID_TIERS[$newTier] ?? "Tier {$newTier}",
            ]);
        }

        return back()->with('success', "User tier updated to " . (self::VALID_TIERS[$newTier] ?? "Tier {$newTier}") . ".");
    }

    // =========================================================================
    // INSIGHT MANAGEMENT
    // =========================================================================

    public function insights()
    {
        $insights = DataInsights::with('category')->orderBy('created_at', 'desc')->paginate(10);
        return view('admin.insights.index', compact('insights'));
    }

    public function editInsight($id)
    {
        $insight    = DataInsights::findOrFail($id);
        $categories = DataInsightsCategory::all();
        return view('admin.insights.edit', compact('insight', 'categories'));
    }

    public function updateInsight(Request $request, $id)
    {
        $insight = DataInsights::findOrFail($id);

        $request->validate([
            'title'       => 'required|string|max:255',
            'state'       => 'required|string',
            'description' => 'required|string',
            'content'     => 'required',
        ]);

        $insight->update([
            'title'         => $request->title,
            'state'         => $request->state,
            'category_id'   => $request->category_id,
            'description'   => $request->description,
            'content'       => $request->content,
            'lastupdatedby' => auth()->id(),
        ]);

        return redirect()->route('admin.insights.index')->with('success', 'Insight updated successfully.');
    }

    public function destroyInsight($id)
    {
        $insight = DataInsights::findOrFail($id);

        if ($insight->featureimage) {
            Storage::delete($insight->featureimage);
        }

        $insight->delete();
        return back()->with('success', 'Insight deleted successfully.');
    }

    // =========================================================================
    // GLOBAL ANNOUNCEMENT BANNER
    //
    // The banner is stored as a JSON blob in the cache under the key
    // 'site_announcement'. No migration or DB table is needed — the cache
    // is the single source of truth. TTL is forever (survives restarts if
    // a persistent cache driver like Redis or database is configured).
    //
    // The public layout reads this key on every page load. Because cache reads
    // are O(1) memory lookups (or a single Redis GET), there is negligible
    // overhead even on high-traffic pages.
    //
    // Admin routes:
    //   POST   /admin/announcement         → save/update and enable
    //   DELETE /admin/announcement         → disable (forget from cache)
    // =========================================================================

    public function updateAnnouncement(Request $request)
    {
        $request->validate([
            'headline'     => 'required|string|max:160',
            'location'     => 'nullable|string|max:100',
            'time'         => 'nullable|string|max:60',
            'impact_level' => 'nullable|in:critical,high,medium',
            'eventid'      => 'nullable|string|max:30',
        ]);

        // ── If an incident was selected, enrich from the DB ───────────────────
        $incidentData = null;
        if ($request->filled('eventid')) {
            $inc = DB::table('tbldataentry as e')
                ->leftJoin('tblweeklydataentry as w', 'w.eventid', '=', 'e.eventid')
                ->where('e.eventid', $request->eventid)
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
                    'w.content as weekly_summary',
                    'w.impact_rationale',
                ])
                ->first();

            if ($inc) {
                $incidentData = (array) $inc;
            }
        }

        $announcement = [
            'active'          => true,
            'headline'        => trim($request->headline),
            'location'        => trim($request->location ?? $incidentData['location'] ?? ''),
            'time'            => trim($request->time ?? ''),
            'impact_level'    => $request->impact_level ?? 'critical',
            'eventid'         => $request->eventid,
            'incident'        => $incidentData,   // full row for the detail page
            'updated_at'      => now()->toIso8601String(),
            'updated_by'      => auth()->user()->name ?? auth()->user()->email,
        ];

        Cache::forever('site_announcement', $announcement);

        Log::info('[Admin] Announcement banner updated', [
            'admin'    => auth()->id(),
            'headline' => $announcement['headline'],
            'eventid'  => $announcement['eventid'],
        ]);

        return response()->json([
            'success'      => true,
            'message'      => 'Announcement banner is now live.',
            'announcement' => $announcement,
        ]);
    }

    public function deleteAnnouncement()
    {
        Cache::forget('site_announcement');

        Log::info('[Admin] Announcement banner cleared', ['admin' => auth()->id()]);

        return response()->json([
            'success' => true,
            'message' => 'Announcement banner has been removed from the site.',
        ]);
    }
}
