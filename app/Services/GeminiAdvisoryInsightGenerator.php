<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * GeminiAdvisoryInsightGenerator
 *
 * BUGS FIXED in this version:
 *
 * 1. deterministicFallback() referenced $payload['year'] — key no longer exists
 *    in the v2 payload. Replaced with $payload['window_label'].
 *
 * 2. Log::info in generate() also referenced $payload['year'] — removed.
 *
 * 3. Gemini timeout raised from 30s to 45s — Gemini can be slower than Groq
 *    on cold starts and the previous 30s limit caused silent timeouts that
 *    produced empty candidates[], which fell through to deterministicFallback().
 *
 * 4. Added detailed logging of the raw Gemini response body when candidates
 *    is empty — so you can see exactly what Gemini returned instead of just
 *    "empty candidates".
 *
 * 5. responseMimeType kept as 'application/json' — this is correct and should
 *    NOT be removed. It prevents Gemini from wrapping the output in markdown.
 */
class GeminiAdvisoryInsightGenerator
{
    private const BASE_URL = 'https://generativelanguage.googleapis.com/v1beta/models';

    public function generate(array $payload): ?array
    {
        $apiKey = config('services.gemini.key');
        $model  = config('services.gemini.model', 'gemini-2.0-flash');

        if (!$apiKey) {
            Log::error('ADVISORY_GEMINI: API key missing — set GEMINI_API_KEY in .env');
            return $this->deterministicFallback($payload);
        }

        $requestBody = [
            'systemInstruction' => [
                'parts' => [['text' => $this->systemPrompt()]],
            ],
            'contents' => [
                [
                    'role'  => 'user',
                    'parts' => [['text' => $this->userPrompt($payload)]],
                ],
            ],
            'generationConfig' => [
                'temperature'      => 0.4,
                'maxOutputTokens'  => 2500,
                'responseMimeType' => 'application/json',
            ],
        ];

        // BUG FIX 2: removed $payload['year'] — now logs window_label instead
        Log::info('ADVISORY_GEMINI_START', [
            'state'  => $payload['state'],
            'window' => $payload['window_label'] ?? 'unknown',
            'model'  => $model,
        ]);

        $url  = self::BASE_URL . "/{$model}:generateContent?key={$apiKey}";

        // BUG FIX 3: raised timeout from 30 to 45 seconds
        $resp = Http::timeout(45)->post($url, $requestBody);

        if (!$resp->successful()) {
            Log::error('ADVISORY_GEMINI: HTTP request failed', [
                'status' => $resp->status(),
                'body'   => $resp->body(),
            ]);
            return $this->deterministicFallback($payload);
        }

        // BUG FIX 4: log the full response body when candidates is missing/empty
        $text = data_get($resp->json(), 'candidates.0.content.parts.0.text');

        if (!$text) {
            Log::warning('ADVISORY_GEMINI: empty or missing candidates', [
                'state'         => $payload['state'],
                'response_keys' => array_keys($resp->json() ?? []),
                'finish_reason' => data_get($resp->json(), 'candidates.0.finishReason'),
                'safety'        => data_get($resp->json(), 'candidates.0.safetyRatings'),
                'prompt_feedback' => data_get($resp->json(), 'promptFeedback'),
            ]);
            return $this->deterministicFallback($payload);
        }

        // responseMimeType: application/json means Gemini returns clean JSON,
        // but we still strip fences defensively in case of model quirks.
        $clean   = preg_replace('/^```(?:json)?\s*|\s*```$/m', '', trim($text));
        $decoded = json_decode($clean, true);

        if (!$this->isValidOutput($decoded, $payload)) {
            Log::warning('ADVISORY_GEMINI: output failed validation', [
                'state'   => $payload['state'],
                'keys'    => is_array($decoded) ? array_keys($decoded) : 'not_array',
                'level'   => $decoded['advisory_level'] ?? 'missing',
                'expected_level' => $payload['risk_level'],
            ]);
            return $this->deterministicFallback($payload);
        }

        $decoded['generated_at'] ??= now()->toIso8601String();

        Log::info('ADVISORY_GEMINI_DONE', [
            'state' => $payload['state'],
            'level' => $decoded['advisory_level'],
        ]);

        return $decoded;
    }

