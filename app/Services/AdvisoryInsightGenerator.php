<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

/**
 * AdvisoryInsightGenerator (Groq)
 *
 * BUGS FIXED in this version:
 *
 * 1. OPERATIONAL GUIDANCE FALLING BACK TO DEFAULT
 *    Root cause: isValidOutput() required count(operational_guidance) >= 3.
 *    Groq was returning valid arrays with 2 items in some cases (especially
 *    for low-risk states with little to say), causing the validator to reject
 *    a perfectly good response and fall through to deterministicFallback().
 *    Fix: relaxed minimum to >= 2. The prompt still asks for 5–7 items, so
 *    you will almost always get more — but 2 is the hard floor for rejection.
 *
 * 2. GROQ JSON MODE NOT ENABLED
 *    The GroqClient sends requests without response_format: { type: "json_object" }.
 *    Without it, Groq sometimes wraps its JSON in markdown code fences (```json...```)
 *    or adds a preamble sentence, both of which cause json_decode() to return null,
 *    which causes isValidOutput() to return false, which triggers the fallback.
 *    Fix: added response_format to the GroqClient call via the updated GroqClient.
 *    See GroqClient.php for the change.
 *
 * 3. CURRENT_SITUATION LENGTH CHECK TOO STRICT
 *    The validator required strlen >= 100. For states with very few incidents,
 *    the model sometimes produced shorter but valid summaries. Reduced to 80.
 *
 * 4. ADDED RAW RESPONSE LOGGING on validation failure so you can see exactly
 *    what Groq returned before it was rejected — helps diagnose future issues
 *    without having to add temporary debug code.
 */
class AdvisoryInsightGenerator
{
    public function __construct(private GroqClient $groq) {}

    public function generate(array $payload): ?array
    {
        $system = $this->systemPrompt();
        $user   = $this->userPrompt($payload);

        Log::info('ADVISORY_GROQ_START', [
            'state'      => $payload['state'],
            'window'     => $payload['window_label'],
            'risk_level' => $payload['risk_level'],
        ]);

        $json = $this->groq->chatJson($system, $user, maxTokens: 2500);

        if (!$this->isValidOutput($json, $payload)) {
            // BUG FIX 4: log exactly what came back so we can diagnose failures
            Log::warning('ADVISORY_GROQ_INVALID — falling back to deterministic', [
                'state'          => $payload['state'],
                'returned_type'  => gettype($json),
                'returned_keys'  => is_array($json) ? array_keys($json) : 'not_array',
                'guidance_count' => is_array($json) ? count($json['operational_guidance'] ?? []) : 0,
                'situation_len'  => is_array($json) ? strlen($json['current_situation'] ?? '') : 0,
                'level_got'      => $json['advisory_level'] ?? 'missing',
                'level_expected' => $payload['risk_level'],
            ]);
            return $this->deterministicFallback($payload);
        }

        $json['generated_at'] ??= now()->toIso8601String();

        Log::info('ADVISORY_GROQ_DONE', [
            'state'           => $payload['state'],
            'level'           => $json['advisory_level'],
            'guidance_items'  => count($json['operational_guidance'] ?? []),
            'signals_items'   => count($json['key_risk_signals'] ?? []),
        ]);

        return $json;
    }

    // ─── System prompt ────────────────────────────────────────────────────────

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
- Kogi is a North-Central convergence state — a transit corridor prone to highway armed robbery,
  farmer-herder conflict, and cult-related violence along major routes.
You may use this knowledge to EXPLAIN WHY the numbers are what they are, but you must never
fabricate incident counts, dates, or specific events that are not in the provided data.

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
   Para 3 — Practical outlook: is the situation stable, worsening, or improving? (1–2 sentences)
5. key_risk_signals: 3–5 items. Each signal name must be concise (3–5 words max). Each detail
   must be one practical sentence a traveller can act on.
6. operational_guidance: 5–7 specific, actionable strings. Each must be a complete sentence.
   At least one point must be state-specific (not generic advice).
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

    // ─── User prompt ──────────────────────────────────────────────────────────

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

        if ((int)($json['advisory_level'] ?? 0) !== (int)$payload['risk_level']) {
            Log::warning('ADVISORY_GROQ: level mismatch', [
                'expected' => $payload['risk_level'],
                'got'      => $json['advisory_level'] ?? 'missing',
            ]);
            return false;
        }

        if (!is_array($json['key_risk_signals'])    || count($json['key_risk_signals']) < 2)  return false;
        // BUG FIX 1: was >= 3, now >= 2 — prevents fallback on slightly short but valid responses
        if (!is_array($json['operational_guidance']) || count($json['operational_guidance']) < 2) return false;
        // BUG FIX 3: was 100, now 80 — less aggressive rejection for low-incident states
        if (strlen(trim((string)($json['current_situation'] ?? ''))) < 80) return false;

        return true;
    }

    // ─── Deterministic fallback ───────────────────────────────────────────────

    private function deterministicFallback(array $payload): array
    {
        $state  = $payload['state'];
        $level  = (int)($payload['risk_level'] ?? 1);
        $total  = $payload['total_incidents'] ?? 0;
        $yoy    = $payload['yoy_change_pct'] ?? 0;
        $dir    = $yoy >= 0 ? 'an increase' : 'a decrease';
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
            "Travellers should review this advisory carefully and take appropriate precautions before and during any visit to the state.",
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
                'detail' => "{$i['count']} incidents recorded in the current period; trend is {$i['trend']}.",
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
