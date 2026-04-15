<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Traits\CalculatesRisk;
use App\Http\Controllers\Traits\GeneratesInsights;
use Carbon\Carbon;

/**
 * AdvisoryDataAggregator  (final)
 *
 * ── THE RISK LEVEL BUG ───────────────────────────────────────────────────────
 *
 * The previous computeRiskScoreAndLevel() had two compounding problems:
 *
 * Problem A — Wrong method.
 *   It called buildIndicatorAggregates() and looked for ->weighted_score on each
 *   row. That method returns raw per-indicator rows with columns like location,
 *   yy, risk_indicator, total_incidents. There is no weighted_score column.
 *   The filter() always returned null, so it always fell through to the fallback.
 *
 * Problem B — Wrong thresholds in the fallback.
 *   The fallback used:  $score = ($incidents / 200) * 100  → thresholds 75/50/25
 *   Most states have 100–300 incidents → score 50–100 → nearly everything Level 3/4.
 *
 * The fix uses calculateCompositeIndexByRiskFactors() — the exact method that
 * powers your Risk Map. It returns:  ['state name' => composite_float]
 * where composite floats are typically 0–50 range. These feed directly into
 * determineBusinessRiskLevel() which uses thresholds:
 *   <= 1.5  → Level 1
 *   <= 3.5  → Level 2
 *   <= 7.0  → Level 3
 *   > 7.0   → Level 4
 *
 * This guarantees the advisory banner shows the same level as the Risk Map.
 *
 * For the 0–100 display score shown in the UI score chip, we normalise the
 * state's composite score against the national maximum for that year.
 *
 * ── LGA RISK TABLE ───────────────────────────────────────────────────────────
 *
 * buildLgaRiskTable() queries actual LGA incident data within the rolling window.
 * Falls back to area-type rows if no LGA data exists.
 */
class AdvisoryDataAggregator
{
    use CalculatesRisk, GeneratesInsights;

    private const CACHE_TTL = 3600;

    // ─── Public API ────────────────────────────────────────────────────────────

    public function build(string $state): array
    {
        $state    = $this->normaliseState($state);
        $window   = $this->rollingWindow();
        $cacheKey = "advisory_payload:{$state}:{$window['cache_slug']}";

        return Cache::remember(
            $cacheKey,
            self::CACHE_TTL,
            fn() => $this->assemblePayload($state, $window)
        );
    }

    public function invalidate(string $state): void
    {
        $state = $this->normaliseState($state);
        Cache::forget("advisory_payload:{$state}:" . now()->toDateString());
        Cache::forget("advisory_payload:{$state}:" . now()->subDay()->toDateString());
    }

    // ─── Window ────────────────────────────────────────────────────────────────

    private function rollingWindow(?Carbon $anchor = null): array
    {
        $anchor = $anchor ?? now();

        $currentEnd   = $anchor->copy()->endOfDay();
        $currentStart = $anchor->copy()->subDays(364)->startOfDay();
        $prevEnd      = $currentStart->copy()->subDay()->endOfDay();
        $prevStart    = $prevEnd->copy()->subDays(364)->startOfDay();

        return [
            'current_start_date' => $currentStart->toDateString(),
            'current_end_date'   => $currentEnd->toDateString(),
            'prev_start_date'    => $prevStart->toDateString(),
            'prev_end_date'      => $prevEnd->toDateString(),
            'current_year'       => (int) $currentEnd->format('Y'),
            'prev_year'          => (int) $prevEnd->format('Y'),
            'label'              => $currentStart->format('M j, Y') . ' – ' . $currentEnd->format('M j, Y'),
            'cache_slug'         => $anchor->toDateString(),
        ];
    }

    // ─── Assembly ──────────────────────────────────────────────────────────────