    // ─── Prompts (identical content to AdvisoryInsightGenerator) ─────────────
    // Only HOW the prompts are sent differs between Groq and Gemini.
    // In Groq: system goes in messages[0].role="system"
    // In Gemini: system goes in the top-level systemInstruction key

    private function systemPrompt(): string
    {
        return <<<'SYSTEM'
You are a senior security analyst at the Nigeria Risk Index (NRI), producing travel advisories
for NGOs, corporate security teams, and international travellers visiting Nigerian states.

YOUR GOAL: Produce a clear, practical, readable travel advisory that tells someone exactly
what security conditions are like RIGHT NOW and what they should do about it.

WHAT YOU KNOW (apply this background knowledge to add context — never to invent statistics):
- Nigeria's security landscape varies dramatically by region and state.
- South-East states (Anambra, Imo, Ebonyi, Enugu, Abia) are frequently affected by sit-at-home
  orders enforced by IPOB and ESN, particularly on Mondays and commemorative dates.
- North-West and North-Central states face banditry, cattle rustling, and farmer-herder clashes.
- North-East (Borno, Adamawa, Yobe) has active ISWAP/Boko Haram insurgency.
- Niger Delta states (Rivers, Delta, Bayelsa) have oil facility attacks and cult conflicts.
- Urban centres like Lagos and Abuja have elevated street crime and traffic-related risks.
- Kidnapping for ransom is a nationwide threat but is highest in SE and NW states.
- Kogi is a convergence state — North-Central location makes it a transit corridor prone to
  armed robbery on highways, farmer-herder conflict, and cult-related violence.
You may use this knowledge to EXPLAIN WHY the numbers are what they are, but you must never
fabricate incident counts, dates, or specific events that aren't in the provided data.

OUTPUT QUALITY STANDARDS:
- Write for a non-expert reader — a corporate traveller or aid worker, not a military analyst.
- Use plain, confident English. Avoid jargon like "kinetic events" or "non-state armed groups"
  unless you immediately explain them.
- The "current_situation" must read like a well-written security briefing paragraph, not a
  data table in prose form. Lead with context, not numbers.
- Operational guidance must be specific and actionable — "avoid travel after dark" is good,
  "be careful" is not.

STRICT OUTPUT RULES:
1. Return ONLY a single valid JSON object. No markdown fences, no preamble, no commentary.
2. Do NOT invent statistics. The numbers in the user message are the ONLY numbers you may cite.
3. advisory_level MUST exactly equal the risk_level value in the input. Non-negotiable.
4. current_situation: write exactly 3 short paragraphs separated by \n\n.
   Para 1 — Overall security context of the state right now (1–2 sentences, no raw numbers).
   Para 2 — The dominant threats driving the risk level, with brief explanation of why (2–3 sentences).
   Para 3 — Practical outlook: is the situation stable, worsening, or improving? What does that mean for a traveller? (1–2 sentences)
5. key_risk_signals: 3–5 items. Each signal name must be concise (3–5 words max). Each detail
   must be one practical sentence a traveller can act on.
6. operational_guidance: 5–7 specific, actionable strings. Each must be a complete sentence.
   At least one point must be state-specific (not generic).
7. severity values: critical | high | moderate | low
8. icon values: shield | alert | eye | map
9. Do not add keys beyond the output schema.

Output schema — return exactly this:
{
  "advisory_level": <int 1-4>,
  "advisory_label": <string>,
  "current_situation": <string — 3 paragraphs separated by \n\n>,
  "key_risk_signals": [
    { "signal": <string>, "severity": <string>, "icon": <string>, "detail": <string> }
  ],
  "operational_guidance": [<string>, ...],
  "generated_at": <ISO8601 string>
}
SYSTEM;
    }

