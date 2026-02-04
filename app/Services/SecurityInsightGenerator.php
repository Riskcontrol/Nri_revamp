<?php

namespace App\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class SecurityInsightGenerator
{
    public function __construct(private GroqClient $groq) {}

    /**
     * $summary is an array of compact signals (top states, zones, trends, etc.)
     * returns: array of insights [{title,text}, ...]
     */
    public function generate(string $indexType, int $year, array $summary): array
    {
        $system = $this->systemPrompt();
        $user   = $this->userPrompt($indexType, $year, $summary);

        // We want strict JSON output:
        $json = $this->groq->chatJson($system, $user);

        // If AI fails, fallback to deterministic insights:
        if (!$this->isValidInsightArray($json)) {
            return $this->fallbackInsights($indexType, $year, $summary);
        }

        $insights = collect($json)
            ->take(3)
            ->map(function ($x) {
                $bullets = array_merge(
                    (array)($x['evidence'] ?? []),
                    (array)($x['implications'] ?? []),
                    (array)($x['recommended_actions'] ?? [])
                );

                $bulletText = collect($bullets)
                    ->map(fn($b) => trim((string)$b))
                    ->filter()
                    ->take(6)
                    ->map(fn($b) => "• " . $b)
                    ->implode("\n");

                $assessment = trim((string)($x['assessment'] ?? ''));

                return [
                    'title' => Str::limit((string)($x['title'] ?? 'Insight'), 90, ''),
                    'text'  => trim($assessment . "\n\n" . $bulletText),
                ];
            })
            ->filter(fn($x) => trim($x['text'] ?? '') !== '')
            ->values()
            ->all();
        return $insights;
    }

    public function generateWithMeta(string $indexType, int $year, array $summary): array
    {
        $system = $this->systemPrompt();
        $user   = $this->userPrompt($indexType, $year, $summary);

        // 1) PROVE we are about to call Groq, and show summary size (no sensitive dump)
        \Log::info('GROQ_CALL_START', [
            'indexType' => $indexType,
            'year' => $year,
            'summary_keys' => array_keys($summary),
            'summary_bytes' => strlen(json_encode($summary)),
        ]);

        try {
            $json = $this->groq->chatJson($system, $user);

            // 2) PROVE we got a response back (type + count)
            \Log::info('GROQ_CALL_DONE', [
                'indexType' => $indexType,
                'year' => $year,
                'returned_type' => gettype($json),
                'returned_count' => is_array($json) ? count($json) : null,
                'first_item' => is_array($json) ? ($json[0] ?? null) : null,
            ]);

            if (!$this->isValidInsightArray($json)) {
                // 3) Validator rejected it — log the exact payload (truncate to avoid huge logs)
                \Log::warning('GROQ_INVALID_INSIGHTS', [
                    'indexType' => $indexType,
                    'year' => $year,
                    'returned_type' => gettype($json),
                    'returned_count' => is_array($json) ? count($json) : null,
                    'returned_preview' => is_array($json)
                        ? array_slice($json, 0, 3)
                        : (is_string($json) ? mb_substr($json, 0, 800) : $json),
                ]);

                return [
                    'insights' => $this->fallbackInsights($indexType, $year, $summary),
                    'meta' => [
                        'source' => 'fallback',
                        'reason' => 'invalid_json',
                    ],
                ];
            }

            // Normalize AI insights
            $insights = collect($json)
                ->take(3)
                ->map(function ($x) {
                    $bullets = array_merge(
                        (array)($x['evidence'] ?? []),
                        (array)($x['implications'] ?? []),
                        (array)($x['recommended_actions'] ?? [])
                    );

                    $bulletText = collect($bullets)
                        ->map(fn($b) => trim((string)$b))
                        ->filter()
                        ->take(6)
                        ->map(fn($b) => "• " . $b)
                        ->implode("\n");

                    $assessment = trim((string)($x['assessment'] ?? ''));

                    return [
                        'title' => Str::limit((string)($x['title'] ?? 'Insight'), 90, ''),
                        'text'  => trim($assessment . "\n\n" . $bulletText),
                    ];
                })
                ->filter(fn($x) => trim($x['text'] ?? '') !== '')
                ->values()
                ->all();


            \Log::info('GROQ_SUCCESS', [
                'indexType' => $indexType,
                'year' => $year,
                'insight_count' => count($insights),
            ]);

            return [
                'insights' => $insights,
                'meta' => [
                    'source' => 'groq',
                ],
            ];
        } catch (\Throwable $e) {
            \Log::error('GROQ_EXCEPTION', [
                'indexType' => $indexType,
                'year' => $year,
                'class' => get_class($e),
                'error' => $e->getMessage(),
            ]);

            return [
                'insights' => $this->fallbackInsights($indexType, $year, $summary),
                'meta' => [
                    'source' => 'fallback',
                    'reason' => 'exception',
                    'error' => $e->getMessage(),
                ],
            ];
        }
    }


