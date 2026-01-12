<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RiskToolController extends Controller
{
    public function index()
    {
        // Get unique states and LGAs for the dropdowns
        $states = DB::table('tbldataentry')
            ->select('location as state', 'lga')
            ->whereNotNull('location')
            ->whereNotNull('lga')
            ->distinct()
            ->get()
            ->groupBy('state')
            ->map(fn($items) => $items->pluck('lga')->unique()->values());

        return view('risk-tool', compact('states'));
    }

    public function analyze(Request $request)
    {
        $lga = $request->input('lga');
        $state = $request->input('state');
        $year = $request->input('year', date('Y'));

        // 1. LGA Specific Stats
        $stats = DB::table('tbldataentry')
            ->where('lga', $lga)
            ->where('eventyear', $year)
            ->selectRaw('count(*) as total, sum(COALESCE(CAST(Casualties_count AS UNSIGNED), 0)) as casualties')
            ->first();

        // 2. Trend Logic (Compared to previous year)
        $prevYearCount = DB::table('tbldataentry')
            ->where('lga', $lga)
            ->where('eventyear', $year - 1)
            ->count();
        $trend = ($prevYearCount > 0) ? round((($stats->total - $prevYearCount) / $prevYearCount) * 100, 1) : 0;

        // 3. Top Risk Factor
        $topIndicator = DB::table('tbldataentry')
            ->where('lga', $lga)
            ->where('eventyear', $year)
            ->select('riskindicators', DB::raw('count(*) as count'))
            ->groupBy('riskindicators')
            ->orderBy('count', 'desc')
            ->first();

        // 4. COMPARATIVE BENCHMARK (State Average)
        $stateAvg = DB::table('tbldataentry')
            ->where('location', $state)
            ->where('eventyear', $year)
            ->selectRaw('count(*) / count(distinct lga) as avg_val')
            ->value('avg_val') ?? 0;

        // 5. Recent Incidents
        $recent = DB::table('tbldataentry')
            ->where('location', $state)
            ->orderBy('eventdateToUse', 'desc')
            ->limit(2)
            ->get(['eventdateToUse', 'add_notes as incident_description', 'lga']);

        $score = $this->calculateRiskScore($stats->total, $stats->casualties, $trend);

        return response()->json([
            'success' => true,
            'score' => $score,
            'total' => $stats->total,
            'casualties' => $stats->casualties ?? 0,
            'top_indicator' => $topIndicator->riskindicators ?? 'N/A',
            'impact_level' => $this->getImpactLabel($score),
            'state_avg' => round($stateAvg, 1),
            'comparison' => ($stats->total > $stateAvg) ? 'higher' : 'lower',
            'recent_incidents' => $recent
        ]);
    }

    private function getImpactLabel($score)
    {
        if ($score >= 75) return 'Critical';
        if ($score >= 50) return 'High';
        if ($score >= 25) return 'Moderate';
        return 'Low';
    }
    private function calculateRiskScore($total, $casualties, $trend)
    {
        // Logic: Weighting incident volume (40%), lethality (40%), and trend (20%)
        $score = ($total * 1.5) + ($casualties * 3) + ($trend > 0 ? 10 : 0);
        return min(100, round($score));
    }
}
