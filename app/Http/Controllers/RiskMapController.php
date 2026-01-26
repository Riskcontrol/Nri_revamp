<?php

namespace App\Http\Controllers;

// --- Laravel Core ---
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache; // <-- Added Cache Facade

// --- App Models ---
use App\Models\tbldataentry;
use App\Models\CorrectionFactorForStates;
use App\Models\tblriskindicators;

// --- App Traits ---
use App\Http\Controllers\Traits\CalculatesRisk;

class RiskMapController extends Controller
{
    use CalculatesRisk;

    private $riskMapping = [
        'All' => null,
        'Terrorism' => 'Terrorism',
        'Kidnapping' => 'Kidnapping',
        'Homicide' => 'Homicide',
        'Crime' => 'Crime',
        'Property-Risk' => 'Property-Risk',
    ];

    private $crimeIndexIndicators;
    private $propertyThreatIndicators;

    public function __construct()
    {
        $this->crimeIndexIndicators = $this->getCrimeIndexIndicators();
        $this->propertyThreatIndicators = $this->getPropertyThreatIndicators();
    }

    private function getCrimeIndexIndicators()
    {
        // Cache these lookups to save DB calls on every request
        return Cache::remember('indicators_crime', 86400, function () {
            try {
                return tblriskindicators::where('author', 'crimeIndex')
                    ->orderByRaw('CAST(indicators AS CHAR) ASC')
                    ->pluck('indicators');
            } catch (\Exception $e) {
                Log::error("CRITICAL: Could not fetch crime indicators. " . $e->getMessage());
                return collect();
            }
        });
    }

    private function getPropertyThreatIndicators()
    {
        return Cache::remember('indicators_property', 86400, function () {
            try {
                return tblriskindicators::where('factors', 'Property Threats')
                    ->orderByRaw('CAST(indicators AS CHAR) ASC')
                    ->pluck('indicators');
            } catch (\Exception $e) {
                Log::error("CRITICAL: Could not fetch property threat indicators. " . $e->getMessage());
                return collect();
            }
        });
    }

    public function showMapPage()
    {
        return view('risk-map');
    }

