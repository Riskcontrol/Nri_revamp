<?php

namespace App\Http\Controllers\Traits;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Models\CorrectionFactorForStates;

trait CalculatesRisk
{
    // Severity weights (your choice; keep consistent everywhere)
    protected $WEIGHT_INCIDENT_SEVERITY = 25;
    protected $WEIGHT_VICTIM_SEVERITY   = 30;
    protected $WEIGHT_DEATH_SEVERITY    = 45;

    private function norm($s): string
    {
        $s = preg_replace('/\s+/', ' ', (string) $s);
        return trim($s);
    }

    /**
     * Map: indicator => [factor, factor_weight]
     * - tblriskindicators.indicators -> tblriskindicators.factors
     * - tblriskindicators.factors -> tblriskfactors.name (weight)
     */
    private function getIndicatorFactorWeightMap(): array
    {
        return Cache::remember('indicator_factor_weight_map', 86400, function () {
            $rows = DB::table('tblriskindicators as ri')
                ->join('tblriskfactors as rf', 'ri.factors', '=', 'rf.name')
                ->select(
                    DB::raw('TRIM(ri.indicators) as indicator'),
                    DB::raw('TRIM(ri.factors) as factor'),
                    DB::raw('MAX(rf.weight) as factor_weight')
                )
                ->groupBy(DB::raw('TRIM(ri.indicators)'), DB::raw('TRIM(ri.factors)'))
                ->get();

            $map = [];
            foreach ($rows as $r) {
                $indicator = $this->norm($r->indicator);
                $map[$indicator] = [
                    'factor' => $this->norm($r->factor),
                    'factor_weight' => (float) $r->factor_weight,
                ];
            }
            return $map;
        });
    }

    /**
     * Build aggregates from tbldataentry grouped by indicator.
     * NOTE: deaths column is Casualties_count (as used across your controller).
     */
    public function buildIndicatorAggregates(int $year, ?string $indicator = null): Collection
    {
        $q = DB::table('tbldataentry')
            ->where('yy', $year)
            ->whereNotNull('location')
            ->where('location', '!=', '');

        if ($indicator && $indicator !== 'All') {
            $q->where('riskindicators', $indicator);
        }

        return $q->selectRaw('
                TRIM(location) as location,
                yy,
                TRIM(riskindicators) as risk_indicator,
                COUNT(*) as total_incidents,
                COALESCE(SUM(victim),0) as total_victims,
                COALESCE(SUM(Casualties_count),0) as total_deaths
            ')
            ->groupBy(DB::raw('TRIM(location)'), 'yy', DB::raw('TRIM(riskindicators)'))
            ->get();
    }

    /**
     * Canonical scoring engine.
     *
     * - If $applyCorrections = false -> use 1,1,1 (UNADJUSTED)
     * - If $applyCorrections = true  -> apply CorrectionFactorForStates multipliers
     *
     * Composite is computed across indicators, weighted by their factor weight.
     */
    public function calculateStateRiskFromIndicators(Collection $data, bool $applyCorrections = true): array
    {
        $reports = [];

        $indicatorFactorMap = $this->getIndicatorFactorWeightMap();

        // Only load corrections if needed (saves memory/time too)
        $correctionFactors = $applyCorrections
            ? CorrectionFactorForStates::all()->keyBy(fn($r) => $this->norm($r->state))
            : collect();

        // Denominators: national totals per indicator
        $nationalTotals = $data->groupBy(fn($row) => $this->norm($row->risk_indicator))
            ->map(function ($rows) {
                return [
                    'incidents' => (float) $rows->sum('total_incidents'),
                    'victims'   => (float) $rows->sum('total_victims'),
                    'deaths'    => (float) $rows->sum('total_deaths'),
                ];
            });

        $dataByState = $data->groupBy(fn($row) => $this->norm($row->location));
        $grandTotalScore = 0.0;

        foreach ($dataByState as $state => $rows) {
            // ✅ Apply corrections only if $applyCorrections is true
            if ($applyCorrections) {
                $cor = $correctionFactors->get($state);

                $incCor = (float) ($cor?->incident_correction ?? 1);
                $vicCor = (float) ($cor?->victim_correction ?? 1);
                $dthCor = (float) ($cor?->death_correction ?? 1);
            } else {
                $incCor = 1.0;
                $vicCor = 1.0;
                $dthCor = 1.0;
            }

            $stateScore = 0.0;
            $stateIncidents = 0.0;

            foreach ($rows as $row) {
                $indicatorKey = $this->norm($row->risk_indicator);

                $totals = $nationalTotals->get($indicatorKey);
                if (!$totals) continue;

                // Factor weight derived through indicator -> factor
                $factorWeight = (float) ($indicatorFactorMap[$indicatorKey]['factor_weight'] ?? 0);

                // If an indicator isn't mapped, ignore it (safer)
                if ($factorWeight <= 0) continue;

                $rowInc = (float) ($row->total_incidents ?? 0);
                $rowVic = (float) ($row->total_victims ?? 0);
                $rowDth = (float) ($row->total_deaths ?? 0);

                $incScore = ($totals['incidents'] > 0)
                    ? ($rowInc / $totals['incidents']) * $this->WEIGHT_INCIDENT_SEVERITY * $incCor
                    : 0;

                $vicScore = ($totals['victims'] > 0)
                    ? ($rowVic / $totals['victims']) * $this->WEIGHT_VICTIM_SEVERITY * $vicCor
                    : 0;

                $dthScore = ($totals['deaths'] > 0)
                    ? ($rowDth / $totals['deaths']) * $this->WEIGHT_DEATH_SEVERITY * $dthCor
                    : 0;

                $stateScore += ($incScore + $vicScore + $dthScore) * $factorWeight;
                $stateIncidents += $rowInc;
            }

            $reports[$state] = [
                'location' => $state,
                'incident_count' => (int) $stateIncidents,
                'raw_score' => $stateScore,
                'year' => $rows->first()->yy ?? now()->year,
            ];

            $grandTotalScore += $stateScore;
        }

        // Normalize to 0–100 share
        foreach ($reports as &$r) {
            $normalized = ($grandTotalScore > 0)
                ? ((float) $r['raw_score'] / $grandTotalScore) * 100
                : 0;

            $r['normalized_ratio'] = round($normalized, 2);
            $r['risk_level'] = $this->determineBusinessRiskLevel($normalized);

            unset($r['raw_score']);
        }

        return $reports;
    }

    public function determineBusinessRiskLevel($totalRatio)
    {
        if ($totalRatio <= 1.5) return 1;
        if ($totalRatio <= 3.5) return 2;
        if ($totalRatio <= 7.0) return 3;
        return 4;
    }
}
