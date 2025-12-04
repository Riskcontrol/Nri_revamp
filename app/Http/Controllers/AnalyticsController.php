<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Models\tbldataentry;
use App\Models\StateInsight;
use App\Services\AnalyticsChartService;
use Illuminate\Support\Arr;

class AnalyticsController extends Controller
{
    protected $chartService;

    public function __construct(AnalyticsChartService $chartService)
    {
        $this->chartService = $chartService;
    }

    public function index()
    {
        return view('analytics');
    }

    /**
     * Fetch Filter Options (Cached for Speed)
     */
    public function getFilterOptions()
    {
        return Cache::remember('analytics_options_v4', 86400, function () {
            return [
                'years' => range((int)date('Y'), 2018),
                'states' => StateInsight::orderBy('state')->pluck('state'),
                'regions' => ['North Central', 'North East', 'North West', 'South East', 'South South', 'South West'],
                // Lookup tables for dropdowns
                'factors' => DB::table('tblriskindicators')->select('factors')->distinct()->whereNotNull('factors')->orderBy('factors')->pluck('factors'),
                'indicators' => tbldataentry::select('riskindicators')->distinct()->orderBy('riskindicators')->pluck('riskindicators'),
                'motives' => DB::table('motive')->orderBy('name')->pluck('name', 'id'),
                'attack_groups' => DB::table('attack_group')->orderBy('name')->pluck('name', 'id'),
                'weapons' => DB::table('weapon_type')->orderBy('name')->pluck('name', 'id'),
            ];
        });
    }

    /**
     * Main Analytics Engine
     */
    public function getFilteredStats(Request $request)
    {
        // 1. INPUTS & DEFAULTS
        $startYear = (int) ($request->start_year ?? date('Y'));
        $endYear   = (int) ($request->end_year ?? date('Y'));

        $dimension = $request->dimension ?? 'state';
        $selections = $request->selection ? explode(',', $request->selection) : [];
        if (empty($selections)) $selections = ($dimension === 'state') ? ['Borno'] : ['North East'];

        // 2. BUILD COMPARISON GROUPS
        $comparisonGroups = [];
        if ($dimension === 'region') {
            foreach ($selections as $region) {
                $states = $this->getStatesByRegion($region);
                if($states) $comparisonGroups[$region] = $states;
            }
        } else {
            foreach ($selections as $state) $comparisonGroups[$state] = [$state];
        }

        // 3. BASE QUERY (OPTIMIZED)
        // Note: No joins here. We filter IDs directly on the main table for speed.
        $query = tbldataentry::query()->whereBetween('yy', [$startYear, $endYear]);

        $query->when($request->risk_factor, fn($q) => $q->where('riskfactors', $request->risk_factor));
        $query->when($request->risk_indicator, fn($q) => $q->where('riskindicators', $request->risk_indicator));
        // Note: Assuming 'attack_group_name' stores the ID in your main table
        $query->when($request->attack_group_id, fn($q) => $q->where('attack_group_name', $request->attack_group_id));
        $query->when($request->weapon_id, fn($q) => $q->where('weapon_type', $request->weapon_id));
        $query->when($request->motive_id, fn($q) => $q->where('motive', $request->motive_id));

        // 4. TIMELINE (Always Visible)
        $timelineData = $this->chartService->getMultiSeriesData($query, $comparisonGroups, $startYear, $endYear);

        // 5. AGGREGATES PREP
        $allInvolvedStates = Arr::flatten($comparisonGroups);
        $aggregateQuery = (clone $query)->whereIn('location', $allInvolvedStates);

        // 6. GENERATE SMART CHARTS

        // A. Factors (Raw String Column)
        $factorData = $this->getSmartChartData(
            $request->risk_factor,
            'riskfactors', // Column in Main Table
            null, // No Lookup Table
            $comparisonGroups, $query, $aggregateQuery, 5
        );

        // B. Indicators (Raw String Column)
        $riskData = $this->getSmartChartData(
            $request->risk_indicator,
            'riskindicators',
            null,
            $comparisonGroups, $query, $aggregateQuery, 8
        );

        // C. Motives (ID Column -> Needs Lookup)
        $motiveData = $this->getSmartChartData(
            $request->motive_id,
            'motive',        // Column in Main Table (FK)
            'motive',        // Lookup Table Name
            $comparisonGroups, $query, $aggregateQuery, 5
        );

        // D. Weapons (ID Column -> Needs Lookup)
        $weaponData = $this->getSmartChartData(
            $request->weapon_id,
            'weapon_type',   // Column in Main Table (FK)
            'weapon_type',   // Lookup Table Name
            $comparisonGroups, $query, $aggregateQuery, 5
        );

        // 7. CARDS
        $impactStats = (clone $aggregateQuery)
            ->selectRaw('SUM(Casualties_count) as deaths, SUM(victim) as victims, SUM(Injuries_count) as injuries')
            ->first();

        return response()->json([
            'timeline' => $timelineData,
            'factors'  => $factorData,
            'risks'    => $riskData,
            'motives'  => $motiveData,
            'weapons'  => $weaponData,
            'impact'   => [
                'deaths'   => (int)($impactStats->deaths ?? 0),
                'victims'  => (int)($impactStats->victims ?? 0),
                'injuries' => (int)($impactStats->injuries ?? 0)
            ]
        ]);
    }

