<?php

namespace App\Http\Controllers\Traits;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Models\CorrectionFactorForStates;

trait CalculatesRisk
{
    // Severity weights
    protected $WEIGHT_INCIDENT_SEVERITY = 15;
    protected $WEIGHT_VICTIM_SEVERITY   = 25;
    protected $WEIGHT_DEATH_SEVERITY    = 60;

    /**
     * Request-scoped memoization so CorrectionFactorForStates::all() is
     * called at most ONCE per request, regardless of how many trait methods
     * invoke it.  The 24-hour cache means it also survives across requests.
     */
    private static ?Collection $_correctionFactors = null;

    private function norm($s): string
    {
        $s = preg_replace('/\s+/', ' ', (string) $s);
        return trim($s);
    }

    // -------------------------------------------------------------------------
    // Shared look-ups (all cached)
    // -------------------------------------------------------------------------

    /**
     * Returns correction factors keyed by normalised state name.
     * Memoized for the lifetime of the current request AND cached for 24 h.
     */
    private function getCorrectionFactors(): Collection
    {
        if (self::$_correctionFactors === null) {
            self::$_correctionFactors = Cache::remember('correction_factors_all', 86400, function () {
                return CorrectionFactorForStates::all()
                    ->keyBy(fn($r) => $this->norm($r->state));
            });
        }

        return self::$_correctionFactors;
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
                    'factor'        => $this->norm($r->factor),
                    'factor_weight' => (float) $r->factor_weight,
                ];
            }
            return $map;
        });
    }

    // -------------------------------------------------------------------------
    // Aggregate builder
    // -------------------------------------------------------------------------

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

    // -------------------------------------------------------------------------
    // Crime-index calculation (used by Home + Location pages)
    // -------------------------------------------------------------------------

    public function calculateCrimeRiskIndexFromIndicators(Collection $data): array
    {
        $reports  = [];
        $corrections = $this->getCorrectionFactors(); // ← memoized, not a DB hit

        $totalIncidents  = (float) $data->sum('raw_incident_count');
        $totalCasualties = (float) $data->sum('raw_casualties_sum');
        $totalVictims    = (float) $data->sum('raw_victims_sum');

        $grandTotalScore = 0.0;

        foreach ($data as $row) {
            $stateKey = $this->norm($row->location);
            $cor      = $corrections->get($stateKey);

            $incidentCorrection = (float) ($cor?->incident_correction ?? 1);
            $deathCorrection    = (float) ($cor?->death_correction    ?? 1);
            $victimCorrection   = (float) ($cor?->victim_correction   ?? 1);

            $incidentRatio = $totalIncidents  > 0 ? $row->raw_incident_count / $totalIncidents  : 0;
            $casualtyRatio = $totalCasualties > 0 ? $row->raw_casualties_sum / $totalCasualties : 0;
            $victimRatio   = $totalVictims    > 0 ? $row->raw_victims_sum    / $totalVictims    : 0;

            $score =
                ($incidentRatio * $this->WEIGHT_INCIDENT_SEVERITY * $incidentCorrection) +
                ($casualtyRatio * $this->WEIGHT_DEATH_SEVERITY    * $deathCorrection)    +
                ($victimRatio   * $this->WEIGHT_VICTIM_SEVERITY   * $victimCorrection);

            $reports[$stateKey] = [
                'location'       => $stateKey,
                'incident_count' => (int) $row->raw_incident_count,
                'raw_score'      => $score,
                'year'           => (int) $row->yy,
            ];

            $grandTotalScore += $score;
        }

        foreach ($reports as &$r) {
            $normalized = $grandTotalScore > 0 ? ($r['raw_score'] / $grandTotalScore) * 100 : 0;

            $r['normalized_ratio_raw'] = $normalized;
            $r['normalized_ratio']     = number_format($normalized, 2, '.', '');
            $r['risk_level']           = $this->determineBusinessRiskLevel($normalized);

            unset($r['raw_score']);
        }

        return array_values($reports);
    }

    // -------------------------------------------------------------------------
    // Indicator-engine state risk calculation
    // -------------------------------------------------------------------------

    public function calculateStateRiskFromIndicators(Collection $data): array
    {
        $reports          = [];
        $indicatorFactorMap = $this->getIndicatorFactorWeightMap();
        $correctionFactors  = $this->getCorrectionFactors(); // ← memoized

        $nationalTotals = $data->groupBy(fn($row) => $this->norm($row->risk_indicator))
            ->map(fn($rows) => [
                'incidents' => (float) $rows->sum('total_incidents'),
                'victims'   => (float) $rows->sum('total_victims'),
                'deaths'    => (float) $rows->sum('total_deaths'),
            ]);

        $dataByState     = $data->groupBy(fn($row) => $this->norm($row->location));
        $grandTotalScore = 0.0;

        foreach ($dataByState as $state => $rows) {
            $cor    = $correctionFactors->get($state);
            $incCor = (float) ($cor?->incident_correction ?? 1);
            $vicCor = (float) ($cor?->victim_correction   ?? 1);
            $dthCor = (float) ($cor?->death_correction    ?? 1);

            $stateScore     = 0.0;
            $stateIncidents = 0.0;

            foreach ($rows as $row) {
                $indicatorKey = $this->norm($row->risk_indicator);
                $totals       = $nationalTotals->get($indicatorKey);
                if (!$totals) continue;

                $factorWeight = (float) ($indicatorFactorMap[$indicatorKey]['factor_weight'] ?? 0);
                if ($factorWeight <= 0) continue;

                $rowInc = (float) ($row->total_incidents ?? 0);
                $rowVic = (float) ($row->total_victims   ?? 0);
                $rowDth = (float) ($row->total_deaths    ?? 0);

                $incScore = $totals['incidents'] > 0 ? ($rowInc / $totals['incidents']) * $this->WEIGHT_INCIDENT_SEVERITY * $incCor : 0;
                $vicScore = $totals['victims']   > 0 ? ($rowVic / $totals['victims'])   * $this->WEIGHT_VICTIM_SEVERITY   * $vicCor : 0;
                $dthScore = $totals['deaths']    > 0 ? ($rowDth / $totals['deaths'])    * $this->WEIGHT_DEATH_SEVERITY    * $dthCor : 0;

                $stateScore     += ($incScore + $vicScore + $dthScore) * $factorWeight;
                $stateIncidents += $rowInc;
            }

            $reports[$state] = [
                'location'       => $state,
                'incident_count' => (int) $stateIncidents,
                'raw_score'      => $stateScore,
                'year'           => $rows->first()->yy ?? now()->year,
            ];

            $grandTotalScore += $stateScore;
        }

        foreach ($reports as &$r) {
            $normalized          = $grandTotalScore > 0 ? ((float) $r['raw_score'] / $grandTotalScore) * 100 : 0;
            $r['normalized_ratio'] = round($normalized, 2);
            $r['risk_level']       = $this->determineBusinessRiskLevel($normalized);
            unset($r['raw_score']);
        }

        return $reports;
    }

    // -------------------------------------------------------------------------
    // multipleRiskIndicatorCalculation (Risk Map — Crime / Property-Risk types)
    // -------------------------------------------------------------------------

    public function multipleRiskIndicatorCalculation(Collection $data): array
    {
        $reports     = [];
        $corrections = $this->getCorrectionFactors(); // ← memoized

        $totalIncidents  = (float) $data->sum('raw_incident_count');
        $totalCasualties = (float) $data->sum('raw_casualties_sum');
        $totalVictims    = (float) $data->sum('raw_victims_sum');

        $grandTotalScore = 0.0;

        foreach ($data as $row) {
            $stateKey = $this->norm($row->location);
            $cor      = $corrections->get($stateKey);

            $incidentCorrection = (float) ($cor?->incident_correction ?? 1);
            $deathCorrection    = (float) ($cor?->death_correction    ?? 1);
            $victimCorrection   = (float) ($cor?->victim_correction   ?? 1);

            $incidentRatio = $totalIncidents  > 0 ? $row->raw_incident_count / $totalIncidents  : 0;
            $casualtyRatio = $totalCasualties > 0 ? $row->raw_casualties_sum / $totalCasualties : 0;
            $victimRatio   = $totalVictims    > 0 ? $row->raw_victims_sum    / $totalVictims    : 0;

            $score =
                ($incidentRatio * $this->WEIGHT_INCIDENT_SEVERITY * $incidentCorrection) +
                ($casualtyRatio * $this->WEIGHT_DEATH_SEVERITY    * $deathCorrection)    +
                ($victimRatio   * $this->WEIGHT_VICTIM_SEVERITY   * $victimCorrection);

            $reports[$stateKey] = [
                'location'       => $stateKey,
                'incident_count' => (int) $row->raw_incident_count,
                'raw_score'      => $score,
                'year'           => (int) $row->yy,
            ];

            $grandTotalScore += $score;
        }

        foreach ($reports as &$r) {
            $normalized = $grandTotalScore > 0 ? ($r['raw_score'] / $grandTotalScore) * 100 : 0;

            $r['normalized_ratio_raw'] = $normalized;
            $r['normalized_ratio']     = number_format($normalized, 2, '.', '');
            $r['risk_level']           = $this->determineBusinessRiskLevel($normalized);

            unset($r['raw_score']);
        }

        return $reports;
    }

    // -------------------------------------------------------------------------
    // Composite index  ← THE BIG FIX: N+1 eliminated
    // -------------------------------------------------------------------------

    /**
     * FIXED: was firing 2 queries per risk-factor in a foreach loop.
     * Now issues ONE consolidated query for all risk factors, then
     * does all aggregation in PHP. Result is cached per year for 1 hour.
     */
    public function calculateCompositeIndexByRiskFactors(int $year): array
    {
        return Cache::remember("composite_index:{$year}", 3600, function () use ($year) {

            // 1) Risk factors with weights > 0  (cached 1 h)
            $riskFactors = Cache::remember('tblriskfactors_weighted', 3600, function () {
                return DB::table('tblriskfactors')
                    ->where('weight', '>', 0)
                    ->get(['name', 'weight']);
            });

            if ($riskFactors->isEmpty()) return [];

            // 2) All distinct states for the year  (one query)
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

            // 3) Correction factors  (memoized + cached 24 h)
            $correctionFactors = $this->getCorrectionFactors();

            // 4) SINGLE query replacing the entire foreach N+1 loop
            //    Fetches all risk-factor × state aggregates at once.
            $allRows = DB::table('tbldataentry')
                ->where('yy', $year)
                ->whereIn('riskfactors', $riskFactors->pluck('name'))
                ->whereNotNull('location')
                ->where('location', '!=', '')
                ->selectRaw('
                    TRIM(riskfactors) as riskfactor,
                    TRIM(location)    as location,
                    COUNT(*)                              as incidentCount,
                    COALESCE(SUM(victim), 0)              as victimCount,
                    COALESCE(SUM(Casualties_count), 0)    as deathCount
                ')
                ->groupBy(DB::raw('TRIM(riskfactors)'), DB::raw('TRIM(location)'))
                ->get()
                ->groupBy('riskfactor');

            $incidentBaseWeight = 15;
            $victimBaseWeight   = 25;
            $deathBaseWeight    = 60;

            $rawValues = [];

            foreach ($riskFactors as $rf) {
                $riskName = $this->norm($rf->name);
                $rawValues[$riskName] = array_fill_keys($states, 0.0);

                $stateRows = ($allRows->get($riskName) ?? collect())
                    ->keyBy(fn($r) => $this->norm($r->location));

                // National totals derived from the same in-memory collection
                $AllIncidentCount = (float) $stateRows->sum('incidentCount');
                $AllVictimCount   = (float) $stateRows->sum('victimCount');
                $AllDeathCount    = (float) $stateRows->sum('deathCount');

                foreach ($states as $state) {
                    $data = $stateRows->get($state);
                    if (!$data) continue;

                    $cor = $correctionFactors->get($state);
                    $incidentCorrection = (float) ($cor?->incident_correction ?? 1);
                    $victimCorrection   = (float) ($cor?->victim_correction   ?? 1);
                    $deathCorrection    = (float) ($cor?->death_correction    ?? 1);

                    $incidentRatio = $AllIncidentCount > 0
                        ? ($data->incidentCount / $AllIncidentCount) * $incidentBaseWeight * $incidentCorrection
                        : 0;

                    $victimRatio = $AllVictimCount > 0
                        ? ($data->victimCount / $AllVictimCount) * $victimBaseWeight * $victimCorrection
                        : 0;

                    $deathRatio = $AllDeathCount > 0
                        ? ($data->deathCount / $AllDeathCount) * $deathBaseWeight * $deathCorrection
                        : 0;

                    $rawValues[$riskName][$state] = $incidentRatio + $victimRatio + $deathRatio;
                }

                // Normalise per risk factor so each sums to 100
                $totalForRisk = array_sum($rawValues[$riskName]);
                if ($totalForRisk > 0) {
                    foreach ($states as $state) {
                        $rawValues[$riskName][$state] = ($rawValues[$riskName][$state] / $totalForRisk) * 100;
                    }
                }
            }

            // 5) Composite score = Σ(normalised risk-factor share × weight)
            $compositeIndexes = array_fill_keys($states, 0.0);

            foreach ($states as $state) {
                $composite = 0.0;
                foreach ($riskFactors as $rf) {
                    $riskName  = $this->norm($rf->name);
                    $composite += ($rawValues[$riskName][$state] ?? 0) * (float) $rf->weight;
                }
                $compositeIndexes[$state] = $composite;
            }

            return $compositeIndexes;
        });
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    public function determineBusinessRiskLevel($totalRatio)
    {
        if ($totalRatio <= 1.5) return 1;
        if ($totalRatio <= 3.5) return 2;
        if ($totalRatio <= 7.0) return 3;
        return 4;
    }
}
