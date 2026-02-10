<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class LocationInsightGenerator
{
    public function __construct(private GroqClient $groq) {}

    public function generateWithMeta(string $state, int $year, array $summary, array $fallbackInsights = []): array
    {
        $system = $this->systemPrompt();
        $user   = $this->userPrompt($state, $year, $summary);

        Log::info('GROQ_LOCATION_CALL_START', [
            'state' => $state,
            'year' => $year,
            'summary_keys' => array_keys($summary),
            'summary_bytes' => strlen(json_encode($summary)),
        ]);

        try {
            $json = $this->groq->chatJson($system, $user);

            Log::info('GROQ_LOCATION_CALL_DONE', [
                'state' => $state,
                'year' => $year,
                'returned_type' => gettype($json),
                'returned_count' => is_array($json) ? count($json) : null,
                'first_item' => is_array($json) ? ($json[0] ?? null) : null,
            ]);

            if (!$this->isValidLocationInsightArray($json)) {
                Log::warning('GROQ_LOCATION_INVALID', [
                    'state' => $state,
                    'year' => $year,
                    'preview' => is_array($json) ? array_slice($json, 0, 3) : $json,
                ]);

                return [
                    'insights' => $fallbackInsights,
                    'meta' => ['source' => 'fallback', 'reason' => 'invalid_json'],
                ];
            }

            // Normalize to EXACT UI shape expected by your Blade/JS:
            // [{type:"Velocity", text:"..."}, ...]
            $insights = collect($json)
                ->take(4)
                ->map(function ($x) {
                    return [
                        'type' => trim((string)($x['type'] ?? 'Forecast')),
                        'text' => trim((string)($x['text'] ?? '')),
                    ];
                })
                ->filter(fn($x) => $x['text'] !== '')
                ->values()
                ->all();

            return [
                'insights' => $insights,
                'meta' => ['source' => 'groq'],
            ];
        } catch (\Throwable $e) {
            Log::error('GROQ_LOCATION_EXCEPTION', [
                'state' => $state,
                'year' => $year,
                'error' => $e->getMessage(),
            ]);

            return [
                'insights' => $fallbackInsights,
                'meta' => ['source' => 'fallback', 'reason' => 'exception'],
            ];
        }
    }

    public function hashSummary(array $summary): string
    {
        return hash('sha256', json_encode($summary));
    }

    private function systemPrompt(): string
    {
        return <<<SYS
You are a Nigeria state-level security intelligence analyst.
You MUST generate exactly 4 insights as JSON (no markdown, no extra text).

Rules:
- Use ONLY the provided summary JSON. Do not invent numbers or events.
- Output must be an array of 4 objects.
- Each object MUST have:
  - "type": one of ["Velocity","Emerging Threat","Lethality","Forecast"]
  - "text": 1–2 short sentences, clear and actionable.
SYS;
    }

    private function userPrompt(string $state, int $year, array $summary): string
    {
        $payload = json_encode([
            'state' => $state,
            'year' => $year,
            'summary' => $summary,
        ], JSON_UNESCAPED_SLASHES);

        return "Generate insights for {$state} state ({$year}) from this JSON:\n{$payload}";
    }

    private function isValidLocationInsightArray($json): bool
    {
        if (!is_array($json) || count($json) < 4) return false;

        $allowed = ['Velocity', 'Emerging Threat', 'Lethality', 'Forecast'];

        foreach (array_slice($json, 0, 4) as $row) {
            if (!is_array($row)) return false;
            if (!isset($row['type'], $row['text'])) return false;
            if (!in_array($row['type'], $allowed, true)) return false;
            if (!is_string($row['text']) || trim($row['text']) === '') return false;
        }
        return true;
    }
}
