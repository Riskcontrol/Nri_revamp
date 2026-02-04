<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GroqClient
{
    public function chatJson(string $system, string $user, array $jsonSchema = [], int $maxTokens = 4096): ?array
    {
        $apiKey = config('services.groq.key');
        $model  = config('services.groq.model');
        $base   = rtrim(config('services.groq.api_url'), '/');

        if (!$apiKey) {
            Log::error('Groq API key missing (GROQ_API_KEY).');
            return null;
        }

        $payload = [
            'model' => $model,
            'temperature' => 0.4,
            'max_tokens' => $maxTokens,
            'messages' => [
                ['role' => 'system', 'content' => $system],
                ['role' => 'user', 'content' => $user],
            ],
        ];

        // If you later want strict structured outputs, you can add schema controls here
        // depending on Groq support for response_format / json_schema in your chosen endpoint.

        $resp = Http::withToken($apiKey)
            ->timeout(30)
            ->post($base, $payload);

        if (!$resp->successful()) {
            Log::error('Groq request failed', [
                'status' => $resp->status(),
                'body' => $resp->body(),
            ]);
            return null;
        }

        $text = data_get($resp->json(), 'choices.0.message.content');
        if (!$text) return null;

        // Expect model to return pure JSON.
        $decoded = json_decode($text, true);
        if (!is_array($decoded)) {
            Log::warning('Groq returned non-JSON; falling back.', ['text' => $text]);
            return null;
        }

        return $decoded;
    }
}