    /**
     * SMART PIVOT HELPER
     * Handles switching between "Overview" (Group by Category) and "Drilldown" (Group by Location)
     */
    private function getSmartChartData($filterValue, $dbCol, $lookupTable, $comparisonGroups, $baseQuery, $aggQuery, $limit)
    {
        $categories = [];
        $series = [];
        $isDrilldown = !empty($filterValue);

        if ($isDrilldown) {
            // --- MODE: DRILLDOWN ---
            // User selected a specific item. Show distribution across Locations.
            // X-Axis: Locations
            // Y-Axis: Total Count
            $categories = array_keys($comparisonGroups);
            $counts = [];

            foreach ($comparisonGroups as $label => $states) {
                // $baseQuery already contains the "WHERE col = filter" logic
                $counts[] = (clone $baseQuery)->whereIn('location', $states)->count();
            }
            $series[] = ['name' => 'Incidents', 'data' => $counts];

        } else {
            // --- MODE: OVERVIEW ---
            // No selection. Show Top N Categories stacked by Location.

            // 1. Get Top Categories
            $catQuery = (clone $aggQuery);
            $selectCol = $dbCol; // Default for raw strings

            if ($lookupTable) {
                // Join for IDs
                $catQuery->join($lookupTable, "tbldataentry.$dbCol", '=', "$lookupTable.id")
                         ->select("$lookupTable.name as label", "tbldataentry.$dbCol as value");
                $groupByCol = "tbldataentry.$dbCol";
                $orderByCol = "COUNT(*)";
            } else {
                // Raw Strings
                $catQuery->select("$dbCol as label", "$dbCol as value");
                $groupByCol = $dbCol;
                $orderByCol = "COUNT(*)";
            }

            $topItems = $catQuery
                ->groupBy($groupByCol)
                ->when($lookupTable, function($q) use ($lookupTable) {
                     // If joining, we might need to group by name too for strict SQL modes
                     return $q->groupBy("$lookupTable.name");
                })
                ->orderByRaw($orderByCol . ' DESC')
                ->take($limit)
                ->get();

            $categories = $topItems->pluck('label')->toArray();
            $values = $topItems->pluck('value')->toArray();

            // 2. Build Stacks
            foreach ($comparisonGroups as $labelName => $statesInGroup) {
                $groupCounts = [];
                foreach ($values as $val) {
                    $q = (clone $baseQuery)
                        ->whereIn('location', $statesInGroup)
                        ->where($dbCol, $val);
                    $groupCounts[] = $q->count();
                }
                $series[] = ['name' => $labelName, 'data' => $groupCounts];
            }
        }

        return [
            'categories' => $categories,
            'series' => $series,
            'is_drilldown' => $isDrilldown
        ];
    }

    private function getStatesByRegion($region)
    {
        $zones = [
            'North West' => ['Jigawa', 'Kaduna', 'Kano', 'Katsina', 'Kebbi', 'Sokoto', 'Zamfara'],
            'North East' => ['Adamawa', 'Bauchi', 'Borno', 'Gombe', 'Taraba', 'Yobe'],
            'North Central' => ['Benue', 'FCT', 'Kogi', 'Kwara', 'Nasarawa', 'Niger', 'Plateau'],
            'South West' => ['Ekiti', 'Lagos', 'Ogun', 'Ondo', 'Osun', 'Oyo'],
            'South East' => ['Abia', 'Anambra', 'Ebonyi', 'Enugu', 'Imo'],
            'South South' => ['Akwa Ibom', 'Bayelsa', 'Cross River', 'Delta', 'Edo', 'Rivers'],
        ];
        return $zones[$region] ?? [];
    }
}