    private function assemblePayload(string $state, array $w): array
    {
        $currentCount  = $this->countInWindow($state, $w['current_start_date'], $w['current_end_date']);
        $previousCount = $this->countInWindow($state, $w['prev_start_date'], $w['prev_end_date']);

        $yoyChangePct = ($previousCount > 0)
            ? round((($currentCount - $previousCount) / $previousCount) * 100, 1)
            : ($currentCount > 0 ? 100.0 : 0.0);

        $casualties    = $this->getCasualties($state, $w['current_start_date'], $w['current_end_date']);
        $topIndicators = $this->getTopRiskIndicators($state, $w);

        [$riskScore, $riskLevel] = $this->computeRiskScoreAndLevel($state, $w['current_year']);

        $trendSignals    = $this->buildTrendSignals($state, $currentCount, $previousCount, $topIndicators);
        $movementSignals = $this->getMovementSignals($state, $w['current_start_date'], $w['current_end_date']);
        $recentSpike     = $this->getRecentSpike($state);
        $lgaRiskTable    = $this->buildLgaRiskTable($state, $w, $riskLevel);

        return [
            'state'                 => $state,
            'window_label'          => $w['label'],
            'window_start'          => $w['current_start_date'],
            'window_end'            => $w['current_end_date'],
            'risk_level'            => $riskLevel,
            'risk_score'            => $riskScore,
            'total_incidents'       => $currentCount,
            'prev_window_incidents' => $previousCount,
            'yoy_change_pct'        => $yoyChangePct,
            'total_deaths'          => (int) ($casualties->total_deaths  ?? 0),
            'total_victims'         => (int) ($casualties->total_victims ?? 0),
            'top_risk_indicators'   => $topIndicators,
            'area_risk_table'       => $lgaRiskTable,
            'trend_signals'         => $trendSignals,
            'movement_signals'      => $movementSignals,
            'recent_spike'          => $recentSpike,
        ];
    }

    // ─── FIXED: Risk scoring ───────────────────────────────────────────────────

    /**
     * Uses calculateCompositeIndexByRiskFactors() — the same method as the
     * Risk Map — so advisory risk levels match what users see elsewhere in the app.
     *
     * Returns [$displayScore, $level] where:
     *   $displayScore = 0–100 normalised against national max (for the UI chip)
     *   $level        = 1–4 from determineBusinessRiskLevel() (for the banner)
     */
    private function computeRiskScoreAndLevel(string $state, int $year): array
    {
        try {
            // This returns ['normalised state name' => composite_float]
            // composite_float is typically 0–50 range
            $compositeIndex = $this->calculateCompositeIndexByRiskFactors($year);

            $key = $this->norm($state); // normalise to match the trait's keying

            if (!empty($compositeIndex) && array_key_exists($key, $compositeIndex)) {
                $rawScore = (float) $compositeIndex[$key];

                // Level uses the same thresholds as the Risk Map
                $level = $this->determineBusinessRiskLevel($rawScore);

                // Display score: normalise to 0–100 relative to national max
                $allScores    = array_filter(array_values($compositeIndex), fn($v) => $v > 0);
                $nationalMax  = !empty($allScores) ? max($allScores) : 1;
                $displayScore = round(($rawScore / $nationalMax) * 100, 1);

                Log::debug("AdvisoryDataAggregator: composite score for {$state}", [
                    'raw_score'     => $rawScore,
                    'display_score' => $displayScore,
                    'level'         => $level,
                    'national_max'  => $nationalMax,
                ]);

                return [$displayScore, $level];
            }

            Log::warning("AdvisoryDataAggregator: state '{$state}' not found in composite index (key: '{$key}')", [
                'available_keys' => array_keys($compositeIndex ?? []),
            ]);
        } catch (\Throwable $e) {
            Log::warning("AdvisoryDataAggregator: composite index failed for {$state}", [
                'error' => $e->getMessage(),
            ]);
        }

        // ── Fallback (only if composite index completely unavailable) ─────────
        // Uses the same threshold scale as determineBusinessRiskLevel()
        // so levels are consistent even in fallback.
        $incidents = $this->countInWindow(
            $state,
            now()->subDays(364)->toDateString(),
            now()->toDateString()
        );

        // Map incident count to the composite score scale:
        // ~5 incidents/year  ≈ score 1.5 (Level 1 boundary)
        // ~15 incidents/year ≈ score 3.5 (Level 2 boundary)
        // ~30 incidents/year ≈ score 7.0 (Level 3 boundary)
        // Anything above → Level 4
        $fallbackScore = match (true) {
            $incidents <= 5  => 0.5,
            $incidents <= 15 => 2.5,
            $incidents <= 30 => 5.0,
            $incidents <= 60 => 8.0,
            default          => 12.0,
        };

        $level        = $this->determineBusinessRiskLevel($fallbackScore);
        $displayScore = round(min(100, ($incidents / 80) * 100), 1);

        return [$displayScore, $level];
    }