    public function hashSummary(array $summary): string
    {
        return hash('sha256', json_encode($summary));
    }

    //     private function systemPrompt(): string
    //     {
    //         return <<<SYS
    // You are a security intelligence analyst.
    // Write 3 concise, high-signal insights based ONLY on the provided summary.
    // No hallucinations. No fake numbers. If a number isn't in the summary, don't invent it.
    // Output MUST be valid JSON: an array of exactly 3 objects: [{ "title": "...", "text": "..." }, ...]
    // SYS;
    //     }

    private function systemPrompt(): string
    {
        return <<<SYS
You are a security intelligence analyst writing a briefing for executives.

CRITICAL INSTRUCTIONS:
1. Use ONLY the provided summary data - do not invent numbers
2. If a metric is missing, state "not available in summary"
3. Your response must be VALID, COMPLETE JSON - start with [ and end with ]
4. Do NOT wrap in markdown code blocks (no ```json or ```)
5. Do NOT add any text before or after the JSON array

OUTPUT FORMAT:
Return exactly 3 JSON objects in an array. Each object must have:
- "title" (string, max 90 characters)
- "assessment" (string, 80-160 words)
- "evidence" (array of 2-5 bullet strings)
- "implications" (array of 2-5 bullet strings)
- "recommended_actions" (array of 2-5 bullet strings)

Example structure:
[
  {
    "title": "...",
    "assessment": "...",
    "evidence": ["...", "..."],
    "implications": ["...", "..."],
    "recommended_actions": ["...", "..."]
  }
]

Return ONLY the JSON array. No commentary.
SYS;
    }


    private function userPrompt(string $indexType, int $year, array $summary): string
    {
        $summaryJson = json_encode($summary, JSON_PRETTY_PRINT);

        return <<<USR
Index Type: {$indexType}
Year: {$year}

Summary (trusted):
{$summaryJson}

Return JSON only. No markdown. No commentary.
USR;
    }

    private function isValidInsightArray($json): bool
    {
        if (!is_array($json) || count($json) !== 3) return false;

        foreach ($json as $row) {
            if (!is_array($row)) return false;

            foreach (['title', 'assessment', 'evidence', 'implications', 'recommended_actions'] as $k) {
                if (!array_key_exists($k, $row)) return false;
            }

            if (trim((string)$row['title']) === '') return false;
            if (trim((string)$row['assessment']) === '') return false;

            foreach (['evidence', 'implications', 'recommended_actions'] as $k) {
                if (!is_array($row[$k]) || count($row[$k]) < 2) return false;
            }
        }

        return true;
    }

    private function fallbackInsights(string $indexType, int $year, array $summary): array
    {
        $topState = Arr::get($summary, 'top_states.0.state', 'N/A');
        $topScore = Arr::get($summary, 'top_states.0.risk_score', null);

        $zone = Arr::get($summary, 'top_zones.0.zone', 'N/A');
        $zoneDeaths = Arr::get($summary, 'top_zones.0.total_deaths', null);

        $yoy = Arr::get($summary, 'national_fatalities.yoy_change_pct', null);

        return [
            [
                'title' => 'Highest Risk Concentration',
                'text'  => $topScore !== null
                    ? "{$topState} records the highest risk score ({$topScore}) for {$year} under {$indexType}."
                    : "{$topState} ranks highest for {$year} under {$indexType} based on the computed risk score."
            ],
            [
                'title' => 'Most Affected Zone',
                'text'  => $zoneDeaths !== null
                    ? "{$zone} is the most impacted zone by fatalities ({$zoneDeaths})."
                    : "{$zone} is the most impacted zone by fatalities."
            ],
            [
                'title' => 'National Direction of Travel',
                'text'  => $yoy !== null
                    ? "Year-over-year fatalities change is {$yoy}% — use this to interpret whether the threat environment is worsening or improving."
                    : "Use year-over-year fatalities trend to determine whether the threat environment is worsening or improving."
            ],
        ];
    }
}
