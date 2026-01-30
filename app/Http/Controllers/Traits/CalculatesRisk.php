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
    protected $WEIGHT_VICTIM_SEVERITY   = 35;
    protected $WEIGHT_DEATH_SEVERITY    = 40;

    private function norm($s): string
    {
        $s = preg_replace('/\s+/', ' ', (string) $s);
        return trim($s);
    }


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

    public function calculateStateRiskFromIndicators(Collection $data): array
    {
        $reports = [];

        $indicatorFactorMap = $this->getIndicatorFactorWeightMap();

        // ✅ ALWAYS load corrections (part of algorithm)
        $correctionFactors = CorrectionFactorForStates::all()
            ->keyBy(fn($r) => $this->norm($r->state));

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
            // ✅ Always apply correction factors (fallback to 1)
            $cor = $correctionFactors->get($state);

            $incCor = (float) ($cor?->incident_correction ?? 1);
            $vicCor = (float) ($cor?->victim_correction ?? 1);
            $dthCor = (float) ($cor?->death_correction ?? 1);

            $stateScore = 0.0;
            $stateIncidents = 0.0;

            foreach ($rows as $row) {
                $indicatorKey = $this->norm($row->risk_indicator);

                $totals = $nationalTotals->get($indicatorKey);
                if (!$totals) continue;

                $factorWeight = (float) ($indicatorFactorMap[$indicatorKey]['factor_weight'] ?? 0);
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


    public function calculateCompositeIndexByRiskFactors(int $year): array
    {
        // 1) Load risk factors with weights > 0
        $riskFactors = Cache::remember('tblriskfactors_weighted', 3600, function () {
            return DB::table('tblriskfactors')
                ->where('weight', '>', 0)
                ->get(['name', 'weight']);
        });

        if ($riskFactors->isEmpty()) return [];

        // 2) Load states for the year (normalized)
        $states = DB::table('tbldataentry')
            ->where('yy', $year)
            ->whereNotNull('location')
            ->where('location', '!=', '')
            ->selectRaw('DISTINCT TRIM(location) as location')
            ->orderBy('location')
            ->pluck('location')
            ->map(fn($s) => $this->norm($s))
            ->values()
            ->all();

        if (empty($states)) return [];

        // 3) Load correction factors (normalized key)
        $correctionFactors = Cache::remember('correction_factors_by_state', 3600, function () {
            return CorrectionFactorForStates::all()
                ->keyBy(fn($r) => $this->norm($r->state));
        });

        // Component weights (keep consistent with your old method)
        $incidentBaseWeight = 25;
        $victimBaseWeight   = 35;
        $deathBaseWeight    = 40;

        // rawValues[riskName][state] = raw value
        $rawValues = [];

        foreach ($riskFactors as $rf) {
            $riskName = $this->norm($rf->name);
            $rawValues[$riskName] = array_fill_keys($states, 0.0);

            // 4) State aggregates for this risk factor
            $stateData = DB::table('tbldataentry')
                ->where('yy', $year)
                ->where('riskfactors', $rf->name)
                ->selectRaw('TRIM(location) as location, COUNT(*) as incidentCount, COALESCE(SUM(victim),0) as victimCount, COALESCE(SUM(Casualties_count),0) as deathCount')
                ->groupBy(DB::raw('TRIM(location)'))
                ->get();

            // Normalize keys
            $stateData = $stateData->keyBy(fn($r) => $this->norm($r->location));

            // 5) National totals for this risk factor
            $overall = DB::table('tbldataentry')
                ->where('yy', $year)
                ->where('riskfactors', $rf->name)
                ->selectRaw('COUNT(*) as AllIncidentCount, COALESCE(SUM(victim),0) as AllVictimCount, COALESCE(SUM(Casualties_count),0) as AllDeathCount')
                ->first();

            $AllIncidentCount = (float) ($overall->AllIncidentCount ?? 0);
            $AllVictimCount   = (float) ($overall->AllVictimCount ?? 0);
            $AllDeathCount    = (float) ($overall->AllDeathCount ?? 0);

            foreach ($states as $state) {
                $data = $stateData->get($state);
                if (!$data) continue;

                $incidentCount = (float) ($data->incidentCount ?? 0);
                $victimCount   = (float) ($data->victimCount ?? 0);
                $deathCount    = (float) ($data->deathCount ?? 0);

                $cor = $correctionFactors->get($state);
                $incidentCorrection = (float) ($cor?->incident_correction ?? 1);
                $victimCorrection   = (float) ($cor?->victim_correction ?? 1);
                $deathCorrection    = (float) ($cor?->death_correction ?? 1);

                $incidentRatio = $AllIncidentCount > 0
                    ? ($incidentCount / $AllIncidentCount) * $incidentBaseWeight * $incidentCorrection
                    : 0;

                $victimRatio = $AllVictimCount > 0
                    ? ($victimCount / $AllVictimCount) * $victimBaseWeight * $victimCorrection
                    : 0;

                $deathRatio = $AllDeathCount > 0
                    ? ($deathCount / $AllDeathCount) * $deathBaseWeight * $deathCorrection
                    : 0;

                $rawValues[$riskName][$state] = $incidentRatio + $victimRatio + $deathRatio;
            }

            // 6) Normalize per risk factor to sum=100
            $totalForRisk = array_sum($rawValues[$riskName]);
            if ($totalForRisk > 0) {
                foreach ($states as $state) {
                    $rawValues[$riskName][$state] = ($rawValues[$riskName][$state] / $totalForRisk) * 100;
                }
            }
        }

        // 7) Composite = sum(normalized risk-factor share * risk factor weight)
        $compositeIndexes = array_fill_keys($states, 0.0);

        foreach ($states as $state) {
            $composite = 0.0;

            foreach ($riskFactors as $rf) {
                $riskName = $this->norm($rf->name);

                $weight = (float) $rf->weight;
                // If your weights are stored as 0–100, uncomment:
                // if ($weight > 1) $weight = $weight / 100;

                $composite += ($rawValues[$riskName][$state] ?? 0) * $weight;
            }

            $compositeIndexes[$state] = $composite;
        }

        return $compositeIndexes;
    }



    public function determineBusinessRiskLevel($totalRatio)
    {
        if ($totalRatio <= 1.5) return 1;
        if ($totalRatio <= 3.5) return 2;
        if ($totalRatio <= 7.0) return 3;
        return 4;
    }
}