    /**
     * Fetch and merge REAL risk data with GeoJSON for the map.
     */
    public function getMapData(Request $request)
    {
        $selectedYear     = (int) $request->input('year', now()->year);
        $selectedRiskType = (string) $request->input('risk_type', 'All');

        // CACHING: Unique key for this specific filter combo
        $cacheKey = "map_data_{$selectedYear}_{$selectedRiskType}";

        return Cache::remember($cacheKey, 3600, function () use ($selectedYear, $selectedRiskType) {

            // ---------------------------------------------
            // 0) Decide correction policy for the map index
            // ---------------------------------------------
            // Recommended: Composite ("All") = UNADJUSTED, others = ADJUSTED
            // If you want Crime / Property-Risk to also be unadjusted, include them in this condition.
            $applyCorrections = !in_array($selectedRiskType, ['All']);
            // e.g. alternative:
            // $applyCorrections = !in_array($selectedRiskType, ['All', 'Crime', 'Property-Risk']);

            // ---------------------------------------------
            // 1) BUILD BASE QUERY (filters only)
            // ---------------------------------------------
            $baseQueryCurrentYear = tbldataentry::query()
                ->where('yy', $selectedYear)
                ->whereNotNull('location')
                ->where('location', '!=', '');

            if ($selectedRiskType === 'Crime') {
                $baseQueryCurrentYear->whereIn('riskindicators', $this->crimeIndexIndicators);
            } elseif ($selectedRiskType === 'Property-Risk') {
                $baseQueryCurrentYear->whereIn('riskindicators', $this->propertyThreatIndicators);
            } else {
                $riskIndicator = $this->riskMapping[$selectedRiskType] ?? null; // e.g. Terrorism
                if ($riskIndicator) {
                    $baseQueryCurrentYear->where('riskindicators', $riskIndicator);
                }
            }

            // ---------------------------------------------
            // 2) AGGREGATE TO THE SHAPE THE TRAIT EXPECTS
            //    (location, yy, risk_indicator, totals...)
            // ---------------------------------------------
            $stateDataCurrentYear = (clone $baseQueryCurrentYear)
                ->selectRaw('
                TRIM(location) as location,
                yy,
                TRIM(riskindicators) as risk_indicator,
                COUNT(*) as total_incidents,
                COALESCE(SUM(victim),0) as total_victims,
                COALESCE(SUM(Casualties_count),0) as total_deaths
            ')
                ->groupBy(DB::raw('TRIM(location)'), 'yy', DB::raw('TRIM(riskindicators)'))
                ->get();

            // ---------------------------------------------
            // 3) CALCULATE RISK (new trait engine)
            // ---------------------------------------------
            // IMPORTANT: returns array keyed by normalized state name (from $this->norm())
            $stateRiskReports = $this->calculateStateRiskFromIndicators($stateDataCurrentYear, $applyCorrections);

            // ---------------------------------------------
            // 4) PREVIOUS YEAR INCIDENTS (for tooltips)
            // ---------------------------------------------
            $previousYear = $selectedYear - 1;

            $baseQueryPreviousYear = tbldataentry::query()
                ->where('yy', $previousYear)
                ->whereNotNull('location')
                ->where('location', '!=', '');

            // apply same filter logic
            if ($selectedRiskType === 'Crime') {
                $baseQueryPreviousYear->whereIn('riskindicators', $this->crimeIndexIndicators);
            } elseif ($selectedRiskType === 'Property-Risk') {
                $baseQueryPreviousYear->whereIn('riskindicators', $this->propertyThreatIndicators);
            } else {
                $riskIndicator = $this->riskMapping[$selectedRiskType] ?? null;
                if ($riskIndicator) {
                    $baseQueryPreviousYear->where('riskindicators', $riskIndicator);
                }
            }

            $previousYearIncidents = $baseQueryPreviousYear
                ->selectRaw('TRIM(location) as location, COUNT(*) as incident_count_prev_year')
                ->groupBy(DB::raw('TRIM(location)'))
                ->pluck('incident_count_prev_year', 'location')
                ->toArray();

            // ---------------------------------------------
            // 5) MOST AFFECTED LGA (same logic)
            // ---------------------------------------------
            $metricRaw = "COUNT(*) as metric_count";
            if (in_array($selectedRiskType, ['All', 'Terrorism', 'Homicide'])) {
                $metricRaw = "SUM(Casualties_count) as metric_count";
            }

            $lgaData = (clone $baseQueryCurrentYear)
                ->select('location', 'lga', DB::raw($metricRaw))
                ->whereNotNull('lga')
                ->where('lga', '!=', '')
                ->groupBy('location', 'lga')
                ->orderBy('location')
                ->orderByDesc('metric_count')
                ->get();

            $mostAffectedLGAs = [];
            $tempStateTracker = [];
            foreach ($lgaData as $row) {
                if (!isset($tempStateTracker[$row->location])) {
                    $mostAffectedLGAs[$row->location] = $row->lga;
                    $tempStateTracker[$row->location] = true;
                }
            }

            // ---------------------------------------------
            // 6) MERGE WITH GEOJSON
            // ---------------------------------------------
            $geoJsonPath = public_path('nigeria-state.geojson');
            if (!File::exists($geoJsonPath)) {
                Log::error("GeoJSON file not found at: " . $geoJsonPath);
                return response()->json(['error' => 'GeoJSON file not found.'], 404);
            }

            $geoJson = json_decode(File::get($geoJsonPath), true);

            foreach ($geoJson['features'] as &$feature) {
                $stateName = $feature['properties']['name'] ?? 'Unknown';

                // Trait keys are normalized; normalize GeoJSON state name to match
                $stateKey = $this->norm($stateName);

                $riskData = $stateRiskReports[$stateKey] ?? null;

                $currentYearIncidents = $riskData['incident_count'] ?? 0;
                $incidentsPrevYear    = $previousYearIncidents[$stateName] ?? ($previousYearIncidents[$stateKey] ?? 0);
                $mostAffectedLGA      = $mostAffectedLGAs[$stateName] ?? ($mostAffectedLGAs[$stateKey] ?? 'N/A');

                $feature['properties']['incidents_count']        = $currentYearIncidents;
                $feature['properties']['risk_level']             = $riskData['risk_level'] ?? 1;
                $feature['properties']['composite_index_score']  = $riskData['normalized_ratio'] ?? 0;
                $feature['properties']['incidents_prev_year']    = $incidentsPrevYear;
                $feature['properties']['most_affected_lga']      = $mostAffectedLGA;

                $feature['properties']['filter_risk_type'] = $selectedRiskType;
                $feature['properties']['current_year']     = $selectedYear;
                $feature['properties']['previous_year']    = $previousYear;
                $feature['properties']['corrections_used'] = $applyCorrections ? 1 : 0; // helpful for debugging in UI
            }

            return response()->json($geoJson);
        });
    }


    public function getMapCardData(Request $request)
    {
        $selectedYear = (int)$request->input('year', now()->year);
        $selectedRiskType = $request->input('risk_type', 'All');

        // Short-circuit for types that likely don't have "Groups" (e.g. Theft)
        if (in_array($selectedRiskType, ['Crime', 'Property-Risk'])) {
            return response()->json([
                'topThreatGroups' => 'N/A'
            ]);
        }

        return Cache::remember("map_card_{$selectedYear}_{$selectedRiskType}", 3600, function () use ($selectedYear, $selectedRiskType) {

            $baseQuery = tbldataentry::where('yy', $selectedYear);

            if ($selectedRiskType === 'Crime') {
                $baseQuery->whereIn('riskindicators', $this->crimeIndexIndicators);
            } elseif ($selectedRiskType === 'Property-Risk') {
                $baseQuery->whereIn('riskindicators', $this->propertyThreatIndicators);
            } else {
                $riskIndicator = $this->riskMapping[$selectedRiskType] ?? null;
                if ($riskIndicator) {
                    $baseQuery->where('riskindicators', $riskIndicator);
                }
            }

            try {
                // Clone base query logic to apply same filters to the Join
                $topThreatGroupsQuery = DB::table('tbldataentry')
                    ->join('attack_group', 'tbldataentry.attack_group_name', '=', 'attack_group.id')
                    ->select('attack_group.name', DB::raw('COUNT(*) as occurrences'))
                    ->where('tbldataentry.yy', $selectedYear)
                    ->whereRaw('LOWER(attack_group.name) NOT IN (?, ?)', ['others', 'unknown']) // Exclude generic names
                    ->groupBy('attack_group.name')
                    ->orderByDesc('occurrences')
                    ->take(5);

                // Manually apply the Risk Indicator filters from the Eloquent query to this DB::table query
                if ($selectedRiskType === 'Crime') {
                    $topThreatGroupsQuery->whereIn('tbldataentry.riskindicators', $this->crimeIndexIndicators);
                } elseif ($selectedRiskType === 'Property-Risk') {
                    $topThreatGroupsQuery->whereIn('tbldataentry.riskindicators', $this->propertyThreatIndicators);
                } else {
                    $riskIndicator = $this->riskMapping[$selectedRiskType] ?? null;
                    if ($riskIndicator) {
                        $topThreatGroupsQuery->where('tbldataentry.riskindicators', $riskIndicator);
                    }
                }

                $results = $topThreatGroupsQuery->get();
                $topThreatGroups = $results->pluck('name')->implode(', ');
            } catch (\Exception $e) {
                Log::error("Failed to get Top Threat Groups: " . $e->getMessage());
                $topThreatGroups = "Data Unavailable";
            }

            return response()->json([
                'topThreatGroups' => $topThreatGroups ?: 'N/A'
            ]);
        });
    }
}
