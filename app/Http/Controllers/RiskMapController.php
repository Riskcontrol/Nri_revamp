<?php

namespace App\Http\Controllers;

use Illuminate\Support\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

use App\Models\tbldataentry;
use App\Models\CorrectionFactorForStates;
use App\Models\tblriskindicators;

use App\Http\Controllers\Traits\CalculatesRisk;

class RiskMapController extends Controller
{
    use CalculatesRisk;

    private $riskMapping = [
        'All'           => null,
        'Terrorism'     => 'Terrorism',
        'Kidnapping'    => 'Kidnapping',
        'Crime'         => 'Crime',
        'Property-Risk' => 'Property-Risk',
    ];

    private $crimeIndexIndicators;
    private $propertyThreatIndicators;

    public function __construct()
    {
        // Both cached 24 h via Cache::remember inside each helper
        $this->crimeIndexIndicators     = $this->getCrimeIndexIndicators();
        $this->propertyThreatIndicators = $this->getPropertyThreatIndicators();
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Indicator look-ups (cached 24 h)
    // ──────────────────────────────────────────────────────────────────────────

    private function getCrimeIndexIndicators()
    {
        return Cache::remember('indicators_crime', 86400, function () {
            try {
                return tblriskindicators::where('author', 'crimeIndex')
                    ->orderByRaw('CAST(indicators AS CHAR) ASC')
                    ->pluck('indicators');
            } catch (\Exception $e) {
                Log::error('CRITICAL: Could not fetch crime indicators. ' . $e->getMessage());
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
                Log::error('CRITICAL: Could not fetch property threat indicators. ' . $e->getMessage());
                return collect();
            }
        });
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Pages
    // ──────────────────────────────────────────────────────────────────────────

    public function showMapPage()
    {
        return view('risk-map');
    }

    public function getPreviewData(Request $request)
    {
        $request->merge(['year' => (int) now()->year, 'risk_type' => 'Terrorism']);
        return $this->getMapData($request);
    }

    public function getPreviewCardData(Request $request)
    {
        $request->merge(['year' => (int) now()->year, 'risk_type' => 'Terrorism']);
        return $this->getMapCardData($request);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Map data AJAX
    // ──────────────────────────────────────────────────────────────────────────

    public function getMapData(Request $request)
    {
        $user             = auth()->user();
        $selectedRiskType = (string) $request->input('risk_type', 'All');

        if ($user && (int) $user->tier === 1) {
            if ($selectedRiskType !== 'Terrorism') {
                return response()->json([
                    'message' => 'Premium access required. Free users can view Terrorism layer only.',
                    'upgrade' => true,
                    'allowed' => ['Terrorism'],
                ], 403);
            }
        }

        $selectedYear     = (int) $request->input('year', now()->year);
        $selectedRiskType = (string) $request->input('risk_type', 'All');

        $cacheKey = "map_data_{$selectedYear}_{$selectedRiskType}";

        return Cache::remember($cacheKey, 3600, function () use ($selectedYear, $selectedRiskType) {

            // ── 1. Base query ─────────────────────────────────────────────────
            $baseQuery = tbldataentry::query()
                ->where('yy', $selectedYear)
                ->whereNotNull('location')
                ->where('location', '!=', '');

            if ($selectedRiskType === 'Crime') {
                $baseQuery->whereIn('riskindicators', $this->crimeIndexIndicators);
            } elseif ($selectedRiskType === 'Property-Risk') {
                $baseQuery->whereIn('riskindicators', $this->propertyThreatIndicators);
            } else {
                $indicator = $this->riskMapping[$selectedRiskType] ?? null;
                if ($indicator) {
                    $baseQuery->where('riskindicators', $indicator);
                }
            }

            // ── 2. Aggregate + calculate risk ─────────────────────────────────
            if ($selectedRiskType === 'Crime' || $selectedRiskType === 'Property-Risk') {
                $stateData = (clone $baseQuery)
                    ->selectRaw('
                        TRIM(location) AS location,
                        yy,
                        COUNT(*) AS raw_incident_count,
                        COALESCE(SUM(Casualties_count),0) AS raw_casualties_sum,
                        COALESCE(SUM(victim),0) AS raw_victims_sum
                    ')
                    ->groupBy(DB::raw('TRIM(location)'), 'yy')
                    ->get();

                // FIXED: was calling CorrectionFactorForStates::all() directly;
                // now goes through memoized getCorrectionFactors() from trait
                $stateRiskReports = $this->multipleRiskIndicatorCalculation($stateData);
            } else {
                $stateData = (clone $baseQuery)
                    ->selectRaw('
                        TRIM(location) AS location,
                        yy,
                        TRIM(riskindicators) AS risk_indicator,
                        COUNT(*) AS total_incidents,
                        COALESCE(SUM(victim),0) AS total_victims,
                        COALESCE(SUM(Casualties_count),0) AS total_deaths
                    ')
                    ->groupBy(DB::raw('TRIM(location)'), 'yy', DB::raw('TRIM(riskindicators)'))
                    ->get();

                // Boolean arg removed — corrections are always applied (trait memoizes them)
                $stateRiskReports = $this->calculateStateRiskFromIndicators($stateData);
            }

            // ── 3. Previous year incident counts ──────────────────────────────
            $previousYear = $selectedYear - 1;

            $previousYearIncidents = tbldataentry::query()
                ->where('yy', $previousYear)
                ->whereNotNull('location')
                ->where('location', '!=', '')
                ->selectRaw('TRIM(location) AS location, COUNT(*) AS cnt')
                ->groupBy(DB::raw('TRIM(location)'))
                ->pluck('cnt', 'location')
                ->mapWithKeys(fn($v, $k) => [$this->norm($k) => $v])
                ->toArray();

            // ── 4. Most affected LGA ──────────────────────────────────────────
            $lgaData = (clone $baseQuery)
                ->whereNotNull('lga')
                ->where('lga', '!=', '')
                ->selectRaw('TRIM(location) AS location, lga, COUNT(*) AS c')
                ->groupBy(DB::raw('TRIM(location)'), 'lga')
                ->orderByDesc('c')
                ->get();

            $mostAffectedLGAs = [];
            foreach ($lgaData as $row) {
                $key = $this->norm($row->location);
                if (!isset($mostAffectedLGAs[$key])) {
                    $mostAffectedLGAs[$key] = $row->lga;
                }
            }

            // ── 5. Merge with GeoJSON ─────────────────────────────────────────
            $geoJson = json_decode(File::get(public_path('nigeria-state.geojson')), true);

            foreach ($geoJson['features'] as &$feature) {
                $stateName = $feature['properties']['name'] ?? '';
                $stateKey  = $this->norm($stateName);
                $risk      = $stateRiskReports[$stateKey] ?? [];

                $feature['properties']['incidents_count']       = (int) ($risk['incident_count'] ?? 0);
                $feature['properties']['risk_level']            = (int) ($risk['risk_level'] ?? 1);
                $feature['properties']['composite_index_score'] = (float) ($risk['normalized_ratio'] ?? 0);
                $feature['properties']['incidents_prev_year']   = (int) ($previousYearIncidents[$stateKey] ?? 0);
                $feature['properties']['most_affected_lga']     = $mostAffectedLGAs[$stateKey] ?? 'N/A';
                $feature['properties']['filter_risk_type']      = $selectedRiskType;
                $feature['properties']['current_year']          = $selectedYear;
                $feature['properties']['previous_year']         = $previousYear;
            }

            return response()->json($geoJson);
        });
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Card data AJAX
    // ──────────────────────────────────────────────────────────────────────────

    public function getMapCardData(Request $request)
    {
        $user             = auth()->user();
        $selectedRiskType = (string) $request->input('risk_type', 'All');

        if ($user && (int) $user->tier === 1) {
            if ($selectedRiskType !== 'Terrorism') {
                return response()->json([
                    'message' => 'Premium access required. Free users can view Terrorism layer only.',
                    'upgrade' => true,
                    'allowed' => ['Terrorism'],
                ], 403);
            }
        }

        $selectedYear     = (int) $request->input('year', now()->year);
        $selectedRiskType = $request->input('risk_type', 'All');

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

            $topThreatGroups = 'Data Unavailable';
            try {
                $topThreatGroupsQuery = DB::table('tbldataentry')
                    ->join('attack_group', 'tbldataentry.attack_group_name', '=', 'attack_group.id')
                    ->select('attack_group.name', DB::raw('COUNT(*) as occurrences'))
                    ->where('tbldataentry.yy', $selectedYear)
                    ->whereRaw('LOWER(attack_group.name) NOT IN (?, ?)', ['others', 'unknown'])
                    ->groupBy('attack_group.name')
                    ->orderByDesc('occurrences')
                    ->take(5);

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

                $results         = $topThreatGroupsQuery->get();
                $topThreatGroups = $results->pluck('name')->implode(', ') ?: 'N/A';
            } catch (\Exception $e) {
                Log::error('Failed to get Top Threat Groups: ' . $e->getMessage());
            }

            return response()->json(['topThreatGroups' => $topThreatGroups]);
        });
    }
}