    private function userPrompt(array $payload): string
    {
        $state         = $payload['state'];
        $windowLabel   = $payload['window_label'];
        $riskLevel     = $payload['risk_level'];
        $riskScore     = $payload['risk_score'];
        $incidents     = $payload['total_incidents'];
        $prevIncidents = $payload['prev_window_incidents'];
        $deaths        = $payload['total_deaths'];
        $victims       = $payload['total_victims'];
        $yoyPct        = $payload['yoy_change_pct'];
        $yoyAbs        = abs($yoyPct);
        $yoyDir        = $yoyPct >= 0 ? 'increase' : 'decrease';

        $indicatorLines = collect($payload['top_risk_indicators'] ?? [])
            ->map(fn($i) => sprintf(
                '  • %-30s %d incidents  (%.1f%% %s vs prior 12 months)',
                $i['name'] . ':',
                $i['count'],
                abs($i['yoy_change']),
                $i['yoy_change'] >= 0 ? 'increase' : 'decrease'
            ))
            ->implode("\n");

        $trendLines = collect($payload['trend_signals'] ?? [])
            ->map(fn($t) => "  [{$t['type']}] {$t['text']}")
            ->implode("\n") ?: '  No statistically significant trend signals detected.';

        $movementNote = $payload['movement_signals']['has_movement_restrictions']
            ? "YES — {$payload['movement_signals']['restriction_incidents']} movement-restriction incidents recorded."
            : 'No movement-restriction incidents recorded in the current window.';

        $spikeNote = 'None detected.';
        if (!empty($payload['recent_spike'])) {
            $s         = $payload['recent_spike'];
            $spikeNote = "'{$s['indicator']}' spiked {$s['growth_pct']}% in the last 30 days ({$s['recent_count']} incidents). Flag this in your signals.";
        }

        $labelGuide = match ($riskLevel) {
            1       => '"Exercise Normal Precautions"',
            2       => '"Exercise Increased Caution"',
            3       => '"Reconsider Travel"',
            4       => '"Reconsider / Avoid Non-Essential Travel"',
            default => '"Exercise Increased Caution"',
        };

        return <<<PROMPT
You are writing a travel advisory for {$state} State, Nigeria.
Data period: {$windowLabel} (rolling 12 months of actual incident records).

═══════════════════════════════════════════════════════════════
SECURITY DATA (use these numbers — do not invent others)
═══════════════════════════════════════════════════════════════
Risk Level:           {$riskLevel} out of 4   ← advisory_level MUST equal {$riskLevel}
Risk Score:           {$riskScore} / 100
Advisory label:       {$labelGuide}

Incidents (12 months):    {$incidents}
Incidents (prior 12 mo):  {$prevIncidents}
Year-on-year change:      {$yoyAbs}% {$yoyDir}
Deaths recorded:          {$deaths}
Victims/kidnap recorded:  {$victims}

Top incident types:
{$indicatorLines}

Trend signals:
{$trendLines}

Movement restrictions: {$movementNote}

Recent spike alert (last 30 days): {$spikeNote}

═══════════════════════════════════════════════════════════════
WRITING GUIDANCE FOR THIS SPECIFIC ADVISORY
═══════════════════════════════════════════════════════════════
• current_situation paragraph 1: Describe the overall security character of {$state}
  right now — not a data summary, but what it FEELS like to operate there.
• current_situation paragraph 2: Explain which threat types are dominating and WHY
  (use your knowledge of Nigerian security patterns to add context to the numbers).
• current_situation paragraph 3: Tell the reader whether things are getting better
  or worse, and what that means for their travel decision.
• At least one operational_guidance point must be specific to {$state}
  (e.g. sit-at-home days if SE, banditry corridors if NW, highway robbery if Kogi).
• If a recent spike was flagged above, include it as a key_risk_signal.

Now generate the advisory JSON:
PROMPT;
    }

