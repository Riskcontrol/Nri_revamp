<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class IncidentsController extends Controller
{
    // =========================================================================
    // INDEX — listing page
    // =========================================================================

    public function index(Request $request)
    {
        $pageStart = microtime(true);

        $filters = [
            'search'            => trim((string) $request->input('search', '')),
            'location'          => trim((string) $request->input('location', '')),
            'year'              => trim((string) $request->input('year', '')),
            'impact'            => trim((string) $request->input('impact', '')),
            'affected_industry' => trim((string) $request->input('affected_industry', '')),
            // ── Breaking filter bug fix ───────────────────────────────────────
            // The view checkbox posts name="breaking_news" value="1".
            // The old code read input('news') — a different key — so the filter
            // was silently ignored and every row was returned regardless.
            // Fix: read the correct key and translate '1' → 'Yes' for the query.
            'news'     => $request->input('breaking_news') === '1' ? 'Yes' : '',
            'per_page' => (int) $request->input('per_page', 25),
        ];

        $filters['per_page'] = in_array($filters['per_page'], [25, 50, 100], true)
            ? $filters['per_page']
            : 25;

        // ── STEP 1: main incidents query — NO weekly join ─────────────────────
        // The join was the source of the slow page load. Instead we do a
        // fast paginated query on tbldataentry alone, then fetch the news
        // status for the current page's rows in a single targeted query.
        $step1Start = microtime(true);

        $query = DB::table('tbldataentry')
            ->select([
                'ID',
                'eventid',
                'eventdate',
                'eventyear',
                'location',
                'lga',
                'riskfactors',
                'riskindicators',
                'impact',
                'affected_industry',
                'Casualties_count',
                'caption',
                'import_id',
            ]);

        if ($filters['search'] !== '') {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('eventid',      'like', "%{$search}%")
                    ->orWhere('caption',    'like', "%{$search}%")
                    ->orWhere('location',   'like', "%{$search}%")
                    ->orWhere('lga',        'like', "%{$search}%")
                    ->orWhere('riskfactors', 'like', "%{$search}%")
                    ->orWhere('riskindicators', 'like', "%{$search}%");
            });
        }

        if ($filters['location'] !== '') {
            $query->where('location', $filters['location']);
        }
        if ($filters['year'] !== '') {
            $query->where('eventyear', $filters['year']);
        }
        if ($filters['impact'] !== '') {
            $query->where('impact', $filters['impact']);
        }

        // Filter by affected industry — partial match so "Technology" catches
        // "Technology, Financial Services" composite values stored at upload.
        if ($filters['affected_industry'] !== '') {
            $query->where('affected_industry', 'like', '%' . $filters['affected_industry'] . '%');
        }

        // Filter by news status using a fast EXISTS subquery instead of JOIN
        if ($filters['news'] === 'Yes') {
            $query->whereExists(function ($sub) {
                $sub->select(DB::raw(1))
                    ->from('tblweeklydataentry as w')
                    ->whereColumn('w.eventid', 'tbldataentry.eventid')
                    ->where('w.news', 'Yes');
            });
        } elseif ($filters['news'] === 'No') {
            $query->whereNotExists(function ($sub) {
                $sub->select(DB::raw(1))
                    ->from('tblweeklydataentry as w')
                    ->whereColumn('w.eventid', 'tbldataentry.eventid')
                    ->where('w.news', 'Yes');
            });
        }

        $incidents = $query
            ->orderByDesc('ID')
            ->paginate($filters['per_page'])
            ->withQueryString();

        // Fetch weekly news flag only for the rows on this page — one query
        // instead of N+1 or a full JOIN over thousands of rows.
        $eventIds = $incidents->getCollection()
            ->pluck('eventid')
            ->filter()
            ->values()
            ->all();

        $weeklyNewsMap = empty($eventIds)
            ? collect()
            : DB::table('tblweeklydataentry')
            ->select('eventid', 'news', 'id as weekly_id')
            ->whereIn('eventid', $eventIds)
            ->get()
            ->keyBy('eventid');

        $incidents->setCollection(
            $incidents->getCollection()->map(function ($row) use ($weeklyNewsMap) {
                $weekly          = $weeklyNewsMap->get($row->eventid);
                $row->news       = optional($weekly)->news      ?? 'No';
                $row->weekly_id  = optional($weekly)->weekly_id ?? null;
                return $row;
            })
        );

        $step1Ms = round((microtime(true) - $step1Start) * 1000, 1);
        Log::debug('[Incidents] STEP 1 — main incidents paginate (optimized)', [
            'ms'           => $step1Ms,
            'total_rows'   => $incidents->total(),
            'current_page' => $incidents->currentPage(),
            'note'         => $step1Ms > 2000 ? '*** SLOW — check tbldataentry indexes ***' : 'OK',
            'filters'      => array_filter($filters, fn($v) => $v !== '' && $v !== 25),
        ]);

        // ── STEP 2: locations dropdown (cached 10 min) ────────────────────────
        $step2Start = microtime(true);
        $locations = Cache::remember('admin_incidents_locations', 600, function () {
            return DB::table('tbldataentry')
                ->select('location')
                ->whereNotNull('location')
                ->where('location', '!=', '')
                ->groupBy('location')
                ->orderBy('location')
                ->pluck('location');
        });
        $step2Ms = round((microtime(true) - $step2Start) * 1000, 1);
        Log::debug('[Incidents] STEP 2 — locations dropdown', ['ms' => $step2Ms, 'count' => $locations->count()]);

        // ── STEP 3: years dropdown (cached 10 min) ────────────────────────────
        $step3Start = microtime(true);
        $years = Cache::remember('admin_incidents_years', 600, function () {
            return DB::table('tbldataentry')
                ->select('eventyear')
                ->whereNotNull('eventyear')
                ->where('eventyear', '!=', '')
                ->groupBy('eventyear')
                ->orderByDesc('eventyear')
                ->pluck('eventyear');
        });
        $step3Ms = round((microtime(true) - $step3Start) * 1000, 1);
        Log::debug('[Incidents] STEP 3 — years dropdown', ['ms' => $step3Ms, 'count' => $years->count()]);

        // ── STEP 4: total incident count (cached 5 min) ───────────────────────
        $step4Start    = microtime(true);
        $totalIncidents = Cache::remember('admin_incidents_total', 300, function () {
            return DB::table('tbldataentry')->count();
        });
        $step4Ms = round((microtime(true) - $step4Start) * 1000, 1);
        Log::debug('[Incidents] STEP 4 — totalIncidents COUNT', ['ms' => $step4Ms, 'total' => $totalIncidents]);

        // ── STEP 5: breaking news count (cached 5 min) ────────────────────────
        $step5Start       = microtime(true);
        $breakingNewsCount = Cache::remember('admin_incidents_breaking_total', 300, function () {
            return DB::table('tblweeklydataentry')->where('news', 'Yes')->count();
        });
        $step5Ms = round((microtime(true) - $step5Start) * 1000, 1);
        Log::debug('[Incidents] STEP 5 — breakingNewsCount', ['ms' => $step5Ms, 'count' => $breakingNewsCount]);

        // ── STEP 6: affected industry dropdown (cached 10 min) ───────────────
        // Values are comma-separated strings like "Technology, Financial Services".
        // We pull distinct non-empty values and let the view display them as-is.
        $industries = Cache::remember('admin_incidents_industries', 600, function () {
            return DB::table('tbldataentry')
                ->select('affected_industry')
                ->whereNotNull('affected_industry')
                ->where('affected_industry', '!=', '')
                ->groupBy('affected_industry')
                ->orderBy('affected_industry')
                ->pluck('affected_industry');
        });

        Log::debug('[Incidents] PAGE TOTAL', [
            'step1_ms'  => $step1Ms,
            'step2_ms'  => $step2Ms,
            'step3_ms'  => $step3Ms,
            'step4_ms'  => $step4Ms,
            'step5_ms'  => $step5Ms,
            'total_ms'  => round((microtime(true) - $pageStart) * 1000, 1),
        ]);

        return view('admin.incidents.index', compact(
            'incidents',
            'locations',
            'years',
            'industries',
            'totalIncidents',
            'breakingNewsCount',
            'filters'
        ));
    }

    // =========================================================================
    // DELETE SINGLE ROW — removes from both tables, returns JSON
    // =========================================================================

    public function destroy(Request $request)
    {
        $eventid = $request->input('eventid');

        if (! $eventid) {
            return response()->json(['success' => false, 'message' => 'Event ID is required.'], 422);
        }

        DB::transaction(function () use ($eventid) {
            DB::table('tbldataentry')->where('eventid', $eventid)->delete();
            DB::table('tblweeklydataentry')->where('eventid', $eventid)->delete();
        });

        // Bust caches affected by a row deletion
        Cache::forget('admin_incidents_total');
        Cache::forget('admin_incidents_breaking_total');
        Cache::forget('news_active_alerts');

        Log::info('[Incidents] Row deleted', ['eventid' => $eventid, 'admin' => auth()->id()]);

        return response()->json([
            'success' => true,
            'message' => 'Incident deleted from both tables.',
        ]);
    }

    // =========================================================================
    // BULK DELETE — multiple eventids, removes from both tables, returns JSON
    // =========================================================================

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'eventids'   => 'required|array|min:1|max:200',
            'eventids.*' => 'required|string',
        ]);

        $eventids = $request->input('eventids');
        $count    = 0;

        DB::transaction(function () use ($eventids, &$count) {
            $count = DB::table('tbldataentry')->whereIn('eventid', $eventids)->delete();
            DB::table('tblweeklydataentry')->whereIn('eventid', $eventids)->delete();
        });

        // Bust caches affected by bulk deletion
        Cache::forget('admin_incidents_total');
        Cache::forget('admin_incidents_breaking_total');
        Cache::forget('news_active_alerts');

        Log::info('[Incidents] Bulk delete', ['count' => $count, 'admin' => auth()->id()]);

        return response()->json([
            'success' => true,
            'deleted' => $count,
            'message' => "{$count} incident" . ($count !== 1 ? 's' : '') . ' deleted successfully.',
        ]);
    }

    // =========================================================================
    // TOGGLE BREAKING NEWS — flips news = 'Yes'/'No' on tblweeklydataentry
    //
    // After the DB update we immediately bust the news_active_alerts cache so
    // the /news page reflects the change on the very next request — no waiting
    // for the 10-minute TTL to expire.
    // =========================================================================

    public function toggleBreakingNews(Request $request)
    {
        $eventid = $request->input('eventid');

        if (! $eventid) {
            return response()->json(['success' => false, 'message' => 'Event ID is required.'], 422);
        }

        $weekly = DB::table('tblweeklydataentry')->where('eventid', $eventid)->first();

        if (! $weekly) {
            return response()->json([
                'success' => false,
                'message' => 'No weekly record found for this event. The incident exists in tbldataentry but has no linked tblweeklydataentry row.',
            ], 404);
        }

        $newStatus = ($weekly->news === 'Yes') ? 'No' : 'Yes';

        DB::table('tblweeklydataentry')
            ->where('eventid', $eventid)
            ->update(['news' => $newStatus]);

        // ── Instant frontend reflection ───────────────────────────────────────
        // Bust the alerts cache so the /news page picks up the change
        // immediately on the next page load — not after the 10-min TTL.
        Cache::forget('news_active_alerts');

        // Also bust the Breaking News count badge in the admin header
        Cache::forget('admin_incidents_breaking_total');

        Log::info('[Incidents] Breaking news toggled', [
            'eventid'    => $eventid,
            'new_status' => $newStatus,
            'admin'      => auth()->id(),
        ]);

        return response()->json([
            'success'    => true,
            'is_news'    => $newStatus === 'Yes',
            'new_status' => $newStatus,
            'message'    => $newStatus === 'Yes'
                ? 'Marked as Breaking News. Alert now live on the News page.'
                : 'Removed from Breaking News. Alert removed from the News page.',
        ]);
    }
}
