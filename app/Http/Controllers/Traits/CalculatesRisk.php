<?php

namespace App\Http\Controllers\Traits;

use App\Models\CorrectionFactorForStates; // <-- Import the model

trait CalculatesRisk
{
    /**
     * Note: This function requires the controller using it to also
     * 'use App\Models\CorrectionFactorForStates;'
     */
    public function calculateStateRisk($data)
    {
        $reports = [];

        // Fetch the correction factors
        $correctionFactors = CorrectionFactorForStates::all()->keyBy('state');

        // Calculate the overall totals for all states (these are the national totals)
        $AllIncidentCount = $data->sum('total_incidents');
        $AllvictimCount = $data->sum('total_victims');
        $AlldeathThreatsCount = $data->sum('total_deaths');

        // Initialize total ratios sum for normalization
        $totalRatiosSum = 0;

        // First pass: calculate the ratios
        foreach ($data as $item) {
            $state = $item->location;
            $correction = $correctionFactors->get($state);

            $incidentCorrection = $correction ? $correction->incident_correction : 1;
            $victimCorrection = $correction ? $correction->victim_correction : 1;
            $casualtyCorrection = $correction ? $correction->casualty_correction : 1;

            $incidentCount = $item->total_incidents;
            $victimCount = $item->total_victims;
            $deathThreatsCount = $item->total_deaths;

            $incidentRatio = $AllIncidentCount != 0 ? ($incidentCount / $AllIncidentCount) * 25 * $incidentCorrection : 0;
            $victimRatio = $AllvictimCount != 0 ? ($victimCount / $AllvictimCount) * 35 * $victimCorrection : 0;
            $deathThreatsRatio = $AlldeathThreatsCount != 0 ? ($deathThreatsCount / $AlldeathThreatsCount) * 40 * $casualtyCorrection : 0;

            $totalRatio = $incidentRatio + $victimRatio + $deathThreatsRatio;
            $totalRatiosSum += $totalRatio;

            $reports[$item->location] = [
                'location' => $item->location,
                'incident_count' => $incidentCount,
                'sum_victims' => $victimCount,
                'sum_casualties' => $deathThreatsCount,
                'total_ratio' => $totalRatio,
                'year' => $item->yy ?? now()->year,
            ];
        }

        // Second pass: normalize the ratios
        foreach ($reports as &$report) {
            $totalRatio = $report['total_ratio'];
            $normalizedRatio = $totalRatiosSum != 0 ? ($totalRatio / $totalRatiosSum) * 100 : 0;

            // Round for cleaner data
            $report['normalized_ratio'] = round($normalizedRatio, 2);

            // Assign risk level
            $report['risk_level'] = $this->determineBusinessRiskLevel($normalizedRatio);
        }

        return $reports; // Return the associative array
    }

    public function determineBusinessRiskLevel($totalRatio) {
        if ($totalRatio >=0 AND $totalRatio <= 1.7) {
            return 1; // Low
        } elseif ($totalRatio  > 1.7 AND $totalRatio <= 2.8) {
            return 2; // Moderate
        } elseif ($totalRatio > 2.8 AND $totalRatio <= 7) {
            return 3; // Elevated
        } else {
            return 4; // High
        }
    }
}
