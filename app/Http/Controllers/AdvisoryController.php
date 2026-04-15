<?php

namespace App\Http\Controllers;

use App\Models\StateAdvisory;
use App\Models\StateInsight;
use App\Services\AdvisoryDataAggregator;
use App\Services\AdvisoryInsightGenerator;
use App\Services\GeminiAdvisoryInsightGenerator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class AdvisoryController extends Controller
{
    private AdvisoryDataAggregator $aggregator;
    private AdvisoryInsightGenerator|GeminiAdvisoryInsightGenerator $generator;

    public function __construct(AdvisoryDataAggregator $aggregator)
    {
        $this->aggregator = $aggregator;

        $this->generator = config('services.advisory.provider') === 'gemini'
            ? app(GeminiAdvisoryInsightGenerator::class)
            : app(AdvisoryInsightGenerator::class);
    }

    // ── Page render ───────────────────────────────────────────────────────────

    public function show(Request $request, string $state): \Illuminate\View\View
    {
        $state     = $this->normaliseState($state);
        $allStates = $this->getAllStates();

        if (!$allStates->contains($state)) {
            abort(404, "No advisory data found for: {$state}");
        }

        return view('advisory.show', [
            'state'     => $state,
            'allStates' => $allStates,
        ]);
    }

    // ── JSON API ──────────────────────────────────────────────────────────────

    public function getData(Request $request, string $state): JsonResponse
    {
        $state    = $this->normaliseState($state);
        $today    = now()->toDateString();
        $cacheKey = "advisory_response:{$state}:{$today}";

        $advisory = Cache::remember($cacheKey, 900, function () use ($state) {
            return $this->resolveAdvisory($state);
        });

        if (!$advisory) {
            return response()->json([
                'error' => 'Advisory data is currently unavailable. Please try again shortly.',
            ], 503);
        }

        return response()->json([
            'state'        => $state,
            'window_label' => $advisory->payload_json['window_label'] ?? null,
            'window_start' => $advisory->payload_json['window_start'] ?? null,
            'window_end'   => $advisory->payload_json['window_end']   ?? null,
            'advisory'     => $advisory->advisory_json,
            // FIX: always use the DB column which is now always set from payload,
            // never from the AI output (see generateAndStore below)
            'risk_level'   => $advisory->risk_level,
            'risk_score'   => $advisory->risk_score,
            'risk_table'   => $advisory->payload_json['area_risk_table'] ?? [],
            'generated_at' => $advisory->generated_at?->toIso8601String(),
            'last_updated' => $advisory->updated_at?->toIso8601String(),
        ]);
    }

    public function regenerate(Request $request, string $state): JsonResponse
    {
        $state = $this->normaliseState($state);
        $today = now()->toDateString();

        $this->aggregator->invalidate($state);
        Cache::forget("advisory_response:{$state}:{$today}");

        $advisory = $this->generateAndStore($state);

        if (!$advisory) {
            return response()->json(['success' => false, 'message' => 'Regeneration failed.'], 500);
        }

        return response()->json([
            'success'      => true,
            'message'      => "Advisory for {$state} regenerated.",
            'risk_level'   => $advisory->risk_level,
            'generated_at' => $advisory->generated_at?->toIso8601String(),
        ]);
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function resolveAdvisory(string $state): ?StateAdvisory
    {
        $today    = now()->toDateString();
        $existing = StateAdvisory::forState($state)->latest('generated_at')->first();

        if ($existing && $this->isAdvisoryFresh($existing, $today)) {
            return $existing;
        }

        return $this->generateAndStore($state);
    }

    private function isAdvisoryFresh(StateAdvisory $advisory, string $today): bool
    {
        $windowEnd = $advisory->payload_json['window_end'] ?? null;
        return $windowEnd === $today;
    }

    private function generateAndStore(string $state): ?StateAdvisory
    {
        try {
            $payload = $this->aggregator->build($state);
            $hash    = hash('sha256', json_encode($payload));

            $existing = StateAdvisory::forState($state)->latest('generated_at')->first();
            if ($existing && $this->isAdvisoryFresh($existing, now()->toDateString()) && $existing->payload_hash === $hash) {
                Log::info("ADVISORY: payload unchanged for {$state}, skipping AI regen.");
                return $existing;
            }

            $aiOutput = $this->generator->generate($payload);

            if (!$aiOutput) {
                Log::error("ADVISORY: AI generation returned null for {$state}");
                return null;
            }

            $windowEnd = $payload['window_end'];

            $advisory = StateAdvisory::updateOrCreate(
                ['state' => $state, 'window_end' => $windowEnd],
                [
                    // ── FIX: risk_level ALWAYS comes from the aggregator payload ──────
                    // Never from $aiOutput['advisory_level'].
                    //
                    // Why: The AI is instructed to echo back the risk_level we sent it,
                    // but sometimes it returns a different value (e.g. it "upgrades" Lagos
                    // from Level 3 to Level 4 based on its general knowledge). Even when
                    // the validator catches this and falls back to deterministicFallback(),
                    // the fallback correctly uses $payload['risk_level'] — but the old code
                    // was still persisting $aiOutput['advisory_level'] to the DB column,
                    // meaning the banner showed the AI's overridden value, not the
                    // aggregator's computeRiskScoreAndLevel() result.
                    //
                    // The advisory_json still contains advisory_level (for the AI narrative
                    // context), but the authoritative level for display is always the
                    // payload value computed by the CalculatesRisk trait.
                    'risk_level'    => $payload['risk_level'],   // ← aggregator, not AI
                    'risk_score'    => $payload['risk_score'],
                    'advisory_json' => $aiOutput,
                    'payload_json'  => $payload,
                    'ai_model'      => config('services.groq.model'),
                    'payload_hash'  => $hash,
                    'generated_at'  => now(),
                ]
            );

            return $advisory;
        } catch (\Throwable $e) {
            Log::error("ADVISORY: pipeline failed for {$state}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }

    private function normaliseState(string $state): string
    {
        return trim(ucwords(strtolower(str_replace(['_', '-'], ' ', $state))));
    }

    private function getAllStates()
    {
        return Cache::remember(
            'advisory_states_list',
            86400,
            fn() => StateInsight::orderBy('state', 'asc')->pluck('state')
        );
    }
}
