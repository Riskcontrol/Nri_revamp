<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Builder;

class AnalyticsChartService
{
    /**
     * Generates multi-line series data for ApexCharts.
     * * @param Builder $baseQuery The query with global filters (Year, Risk, etc.) already applied.
     * @param array $groups An associative array where Key = Label (Line Name) and Value = Array of States.
     * @param int $startYear
     * @param int $endYear
     * @return array
     */
    public function getMultiSeriesData(Builder $baseQuery, array $groups, int $startYear, int $endYear): array
    {
        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

        // 1. Generate the "Backbone" (Time Axis)
        // Ensures chronological order: Jan 2023 -> Dec 2025
        $backbone = [];
        $categories = [];

        for ($y = $startYear; $y <= $endYear; $y++) {
            foreach ($months as $m) {
                $key = "{$m}-{$y}"; // e.g., "Jan-2023"
                $backbone[$key] = 0;
                $categories[] = $key;
            }
        }

        $series = [];

        // 2. Loop through each Comparison Group (Line on the chart)
        foreach ($groups as $labelName => $states) {

            // Clone the query so this loop doesn't affect the next one
            $groupQuery = clone $baseQuery;

            // Fetch data for this group
            // CRITICAL: We use whereIn.
            // If $states is ['Lagos'], it finds just Lagos.
            // If $states is ['Kano', 'Kaduna'...], it sums them all up into one timeline.
            $data = $groupQuery
                ->whereIn('tbldataentry.location', $states)
                ->selectRaw("CONCAT(tbldataentry.month_pro, '-', tbldataentry.yy) as date_key, COUNT(*) as count")
                ->groupBy('date_key')
                ->pluck('count', 'date_key')
                ->toArray();

            // 3. Merge with Backbone (Zero-Filling)
            $mergedData = array_merge($backbone, $data);

            $series[] = [
                'name' => $labelName, // This will be "Lagos" (State Mode) or "North West" (Region Mode)
                'data' => array_values($mergedData)
            ];
        }

        return [
            'categories' => $categories, // X-Axis Labels
            'series' => $series          // Y-Axis Data Lines
        ];
    }
}