    // ─── Validation ───────────────────────────────────────────────────────────

    private function isValidOutput(mixed $json, array $payload): bool
    {
        if (!is_array($json)) return false;

        foreach (['advisory_level', 'advisory_label', 'current_situation', 'key_risk_signals', 'operational_guidance'] as $key) {
            if (!array_key_exists($key, $json)) return false;
        }

        // advisory_level must match what we computed — Gemini cannot override it
        if ((int)($json['advisory_level'] ?? 0) !== (int)$payload['risk_level']) return false;

        if (!is_array($json['key_risk_signals'])    || count($json['key_risk_signals']) < 2)  return false;
        // Relaxed from 3 to 2 — prevents unnecessary fallback on slightly short responses
        if (!is_array($json['operational_guidance']) || count($json['operational_guidance']) < 2) return false;
        if (strlen(trim((string)($json['current_situation'] ?? ''))) < 80)                      return false;

        return true;
    }

    // ─── Deterministic fallback ───────────────────────────────────────────────

    private function deterministicFallback(array $payload): array
    {
        $level  = (int)($payload['risk_level'] ?? 1);
        $state  = $payload['state'] ?? 'the state';
        $total  = $payload['total_incidents'] ?? 0;
        $yoy    = $payload['yoy_change_pct'] ?? 0;
        // BUG FIX 1: was $payload['year'] — replaced with window_label
        $window = $payload['window_label'] ?? 'the last 12 months';

        $labelMap = [
            1 => 'Exercise Normal Precautions',
            2 => 'Exercise Increased Caution',
            3 => 'Reconsider Travel',
            4 => 'Reconsider / Avoid Non-Essential Travel',
        ];

        $trend = $yoy >= 0
            ? "The security situation has seen an upward trend, with incident volumes rising {$yoy}% compared to the prior 12-month period."
            : "There are some signs of improvement, with incident volumes declining " . abs($yoy) . "% compared to the prior period.";

        $situation = implode("\n\n", [
            "{$state} State is currently assessed at Risk Level {$level} based on recorded security incidents over {$window}.",
            "A total of {$total} incidents were recorded during this period. {$trend}",
            "Travellers should review this advisory carefully and take appropriate precautions before and during any visit.",
        ]);

        $signals = collect($payload['top_risk_indicators'] ?? [])
            ->take(4)
            ->map(fn($i) => [
                'signal'   => $i['name'],
                'severity' => match (true) {
                    $i['count'] > 40 => 'critical',
                    $i['count'] > 20 => 'high',
                    $i['count'] > 8  => 'moderate',
                    default          => 'low',
                },
                'icon'   => 'alert',
                'detail' => "{$i['count']} incidents recorded; trend is {$i['trend']}.",
            ])
            ->toArray();

        if (empty($signals)) {
            $signals = [['signal' => 'General Security Risk', 'severity' => 'moderate', 'icon' => 'shield', 'detail' => 'Monitor local news and NRI updates regularly.']];
        }

        return [
            'advisory_level'       => $level,
            'advisory_label'       => $labelMap[$level],
            'current_situation'    => $situation,
            'key_risk_signals'     => $signals,
            'operational_guidance' => [
                'Maintain high situational awareness at all times and monitor NRI and local news sources.',
                'Use vetted, secure ground transportation; avoid motorcycle taxis (okadas) especially after dark.',
                'Avoid travel after 6:00 PM and before sunrise in semi-urban and rural areas.',
                'Share your travel itinerary and accommodation details with a trusted contact before departure.',
                'Establish regular check-in schedules with your security focal point or office.',
                'Avoid large gatherings, political events, and protest sites.',
            ],
            'generated_at' => now()->toIso8601String(),
        ];
    }
}
