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
        $currentYear = 2024;
        $lastYear = 2023;

        // 1. Basic Stats
        $stats = DB::table('tbldataentry')
            ->where('lga', $lga)
            ->selectRaw('count(*) as total, sum(CAST(Casualties_count AS UNSIGNED)) as casualties')
            ->first();

        // 2. Trend (YoY)
        $thisYear = DB::table('tbldataentry')->where('lga', $lga)->where('eventyear', $currentYear)->count();
        $prevYear = DB::table('tbldataentry')->where('lga', $lga)->where('eventyear', $lastYear)->count();
        $trend = ($prevYear > 0) ? round((($thisYear - $prevYear) / $prevYear) * 100, 1) : 0;

        // 3. Distribution for Chart
        $distribution = DB::table('tbldataentry')
            ->where('lga', $lga)
            ->select('riskindicators as label', DB::raw('count(*) as value'))
            ->groupBy('riskindicators')
            ->orderBy('value', 'desc')
            ->get();

        // 4. Detailed Advisory & Latest Incident Note
        $report = DB::table('tbldataentry')
            ->where('lga', $lga)
            ->whereNotNull('business_advisory')
            ->orderBy('eventdateToUse', 'desc')
            ->first();

        return response()->json([
            'total' => $stats->total,
            'casualties' => $stats->casualties ?? 0,
            'trend' => $trend,
            'score' => $this->calculateRiskScore($stats->total, $stats->casualties, $trend),
            'distribution' => $distribution,
            'advisory' => $report->business_advisory ?? "No specific local advisory available for this period.",
            'recent_note' => $report->add_notes ?? "Monitoring active in this corridor.",
            'impact_level' => $report->impact ?? 'Low'
        ]);
    }

    private function calculateRiskScore($total, $casualties, $trend)
    {
        // Logic: Weighting incident volume (40%), lethality (40%), and trend (20%)
        $score = ($total * 1.5) + ($casualties * 3) + ($trend > 0 ? 10 : 0);
        return min(100, round($score));
    }
}
