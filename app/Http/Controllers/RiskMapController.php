<?php

namespace App\Http\Controllers;

// --- Laravel Core ---
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

// --- App Models ---
use App\Models\tbldataentry;
use App\Models\CorrectionFactorForStates;
use App\Models\tblriskindicators; // For dynamic indicators

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

    /**
     * @var \Illuminate\Support\Collection
     */
    private $crimeIndexIndicators;

    /**
     * @var \Illuminate\Support\Collection
     */
    private $propertyThreatIndicators; // <-- 1. NEW PROPERTY

    /**
     * Controller constructor.
     */
    public function __construct()
    {
        $this->crimeIndexIndicators = $this->getCrimeIndexIndicators();
        $this->propertyThreatIndicators = $this->getPropertyThreatIndicators();
    }


    private function getCrimeIndexIndicators()
    {
        try {
            return tblriskindicators::where('author', 'crimeIndex')
                ->orderByRaw('CAST(indicators AS CHAR) ASC')
                ->pluck('indicators');
        } catch (\Exception $e) {
            Log::error("CRITICAL: Could not fetch crime indicators. " . $e->getMessage());
            return collect();
        }
    }


    private function getPropertyThreatIndicators()
    {

        try {
            return tblriskindicators::where('factors', 'Property Threats')
                ->orderByRaw('CAST(indicators AS CHAR) ASC')
                ->pluck('indicators');
        } catch (\Exception $e) {
            Log::error("CRITICAL: Could not fetch property threat indicators. " . $e->getMessage());
            return collect();
        }
    }


    /**
     * Display the main risk map page.
     */
    public function showMapPage()
    {
        return view('risk-map');
    }

    /**
     * Fetch and merge REAL risk data with GeoJSON for the map.
     */
  public function getMapData(Request $request)
    {
        // --- 1. Get Filters ---
        $selectedYear = (int)$request->input('year', now()->year);
        $selectedRiskType = $request->input('risk_type', 'All');

        // --- 2. BUILD FILTER LOGIC (Keep your existing filter logic) ---
        $baseQueryCurrentYear = tbldataentry::where('yy', $selectedYear);

        if ($selectedRiskType === 'Crime') {
            $baseQueryCurrentYear->whereIn('riskindicators', $this->crimeIndexIndicators);
        } elseif ($selectedRiskType === 'Property-Risk') {
            $baseQueryCurrentYear->whereIn('riskindicators', $this->propertyThreatIndicators);
        } else {
            $riskIndicator = $this->riskMapping[$selectedRiskType] ?? null;
            if ($riskIndicator) {
                $baseQueryCurrentYear->where('riskindicators', $riskIndicator);
            }
        }

        // --- Base Query for Previous Year ---
        $previousYear = $selectedYear - 1;
        $baseQueryPreviousYear = tbldataentry::where('yy', $previousYear);
        // ... (Keep your existing previous year filter logic) ...
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

        // --- Get Current Year's Incident Data ---
        $stateDataCurrentYear = (clone $baseQueryCurrentYear)
            ->selectRaw('location, COUNT(*) as total_incidents, SUM(Casualties_count) as total_deaths, SUM(victim) as total_victims, MAX(yy) as yy')
            ->groupBy('location')
            ->get();

        // --- Get Previous Year's Incident Counts ---
        $previousYearIncidents = $baseQueryPreviousYear
            ->selectRaw('location, COUNT(*) as incident_count_prev_year')
            ->groupBy('location')
            ->pluck('incident_count_prev_year', 'location')
            ->toArray();

        // ============================================================
        // --- SMART LOGIC: Determine Metric for "Most Affected LGA" ---
        // ============================================================

        $metricRaw = "COUNT(*) as metric_count"; // Default to Frequency (Incidents)

        // If the risk is highly lethal, use Fatalities. Otherwise, use Frequency.
        if (in_array($selectedRiskType, ['All', 'Terrorism', 'Homicide'])) {
            $metricRaw = "SUM(Casualties_count) as metric_count";
        }
        // Note: Kidnapping, Crime, and Property will default to COUNT(*)
        // This ensures Property Risk map doesn't break due to 0 deaths.

        $lgaQuery = (clone $baseQueryCurrentYear)
            ->select('location', 'lga', DB::raw($metricRaw))
            ->whereNotNull('lga')
            ->where('lga', '!=', '');

        $lgaData = $lgaQuery->groupBy('location', 'lga')
                            ->orderBy('location')
                            ->orderByDesc('metric_count') // Sorts by the chosen metric (Deaths or Incidents)
                            ->get();

        $tempLGAs = [];
        foreach ($lgaData as $row) {
            // Pick the first one (highest value) for each state
            if (!isset($tempLGAs[$row->location])) {
                // Formatting the label so the user knows WHY this LGA was picked
                $label = $row->lga;

                // Optional: Append the count to the name for clarity?
                // e.g., "Chikun (50)"
                // $label = $row->lga . " (" . number_format($row->metric_count) . ")";

                $tempLGAs[$row->location] = [
                    'lga_name' => $label,
                    'count' => $row->metric_count
                ];
            }
        }

        $mostAffectedLGAs = [];
        foreach ($tempLGAs as $state => $data) {
            $mostAffectedLGAs[$state] = $data['lga_name'];
        }
        // ============================================================

        // --- Calculate Risk using the ALGORITHM ---
        $stateRiskReports = $this->calculateStateRisk($stateDataCurrentYear);

        // --- Load GeoJSON file ---
        $geoJsonPath = public_path('nigeria-state.geojson');
        if (!File::exists($geoJsonPath)) {
            Log::error("GeoJSON file not found at: " . $geoJsonPath);
            return response()->json(['error' => 'GeoJSON file not found.'], 404);
        }
        $geoJson = json_decode(File::get($geoJsonPath), true);

        // --- Loop and merge ALL data into GeoJSON ---
        foreach ($geoJson['features'] as &$feature) {
            $stateName = $feature['properties']['name'] ?? 'Unknown';

            $riskData = $stateRiskReports[$stateName] ?? null;

            $currentYearIncidents = $riskData['incident_count'] ?? 0;
            $incidentsPrevYear = $previousYearIncidents[$stateName] ?? 0;
            $mostAffectedLGA = $mostAffectedLGAs[$stateName] ?? 'N/A';

            // Merge all data points
            $feature['properties']['incidents_count'] = $currentYearIncidents;
            $feature['properties']['casualties_count'] = $riskData['sum_casualties'] ?? 0;
            $feature['properties']['victim_count'] = $riskData['sum_victims'] ?? 0;
            $feature['properties']['risk_level'] = $riskData['risk_level'] ?? 0;
            $feature['properties']['composite_index_score'] = $riskData['normalized_ratio'] ?? 0;
            $feature['properties']['incidents_prev_year'] = $incidentsPrevYear;
            $feature['properties']['most_affected_lga'] = $mostAffectedLGA;
            $feature['properties']['filter_risk_type'] = $selectedRiskType;
            $feature['properties']['current_year'] = $selectedYear;
            $feature['properties']['previous_year'] = $previousYear;
        }

        // --- Return the final, merged GeoJSON ---
        return response()->json($geoJson);
    }

    public function getMapCardData(Request $request)
    {
        // --- 1. Get Filters ---
        $selectedYear = (int)$request->input('year', now()->year);
        $selectedRiskType = $request->input('risk_type', 'All');

        if (in_array($selectedRiskType, ['Crime', 'Property-Risk'])) {
            return response()->json([
                'topThreatGroups' => 'No Active Threat Group'
            ]);
        }

        // --- 2. Build Filter Logic ---
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

        // --- 3. Get Top Threat Groups ---
        try {
            // We use the $baseQuery's filters to build our join
            $topThreatGroupsQuery = DB::table('tbldataentry')
                ->join('attack_group', 'tbldataentry.attack_group_name', '=', 'attack_group.id') // <-- CHECK THIS JOIN COLUMN
                ->select('attack_group.name', DB::raw('COUNT(*) as occurrences'))
                ->where('tbldataentry.yy', $selectedYear)
                ->whereRaw('LOWER(attack_group.name) != ?', ['others'])
                ->groupBy('attack_group.name')
                ->orderByDesc('occurrences')
                ->take(5);

            // Apply the same WHERE conditions from the $baseQuery
            foreach ($baseQuery->getQuery()->wheres as $where) {
                if ($where['column'] === 'yy') continue; // Already added
                $topThreatGroupsQuery->where($where['column'], $where['operator'], $where['value']);
            }

            $topThreatGroups = $topThreatGroupsQuery->get()->pluck('name')->implode(', ');

        } catch (\Exception $e) {
            Log::error("Failed to get Top Threat Groups: " . $e->getMessage());
            $topThreatGroups = "Error: Check join column";
        }

        return response()->json([
            'topThreatGroups' => $topThreatGroups ?: 'N/A'
        ]);
    }
}
