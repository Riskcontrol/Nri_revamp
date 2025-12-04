<?php

namespace App\Http\Controllers\Traits;

use App\Models\tbldataentry;
use Illuminate\Support\Facades\DB;

trait GeneratesInsights
{
    /**
     * Calculates 'Velocity' (Speed of change) and 'Emerging Threats' (High growth risks).
     */
    public function calculateTrendInsights($state, $currentYear)
    {
        $insights = [];

        // --- 1. Velocity Calculation ---
        // We compare total count of Current Year vs Previous Year
        $currentCount = tbldataentry::whereRaw('LOWER(location) = ?', [strtolower($state)])
            ->where('yy', $currentYear)->count();

        $previousCount = tbldataentry::whereRaw('LOWER(location) = ?', [strtolower($state)])
            ->where('yy', $currentYear - 1)->count();

        if ($previousCount > 0) {
            $percentChange = (($currentCount - $previousCount) / $previousCount) * 100;
            $direction = $percentChange > 0 ? 'Escalating' : 'Improving';

            // Only show insight if the change is significant (> 5%)
            if (abs($percentChange) > 5) {
                $insights['velocity'] = [
                    'type' => 'Velocity',
                    'text' => "Incidents in {$state} are {$direction} at a " . abs(round($percentChange, 1)) . "% year-over-year rate.",
                ];
            }
        }

        // --- 2. Emerging Threat Calculation ---
        // We look for a specific risk that has grown massively (>50%) compared to last year
        $emerging = tbldataentry::select('riskindicators')
            ->selectRaw("
                SUM(CASE WHEN yy = ? THEN 1 ELSE 0 END) as current_vol,
                SUM(CASE WHEN yy = ? THEN 1 ELSE 0 END) as prev_vol
            ", [$currentYear, $currentYear - 1])
            ->whereRaw('LOWER(location) = ?', [strtolower($state)])
            ->whereIn('yy', [$currentYear, $currentYear - 1])
            ->groupBy('riskindicators')
            ->havingRaw('prev_vol > 5') // Noise filter: Must have had at least 5 incidents last year
            ->get()
            ->map(function($item) {
                $growth = ($item->prev_vol > 0) ? (($item->current_vol - $item->prev_vol) / $item->prev_vol) * 100 : 0;
                return ['risk' => $item->riskindicators, 'growth' => $growth];
            })
            ->sortByDesc('growth')
            ->first();

        if ($emerging && $emerging['growth'] > 50) {
             $insights['emerging'] = [
                'type' => 'Emerging Threat',
                'text' => "'{$emerging['risk']}' has shown " . round($emerging['growth']) . "% growth, identifying it as a major emerging threat.",
            ];
        }

        return $insights;
    }

    /**
     * Calculates 'Lethality'.
     * Finds the risk type with the highest Deaths per Incident ratio.
     */
    public function calculateLethalityInsights($state, $year)
    {
        $mostLethal = tbldataentry::select('riskindicators')
            ->selectRaw('COUNT(*) as total_incidents')
            ->selectRaw('SUM(Casualties_count) as total_deaths')
            ->whereRaw('LOWER(location) = ?', [strtolower($state)])
            ->where('yy', $year)
            ->groupBy('riskindicators')
            ->having('total_incidents', '>', 5) // Minimum sample size to be statistically relevant
            ->get()
            ->map(function($item) {
                // Calculate Ratio
                $item->lethality_rate = $item->total_incidents > 0 ? ($item->total_deaths / $item->total_incidents) : 0;
                return $item;
            })
            ->sortByDesc('lethality_rate')
            ->first();

        // Only report if the lethality rate is actually dangerous (> 0.5 deaths per incident)
        if ($mostLethal && $mostLethal->lethality_rate > 0.5) {
            return [
                'type' => 'Lethality',
                'text' => "'{$mostLethal->riskindicators}' is the most lethal risk here, averaging " . round($mostLethal->lethality_rate, 1) . " casualties per incident."
            ];
        }

        return null;
    }

    /**
     * Calculates 'Forecast'.
     * Uses a simple 3-month moving average to predict next month.
     */
   public function calculateForecast($state)
    {
        $recentMonths = tbldataentry::selectRaw('yy, month_pro, COUNT(*) as count')
            ->whereRaw('LOWER(location) = ?', [strtolower($state)])

            // --- FIX 1: Add Group By ---
            ->groupBy('yy', 'month_pro')

            // --- FIX 2: Order by the latest incident in that group ---
            // We cannot order by just 'datecreated' because it is not grouped.
            // We use MAX(datecreated) or MAX(id) to find the most recent month.
            ->orderByRaw('MAX(id) desc')

            ->limit(3)
            ->get();

        if ($recentMonths->count() == 3) {
            $avg = $recentMonths->avg('count');
            return [
                'type' => 'Forecast',
                'text' => "Based on a 3-month moving average, we project approximately " . round($avg) . " incidents for the upcoming month."
            ];
        }
        return null;
    }
}
