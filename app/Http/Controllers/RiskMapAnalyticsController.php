<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Models\tbldataentry;
use App\Models\StateInsight;
use App\Models\tblriskindicators;
use App\Http\Controllers\Traits\CalculatesRisk;

class RiskMapAnalyticsController extends Controller
{
    use CalculatesRisk;

    public function index()
    {
        $options = Cache::remember('risk_map_options', 86400, function () {
            return [
                'years' => range((int)date('Y'), 2018),
                'states' => StateInsight::orderBy('state')->pluck('state'),
                'indicators' => tblriskindicators::orderBy('indicators')->pluck('indicators'),
            ];
        });

        return view('risk-map-analytics', compact('options'));
    }

    public function getData(Request $request)
    {
        $year = (int) ($request->year ?? date('Y'));
        $state = $request->state;
        $indicator = $request->indicator;

        // Use Try-Catch to handle any potential DB errors gracefully
        try {
            // ============================================================
            // 1. MAP DATA (Filtered by selected Year, State, Indicator)
            // ============================================================
            $mapQuery = tbldataentry::where('yy', $year);

            // Apply filters to the Map Query
            if ($state && $state !== 'All Nigeria') {
                $mapQuery->where('location', $state);
            }
            if ($indicator && $indicator !== 'All Indicators') {
                $mapQuery->where('riskindicators', $indicator);
            }

            // Group data for the Risk Algorithm
            $stateData = $mapQuery
                ->selectRaw('location, riskindicators, COUNT(*) as total_incidents, SUM(Casualties_count) as total_deaths, SUM(victim) as total_victims')
                ->groupBy('location', 'riskindicators')
                ->get();

            // Calculate Risk Scores
            $riskReports = $this->calculateWeightedStateRisk($stateData);

            // Load & Merge GeoJSON
            $geoJsonPath = public_path('nigeria-state.geojson');
            if (!File::exists($geoJsonPath)) {
                throw new \Exception("GeoJSON file missing.");
            }
            $geoJson = json_decode(File::get($geoJsonPath), true);

            foreach ($geoJson['features'] as &$feature) {
                $sName = $feature['properties']['name'];
                $report = $riskReports[$sName] ?? null;

                $feature['properties']['incident_count'] = $report['incident_count'] ?? 0;
                $feature['properties']['risk_score'] = $report['normalized_ratio'] ?? 0;
                $feature['properties']['risk_level'] = $report['risk_level'] ?? 1;
            }

            // ============================================================
            // 2. CHART DATA (Historical Trend 2018 - Present)
            // ============================================================
            // Instead of months (which fail), we show the trend over ALL years.
            // This gives context to the current year's map.

            $chartQuery = tbldataentry::query(); // Start fresh, ignore the 'year' filter for the chart

            // Apply ONLY State and Indicator filters (so we see the trend for this specific location/risk)
            if ($state && $state !== 'All Nigeria') {
                $chartQuery->where('location', $state);
            }
            if ($indicator && $indicator !== 'All Indicators') {
                $chartQuery->where('riskindicators', $indicator);
            }

            // Group by 'yy' (Year) - This column definitely exists
            $trendData = $chartQuery
                ->selectRaw('yy, COUNT(*) as count')
                ->where('yy', '>=', 2018) // Optional: limit history
                ->groupBy('yy')
                ->orderBy('yy', 'asc')
                ->pluck('count', 'yy')
                ->toArray();

            // Format for Chart.js / ApexCharts
            $chartCategories = []; // e.g., [2018, 2019, 2020...]
            $chartValues = [];     // e.g., [50, 120, 90...]

            // Ensure continuous timeline (fill gaps with 0)
            $minYear = 2018;
            $maxYear = (int)date('Y');

            for ($y = $minYear; $y <= $maxYear; $y++) {
                $chartCategories[] = (string)$y;
                $chartValues[] = $trendData[$y] ?? 0;
            }

            return response()->json([
                'geoJson' => $geoJson,
                'chart' => [
                    'categories' => $chartCategories,
                    'series' => [[
                        'name' => 'Incidents',
                        'data' => $chartValues
                    ]]
                ]
            ]);

        } catch (\Exception $e) {
            Log::error("Risk Map Error: " . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
