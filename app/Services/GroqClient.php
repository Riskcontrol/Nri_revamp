<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * GroqClient
 *
 * BUGS FIXED in this version:
 *
 * 1. NO JSON MODE → OPERATIONAL GUIDANCE FALLING BACK
 *    The previous version sent no response_format instruction to Groq.
 *    Without it, Groq sometimes returns:
 *      a) JSON wrapped in markdown fences: ```json { ... } ```
 *      b) A preamble sentence before the JSON: "Here is the advisory: { ... }"
 *    Both break json_decode(), returning null, which fails isValidOutput(),
 *    which triggers deterministicFallback() — giving you the generic guidance.
 *
 *    Fix: added response_format: { type: "json_object" } to the request payload.
 *    This instructs Groq to return raw JSON with no decoration whatsoever.
 *    Supported on all current Groq models (llama-3.1-70b, mixtral, etc.).
 *
 * 2. NO FENCE STRIPPING
 *    Even with JSON mode, some older Groq models occasionally still wrap output.
 *    Added a fence-stripping pass before json_decode() as a safety net.
 *
 * 3. BETTER FAILURE LOGGING
 *    Now logs the raw response text when JSON decoding fails so you can see
 *    exactly what Groq returned. Essential for diagnosing prompt issues.
 *
 * 4. TIMEOUT RAISED FROM 30s TO 45s
 *    Groq is fast but can spike under load. 30s was causing silent timeouts
 *    on large prompts (2500 tokens of output requested). 45s is safer.
 */
class GroqClient
{
    /**
     * Send a chat completion request and return the parsed JSON response.
     *
     * @param string $system     System prompt (role: system)
     * @param string $user       User prompt (role: user)
     * @param array  $jsonSchema Unused — kept for signature compatibility
     * @param int    $maxTokens  Max output tokens (default 4096)
     * @return array|null        Decoded JSON array, or null on any failure
     */
    public function chatJson(string $system, string $user, array $jsonSchema = [], int $maxTokens = 4096): ?array
    {
        $apiKey = config('services.groq.key');
        $model  = config('services.groq.model');
        $base   = rtrim(config('services.groq.api_url'), '/');

        if (!$apiKey) {
            Log::error('GroqClient: API key missing — set GROQ_API_KEY in .env');
            return null;
        }

        $requestPayload = [
            'model'       => $model,
            'temperature' => 0.4,
            'max_tokens'  => $maxTokens,
            'messages'    => [
                ['role' => 'system', 'content' => $system],
                ['role' => 'user',   'content' => $user],
            ],
            // BUG FIX 1: enable JSON mode — tells Groq to return raw JSON,
            // no markdown fences, no preamble text, no trailing sentences.
            // This is the primary fix for the operational guidance fallback issue.
            'response_format' => ['type' => 'json_object'],
        ];

        // BUG FIX 4: raised timeout from 30 to 45 seconds
        $resp = Http::withToken($apiKey)
            ->timeout(45)
            ->post($base, $requestPayload);

        if (!$resp->successful()) {
            Log::error('GroqClient: HTTP request failed', [
                'status' => $resp->status(),
                'body'   => $resp->body(),
            ]);
            return null;
        }

        $text = data_get($resp->json(), 'choices.0.message.content');

        if (!$text) {
            Log::warning('GroqClient: empty content in response', [
                'finish_reason' => data_get($resp->json(), 'choices.0.finish_reason'),
                'usage'         => data_get($resp->json(), 'usage'),
            ]);
            return null;
        }

        // BUG FIX 2: strip markdown fences defensively, even with JSON mode active
        $clean = preg_replace('/^```(?:json)?\s*|\s*```$/m', '', trim($text));

        $decoded = json_decode($clean, true);

        if (!is_array($decoded)) {
            // BUG FIX 3: log the raw text so you can see what Groq actually returned
            Log::warning('GroqClient: JSON decode failed', [
                'json_error' => json_last_error_msg(),
                'raw_text'   => substr($clean, 0, 500), // first 500 chars is enough to diagnose
            ]);
            return null;
        }

        return $decoded;
    }
}
