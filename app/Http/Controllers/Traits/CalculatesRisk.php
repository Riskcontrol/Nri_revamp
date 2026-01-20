<?php

namespace App\Http\Controllers\Traits;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Models\CorrectionFactorForStates;

trait CalculatesRisk
{
    // These severity weights remain static as they are part of the core formula
    protected $WEIGHT_INCIDENT_SEVERITY = 25;
    protected $WEIGHT_VICTIM_SEVERITY   = 30;
    protected $WEIGHT_DEATH_SEVERITY    = 45;

    private function getRiskWeightMap()
    {
        // Cache this query for 24 hours (86400 seconds) so we don't hit the DB constantly
        return Cache::remember('risk_weight_map', 86400, function () {

            // Join the tables based on the 'factors' column name matching the risk factor 'name'
            $rows = DB::table('tblriskindicators')
                ->join('tblriskfactors', 'tblriskindicators.factors', '=', 'tblriskfactors.name')
                ->select(
                    'tblriskindicators.indicators as indicator_name', // e.g., Terrorism
                    'tblriskfactors.weight'                           // e.g., 0.40
                )
                ->get();

            $map = [];
            foreach ($rows as $row) {
                // Trim to ensure " Terrorism " becomes "Terrorism"
                $key = trim($row->indicator_name);
                $map[$key] = (float) $row->weight;
            }
            return $map;
        });
    }

    /**
     * 2. CALCULATE RISK
     */
    public function calculateWeightedStateRisk(Collection $data)
    {
        $reports = [];
        $correctionFactors = CorrectionFactorForStates::all()->keyBy('state');

        // Fetch the weights from the DB
        $weightMap = $this->getRiskWeightMap();

        // Calculate National Totals (Denominator)
        $nationalTotals = $data->groupBy('riskindicators')->map(function ($rows) {
            return [
                'incidents' => $rows->sum('total_incidents'),
                'victims'   => $rows->sum('total_victims'),
                'deaths'    => $rows->sum('total_deaths'),
            ];
        });

        $dataByState = $data->groupBy('location');
        $grandTotalScore = 0;

        foreach ($dataByState as $state => $riskRows) {
            $correction = $correctionFactors->get($state);
            // Default to 1 if no correction found
            $incCor = $correction ? $correction->incident_correction : 1;
            $vicCor = $correction ? $correction->victim_correction : 1;
            $dthCor = $correction ? $correction->casualty_correction : 1;

            $stateCompositeScore = 0;
            $stateTotalIncidents = 0;

            foreach ($riskRows as $row) {
                $riskName = trim($row->riskindicators);

                // LOOKUP WEIGHT FROM DB MAP
                // If the risk isn't in 'tblriskindicators', it gets 0 weight (ignored)
                $weight = $weightMap[$riskName] ?? 0;

                if ($weight <= 0) continue;

                $totals = $nationalTotals->get($row->riskindicators);

                // Severity Calculation
                $incScore = ($totals['incidents'] > 0) ? ($row->total_incidents / $totals['incidents']) * $this->WEIGHT_INCIDENT_SEVERITY * $incCor : 0;
                $vicScore = ($totals['victims'] > 0)   ? ($row->total_victims / $totals['victims'])     * $this->WEIGHT_VICTIM_SEVERITY   * $vicCor   : 0;
                $dthScore = ($totals['deaths'] > 0)    ? ($row->total_deaths / $totals['deaths'])       * $this->WEIGHT_DEATH_SEVERITY    * $dthCor    : 0;

                $rawRiskScore = $incScore + $vicScore + $dthScore;

                // Apply the Weight from the DB (e.g., * 0.40)
                $weightedRiskScore = $rawRiskScore * $weight;

                $stateCompositeScore += $weightedRiskScore;
                $stateTotalIncidents += $row->total_incidents;
            }

            $reports[$state] = [
                'location' => $state,
                'incident_count' => $stateTotalIncidents,
                'raw_composite_score' => $stateCompositeScore,
                'year' => $riskRows->first()->yy ?? now()->year,
            ];

            $grandTotalScore += $stateCompositeScore;
        }

        // Final Normalization
        foreach ($reports as &$report) {
            $score = $report['raw_composite_score'];
            $normalized = ($grandTotalScore > 0) ? ($score / $grandTotalScore) * 100 : 0;

            $report['normalized_ratio'] = round($normalized, 2);
            $report['risk_level'] = $this->determineBusinessRiskLevel($normalized);

            unset($report['raw_composite_score']);
        }

        return $reports;
    }

    public function determineBusinessRiskLevel($totalRatio)
    {
        if ($totalRatio <= 1.5) return 1;      // Low
        if ($totalRatio <= 3.5) return 2;      // Moderate
        if ($totalRatio <= 7.0) return 3;      // High
        return 4;                              // Critical
    }
}
