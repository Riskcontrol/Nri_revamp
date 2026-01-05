<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RiskToolController extends Controller
{
    // app/Http/Controllers/RiskToolController.php
    public function analyze(Request $request)
    {
        $state = $request->input('state');
        $lga = $request->input('lga');
        $currentYear = 2024;
        $lastYear = 2023;

        // Aggregates for the LGA
        $lgaStats = DB::table('incidents')
            ->where('lga', $lga)
            ->selectRaw('count(*) as total, sum(Casualties_count) as casualties')
            ->first();

        // Distribution for Chart
        $distribution = DB::table('incidents')
            ->where('lga', $lga)
            ->select('riskindicators as label', DB::raw('count(*) as value'))
            ->groupBy('riskindicators')
            ->get();

        // Trend calculation
        $thisYearCount = DB::table('incidents')->where('lga', $lga)->where('eventyear', $currentYear)->count();
        $lastYearCount = DB::table('incidents')->where('lga', $lga)->where('eventyear', $lastYear)->count();
        $trend = ($lastYearCount > 0) ? (($thisYearCount - $lastYearCount) / $lastYearCount) * 100 : 0;

        // Advisory (The gated content)
        $advisory = DB::table('incidents')
            ->where('lga', $lga)
            ->whereNotNull('business_advisory')
            ->value('business_advisory') ?? "Standard security protocols advised for this region.";

        return response()->json([
            'total' => $lgaStats->total,
            'casualties' => $lgaStats->casualties ?? 0,
            'trend' => round($trend, 1),
            'score' => min(95, ($lgaStats->total * 2)), // Simplified scoring
            'distribution' => $distribution,
            'advisory' => $advisory
        ]);
    }
}