    // ─── LGA risk table ────────────────────────────────────────────────────────

    /**
     * Query the top 5 most incident-active LGAs in the rolling window.
     * Each row includes: real incident count, trend, dominant threat type.
     * Falls back to area-type rows if no LGA data exists.
     */
    private function buildLgaRiskTable(string $state, array $w, int $stateRiskLevel): array
    {
        $rows = DB::table('tbldataentry as t')
            ->whereRaw('LOWER(TRIM(t.location)) = ?', [strtolower($state)])
            ->where(function ($q) use ($w) {
                $q->whereBetween('t.eventdateToUse', [$w['current_start_date'], $w['current_end_date']])
                    ->orWhereBetween('t.eventdateToUse', [$w['prev_start_date'], $w['prev_end_date']]);
            })
            ->where('t.lga', '!=', '')
            ->whereNotNull('t.lga')
            ->selectRaw("
                TRIM(t.lga) as lga,
                SUM(CASE WHEN t.eventdateToUse BETWEEN ? AND ? THEN 1 ELSE 0 END) as current_count,
                SUM(CASE WHEN t.eventdateToUse BETWEEN ? AND ? THEN 1 ELSE 0 END) as prev_count
            ", [
                $w['current_start_date'],
                $w['current_end_date'],
                $w['prev_start_date'],
                $w['prev_end_date'],
            ])
            ->groupBy(DB::raw('TRIM(t.lga)'))
            ->orderByDesc('current_count')
            ->limit(5)
            ->get();

        if ($rows->isEmpty()) {
            return $this->buildAreaTypeFallback($stateRiskLevel);
        }

        // Get dominant indicator per LGA in one query
        $topLgas = $rows->pluck('lga')->toArray();

        $dominantTypes = DB::table('tbldataentry')
            ->whereRaw('LOWER(TRIM(location)) = ?', [strtolower($state)])
            ->whereIn(DB::raw('TRIM(lga)'), $topLgas)
            ->whereBetween('eventdateToUse', [$w['current_start_date'], $w['current_end_date']])
            ->where('lga', '!=', '')
            ->selectRaw('TRIM(lga) as lga, TRIM(riskindicators) as indicator, COUNT(*) as cnt')
            ->groupBy(DB::raw('TRIM(lga)'), DB::raw('TRIM(riskindicators)'))
            ->orderByDesc('cnt')
            ->get()
            ->groupBy('lga')
            ->map(fn($g) => $g->first()->indicator ?? null);

        $maxCount = $rows->max('current_count') ?: 1;

        $labelMap = [1 => 'Low', 2 => 'Medium-High', 3 => 'High', 4 => 'Very High'];

