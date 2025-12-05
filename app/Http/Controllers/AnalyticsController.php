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

    public function getFilterOptions()
    {
        return Cache::remember('analytics_options_v4', 86400, function () {
            return [
                'years' => range((int)date('Y'), 2018),
                'states' => StateInsight::orderBy('state')->pluck('state'),
                'regions' => ['North Central', 'North East', 'North West', 'South East', 'South South', 'South West'],
                'factors' => DB::table('tblriskindicators')->select('factors')->distinct()->whereNotNull('factors')->orderBy('factors')->pluck('factors'),
                'indicators' => tbldataentry::select('riskindicators')->distinct()->orderBy('riskindicators')->pluck('riskindicators'),
                'motives' => DB::table('motive')->orderBy('name')->pluck('name', 'id'),
                'attack_groups' => DB::table('attack_group')->orderBy('name')->pluck('name', 'id'),
                'weapons' => DB::table('weapon_type')->orderBy('name')->pluck('name', 'id'),
            ];
        });
    }

    public function getFilteredStats(Request $request)
    {
        // 1. INPUTS & DEFAULTS
        $startYear = (int) ($request->start_year ?? date('Y'));
        $endYear   = (int) ($request->end_year ?? date('Y'));
        $dimension = $request->dimension ?? 'state';

        // Remove empty strings from array filter
        $selections = $request->selection ? array_filter(explode(',', $request->selection)) : [];

        // 2. BUILD COMPARISON GROUPS
        $comparisonGroups = [];

        // --- CHANGE 1: Handle "All Country" Default ---
        if (empty($selections)) {
            // If no selection, we group EVERYTHING under "All Nigeria"
            // We fetch all distinct locations from the table to ensure we capture everything
            $allStates = StateInsight::pluck('state')->toArray();
            $comparisonGroups['All Nigeria'] = $allStates;
        } else {
            if ($dimension === 'region') {
                foreach ($selections as $region) {
                    $states = $this->getStatesByRegion($region);
                    if($states) $comparisonGroups[$region] = $states;
                }
            } else {
                foreach ($selections as $state) $comparisonGroups[$state] = [$state];
            }
        }

        // 3. BASE QUERY
        $query = tbldataentry::query()->whereBetween('yy', [$startYear, $endYear]);
        $this->applyFilters($query, $request); // Refactored filters to helper method

        // 4. TIMELINE
        $timelineData = $this->chartService->getMultiSeriesData($query, $comparisonGroups, $startYear, $endYear);

        // 5. AGGREGATES PREP
        $allInvolvedStates = Arr::flatten($comparisonGroups);
        $aggregateQuery = (clone $query)->whereIn('location', $allInvolvedStates);

        // --- CHANGE 2: Calculate Card Stats (Incidents, Deaths, Status) ---

        // A. Current Period Stats
        $currentStats = (clone $aggregateQuery)
            ->selectRaw('COUNT(*) as incidents, SUM(Casualties_count) as deaths')
            ->first();

        $totalIncidents = (int)($currentStats->incidents ?? 0);
        $totalDeaths = (int)($currentStats->deaths ?? 0);

        // B. Status Calculation (Compare vs Previous Period)
        $duration = $endYear - $startYear + 1;
        $prevStart = $startYear - $duration;
        $prevEnd = $startYear - 1;

        $prevQuery = tbldataentry::query()
            ->whereBetween('yy', [$prevStart, $prevEnd])
            ->whereIn('location', $allInvolvedStates);
        $this->applyFilters($prevQuery, $request);

        $prevIncidents = $prevQuery->count();

        $status = 'Stable';
        $statusColor = 'text-gray-400';

        if ($prevIncidents > 0) {
            $change = $totalIncidents - $prevIncidents;
            // Threshold of 5% to declare a trend
            $percentChange = ($change / $prevIncidents) * 100;

            if ($percentChange > 5) {
                $status = 'Escalating';
                $statusColor = 'text-red-500'; // Red for bad
            } elseif ($percentChange < -5) {
                $status = 'Improving';
                $statusColor = 'text-emerald-500'; // Green for good
            }
        } elseif ($totalIncidents > 0 && $prevIncidents == 0) {
             $status = 'Escalating';
             $statusColor = 'text-red-500';
        }

        // 6. GENERATE SMART CHARTS
        $factorData = $this->getSmartChartData($request->risk_factor, 'riskfactors', null, $comparisonGroups, $query, $aggregateQuery, 5);
        $riskData = $this->getSmartChartData($request->risk_indicator, 'riskindicators', null, $comparisonGroups, $query, $aggregateQuery, 8);
        $motiveData = $this->getSmartChartData($request->motive_id, 'motive', 'motive', $comparisonGroups, $query, $aggregateQuery, 5);
        $weaponData = $this->getSmartChartData($request->weapon_id, 'weapon_type', 'weapon_type', $comparisonGroups, $query, $aggregateQuery, 5);

        return response()->json([
            'timeline' => $timelineData,
            'factors'  => $factorData,
            'risks'    => $riskData,
            'motives'  => $motiveData,
            'weapons'  => $weaponData,
            'impact'   => [
                'incidents' => $totalIncidents, // New
                'deaths'    => $totalDeaths,    // New
                'status'    => $status,         // New
                'status_color' => $statusColor  // New
            ]
        ]);
    }

    private function applyFilters($query, $request) {
        $query->when($request->risk_factor, fn($q) => $q->where('riskfactors', $request->risk_factor));
        $query->when($request->risk_indicator, fn($q) => $q->where('riskindicators', $request->risk_indicator));
        $query->when($request->attack_group_id, fn($q) => $q->where('attack_group_name', $request->attack_group_id));
        $query->when($request->weapon_id, fn($q) => $q->where('weapon_type', $request->weapon_id));
        $query->when($request->motive_id, fn($q) => $q->where('motive', $request->motive_id));
    }

    // ... (Keep getSmartChartData and getStatesByRegion as they were in your code) ...
    // Note: In getSmartChartData, logic remains valid. If $comparisonGroups has only "All Nigeria",
    // it will return 1 series named "All Nigeria", creating a clean bar chart.

    // Copy the exact private functions from your previous code block here.
    private function getSmartChartData($filterValue, $dbCol, $lookupTable, $comparisonGroups, $baseQuery, $aggQuery, $limit)
    {
       // ... [Paste your existing getSmartChartData logic here] ...
       // It works perfectly with the "All Nigeria" logic.
        $categories = [];
        $series = [];
        $isDrilldown = !empty($filterValue);

        if ($isDrilldown) {
            $categories = array_keys($comparisonGroups);
            $counts = [];
            foreach ($comparisonGroups as $label => $states) {
                $counts[] = (clone $baseQuery)->whereIn('location', $states)->count();
            }
            $series[] = ['name' => 'Incidents', 'data' => $counts];
        } else {
            $catQuery = (clone $aggQuery);
            $selectCol = $dbCol;
            if ($lookupTable) {
                $catQuery->join($lookupTable, "tbldataentry.$dbCol", '=', "$lookupTable.id")
                         ->select("$lookupTable.name as label", "tbldataentry.$dbCol as value");
                $groupByCol = "tbldataentry.$dbCol";
                $orderByCol = "COUNT(*)";
            } else {
                $catQuery->select("$dbCol as label", "$dbCol as value");
                $groupByCol = $dbCol;
                $orderByCol = "COUNT(*)";
            }

            $topItems = $catQuery
                ->groupBy($groupByCol)
                ->when($lookupTable, function($q) use ($lookupTable) {
                      return $q->groupBy("$lookupTable.name");
                })
                ->orderByRaw($orderByCol . ' DESC')
                ->take($limit)
                ->get();

            $categories = $topItems->pluck('label')->toArray();
            $values = $topItems->pluck('value')->toArray();

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
