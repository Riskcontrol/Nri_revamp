<?php

namespace App\Jobs;

use App\Models\StateAdvisory;
use App\Services\AdvisoryDataAggregator;
use App\Services\AdvisoryInsightGenerator;
use App\Services\GeminiAdvisoryInsightGenerator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * GenerateAdvisoryJob
 *
 * KEY CHANGE from v1: no $year parameter.
 * The aggregator handles the rolling 12-month window internally.
 * This job simply says "generate the current advisory for this state."
 *
 * Dispatched by:
 *  - The daily cron in routes/console.php (all 36 states, 2 AM)
 *  - AdvisoryController::regenerate() (single state, on-demand)
 *  - DataImportController (single state, after a data import)
 *
 * Usage:
 *   GenerateAdvisoryJob::dispatch('Anambra');
 *   GenerateAdvisoryJob::dispatchAllStates();
 */
class GenerateAdvisoryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 120; // Groq is usually < 10 sec; 2 min is generous
    public $tries   = 2;
    public $backoff = 60;

    public function __construct(private string $state) {}

    public function handle(AdvisoryDataAggregator $aggregator): void
    {
        $generator = config('services.advisory.provider') === 'gemini'
            ? app(GeminiAdvisoryInsightGenerator::class)
            : app(AdvisoryInsightGenerator::class);

        Log::info("GenerateAdvisoryJob: starting for {$this->state}");

        try {
            // Always build fresh in a job (bypass the aggregator's 1-hour cache)
            $aggregator->invalidate($this->state);
            $payload = $aggregator->build($this->state);

            $hash      = hash('sha256', json_encode($payload));
            $windowEnd = $payload['window_end']; // today's date
            $existing  = StateAdvisory::forState($this->state)
                ->where('window_end', $windowEnd)
                ->first();

            // Skip AI call if data hasn't changed today
            if ($existing && $existing->payload_hash === $hash) {
                Log::info("GenerateAdvisoryJob: payload unchanged for {$this->state}, skipping AI.");
                return;
            }

            $aiOutput = $generator->generate($payload);

            if (!$aiOutput) {
                Log::error("GenerateAdvisoryJob: AI generation failed for {$this->state}");
                $this->fail(new \RuntimeException("AI returned null for {$this->state}"));
                return;
            }

            StateAdvisory::updateOrCreate(
                ['state' => $this->state, 'window_end' => $windowEnd],
                [
                    'risk_level'    => $aiOutput['advisory_level'],
                    'risk_score'    => $payload['risk_score'],
                    'advisory_json' => $aiOutput,
                    'payload_json'  => $payload,
                    'ai_model'      => config('services.groq.model'),
                    'payload_hash'  => $hash,
                    'generated_at'  => now(),
                ]
            );

            // Clear the response cache so the next page load gets fresh data
            Cache::forget("advisory_response:{$this->state}:{$windowEnd}");

            Log::info("GenerateAdvisoryJob: completed for {$this->state}");
        } catch (\Throwable $e) {
            Log::error("GenerateAdvisoryJob: failed for {$this->state}", ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Dispatch jobs for all states.
     * Called from routes/console.php scheduler.
     */
    public static function dispatchAllStates(): void
    {
        $states = \App\Models\StateInsight::orderBy('state')->pluck('state');

        foreach ($states as $index => $state) {
            static::dispatch($state)
                ->onQueue('advisories')
                ->delay(now()->addSeconds($index * 3)); // 3 sec stagger = ~108 sec for 36 states
        }

        Log::info("GenerateAdvisoryJob: queued {$states->count()} states.");
    }
}