        return $rows->map(function ($row) use ($maxCount, $stateRiskLevel, $dominantTypes, $labelMap) {
            $intensity = $row->current_count / $maxCount;

            $lgaLevel = match (true) {
                $intensity >= 0.70 => min(4, $stateRiskLevel + 1),
                $intensity >= 0.35 => $stateRiskLevel,
                default            => max(1, $stateRiskLevel - 1),
            };

            $yoyChange = ($row->prev_count > 0)
                ? round((($row->current_count - $row->prev_count) / $row->prev_count) * 100, 1)
                : ($row->current_count > 0 ? 100.0 : 0.0);

            $trend = $yoyChange > 10 ? 'rising' : ($yoyChange < -10 ? 'falling' : 'stable');

            $dominant = $dominantTypes[$row->lga] ?? null;
            $advisory = $this->lgaAdvisoryText($lgaLevel, $dominant, $trend);

            return [
                'area_type'      => $row->lga . ' LGA',
                'lga_name'       => $row->lga,
                'risk_level'     => $lgaLevel,
                'risk_label'     => $labelMap[$lgaLevel],
                'incident_count' => (int) $row->current_count,
                'yoy_change'     => $yoyChange,
                'trend'          => $trend,
                'trend_symbol'   => $trend === 'rising' ? '↑' : ($trend === 'falling' ? '↓' : '→'),
                'trend_label'    => $trend === 'rising' ? 'Increasing' : ($trend === 'falling' ? 'Decreasing' : 'Stable'),
                'dominant_type'  => $dominant,
                'advisory'       => $advisory,
            ];
        })->values()->toArray();
    }

    private function lgaAdvisoryText(int $level, ?string $dominant, string $trend): string
    {
        $base = match ($level) {
            1 => 'Standard precautions apply.',
            2 => 'Exercise increased caution; maintain situational awareness.',
            3 => 'Reconsider non-essential travel; use secure transport.',
            4 => 'Avoid non-essential travel; significant security risk.',
            default => 'Monitor conditions closely.',
        };

        if ($dominant) {
            $trendNote = match ($trend) {
                'rising'  => 'Incidents are increasing.',
                'falling' => 'Incidents are declining.',
                default   => 'Conditions are broadly stable.',
            };
            return "{$base} Primary threat: {$dominant}. {$trendNote}";
        }

        return $base;
    }

    private function buildAreaTypeFallback(int $riskLevel): array
    {
        $areaLevels = [
            'Urban Centers'           => max(1, $riskLevel - 1),
            'Semi-Urban Areas'        => $riskLevel,
            'Rural / Flashpoint LGAs' => min(4, $riskLevel + 1),
        ];
        $labelMap = [1 => 'Low', 2 => 'Medium-High', 3 => 'High', 4 => 'Very High'];
        $advisoryMap = [
            'Urban Centers'           => [1 => 'Normal vigilance applies.', 2 => 'Exercise caution; limit exposure in crowded areas.', 3 => 'Exercise high caution; use secure transport.', 4 => 'Avoid non-essential movement.'],
            'Semi-Urban Areas'        => [1 => 'Standard precautions apply.', 2 => 'Reconsider non-essential travel; maintain a low profile.', 3 => 'Reconsider non-essential travel; expect security checkpoints.', 4 => 'Avoid travel; significant armed violence threat.'],
            'Rural / Flashpoint LGAs' => [1 => 'Travel with local knowledge.', 2 => 'Elevated risk of encountering violence.', 3 => 'Avoid all travel; high probability of armed violence.', 4 => 'Do not enter; active conflict reported.'],
        ];

        return collect($areaLevels)->map(fn($lvl, $type) => [
            'area_type'      => $type,
            'lga_name'       => null,
            'risk_level'     => $lvl,
            'risk_label'     => $labelMap[$lvl],
            'incident_count' => null,
            'yoy_change'     => null,
            'trend'          => null,
            'trend_symbol'   => null,
            'trend_label'    => null,
            'dominant_type'  => null,
            'advisory'       => $advisoryMap[$type][$lvl],
        ])->values()->toArray();
    }

    // ─── Shared query helpers ──────────────────────────────────────────────────

    private function countInWindow(string $state, string $start, string $end): int
    {
        return (int) DB::table('tbldataentry')
            ->whereRaw('LOWER(TRIM(location)) = ?', [strtolower($state)])
            ->whereBetween('eventdateToUse', [$start, $end])
            ->count();
    }

    private function getCasualties(string $state, string $start, string $end): object
    {
        return DB::table('tbldataentry')
            ->whereRaw('LOWER(TRIM(location)) = ?', [strtolower($state)])
            ->whereBetween('eventdateToUse', [$start, $end])
            ->selectRaw('
                COALESCE(SUM(CAST(Casualties_count AS UNSIGNED)), 0) as total_deaths,
                COALESCE(SUM(CAST(victim AS UNSIGNED)), 0)           as total_victims
            ')
            ->first() ?? (object) ['total_deaths' => 0, 'total_victims' => 0];
    }

    private function getTopRiskIndicators(string $state, array $w): array
    {
        $rows = DB::table('tbldataentry')
            ->whereRaw('LOWER(TRIM(location)) = ?', [strtolower($state)])
            ->where(function ($q) use ($w) {
                $q->whereBetween('eventdateToUse', [$w['current_start_date'], $w['current_end_date']])
                    ->orWhereBetween('eventdateToUse', [$w['prev_start_date'], $w['prev_end_date']]);
            })
            ->selectRaw("
                TRIM(riskindicators) as name,
                SUM(CASE WHEN eventdateToUse BETWEEN ? AND ? THEN 1 ELSE 0 END) as current_count,
                SUM(CASE WHEN eventdateToUse BETWEEN ? AND ? THEN 1 ELSE 0 END) as prev_count
            ", [
                $w['current_start_date'],
                $w['current_end_date'],
                $w['prev_start_date'],
                $w['prev_end_date'],
            ])
            ->groupBy(DB::raw('TRIM(riskindicators)'))
            ->orderByDesc('current_count')
            ->limit(5)
            ->get();

        return $rows->map(function ($row) {
            $yoyChange = ($row->prev_count > 0)
                ? round((($row->current_count - $row->prev_count) / $row->prev_count) * 100, 1)
                : ($row->current_count > 0 ? 100.0 : 0.0);

            return [
                'name'       => $row->name,
                'count'      => (int) $row->current_count,
                'yoy_change' => $yoyChange,
                'trend'      => $yoyChange > 10 ? 'rising' : ($yoyChange < -10 ? 'falling' : 'stable'),
            ];
        })->toArray();
    }

    private function buildTrendSignals(string $state, int $current, int $previous, array $topIndicators): array
    {
        $signals = [];

        if ($previous > 0) {
            $pct = round((($current - $previous) / $previous) * 100, 1);
            if (abs($pct) > 5) {
                $direction = $pct > 0 ? 'Escalating' : 'Improving';
                $signals[] = ['type' => 'Velocity', 'text' => "Incidents in {$state} are {$direction} at " . abs($pct) . "% over the last 12 months vs the prior period."];
            }
        }

        $rising = collect($topIndicators)->filter(fn($i) => $i['yoy_change'] > 50 && $i['count'] > 5)->sortByDesc('yoy_change')->first();
        if ($rising) {
            $signals[] = ['type' => 'Emerging Threat', 'text' => "'{$rising['name']}' increased {$rising['yoy_change']}% in the rolling 12-month window."];
        }

        return $signals;
    }

    private function getMovementSignals(string $state, string $start, string $end): array
    {
        $keywords = ['curfew', 'sit-at-home', 'roadblock', 'checkpoint', 'movement restriction'];
        $count = DB::table('tbldataentry')
            ->whereRaw('LOWER(TRIM(location)) = ?', [strtolower($state)])
            ->whereBetween('eventdateToUse', [$start, $end])
            ->where(function ($q) use ($keywords) {
                foreach ($keywords as $kw) {
                    $q->orWhereRaw("LOWER(COALESCE(add_notes, '')) LIKE ?", ["%{$kw}%"]);
                }
            })
            ->count();

        return ['has_movement_restrictions' => $count > 0, 'restriction_incidents' => (int) $count];
    }

    private function getRecentSpike(string $state): ?array
    {
        $last30End    = now()->toDateString();
        $last30Start  = now()->subDays(29)->toDateString();
        $prior30End   = now()->subDays(30)->toDateString();
        $prior30Start = now()->subDays(59)->toDateString();

        $row = DB::table('tbldataentry')
            ->whereRaw('LOWER(TRIM(location)) = ?', [strtolower($state)])
            ->where(function ($q) use ($last30Start, $last30End, $prior30Start, $prior30End) {
                $q->whereBetween('eventdateToUse', [$last30Start, $last30End])
                    ->orWhereBetween('eventdateToUse', [$prior30Start, $prior30End]);
            })
            ->selectRaw("
                TRIM(riskindicators) as name,
                SUM(CASE WHEN eventdateToUse BETWEEN ? AND ? THEN 1 ELSE 0 END) as recent,
                SUM(CASE WHEN eventdateToUse BETWEEN ? AND ? THEN 1 ELSE 0 END) as prior
            ", [$last30Start, $last30End, $prior30Start, $prior30End])
            ->groupBy(DB::raw('TRIM(riskindicators)'))
            ->havingRaw('recent > 3')
            ->orderByDesc('recent')
            ->first();

        if (!$row) return null;

        $growth = ($row->prior > 0) ? round((($row->recent - $row->prior) / $row->prior) * 100) : 100;

        return $growth >= 30
            ? ['indicator' => $row->name, 'recent_count' => (int) $row->recent, 'growth_pct' => $growth]
            : null;
    }

    private function normaliseState(string $state): string
    {
        return trim(ucwords(strtolower(str_replace(['_', '-'], ' ', $state))));
    }
}
