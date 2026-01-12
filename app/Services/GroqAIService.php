<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class GroqAIService
{
    protected Client $http;
    protected string $groqApiKey;
    protected ?string $searchApiKey;
    protected ?string $searchEngineId;
    protected int $maxRetries = 3;

    public function __construct()
    {
        $this->http = new Client([
            'timeout' => 45,
            'connect_timeout' => 15,
            'http_errors' => false, // Handle errors manually
        ]);

        $this->groqApiKey = config('services.groq.key');
        $this->searchApiKey = config('services.search.api_key');
        $this->searchEngineId = config('services.search.engine_id');
    }

    /**
     * Generate comprehensive business report
     */
    public function generateBusinessReport(string $text, string $addNote, ?string $sourceLink = null): array
    {
        // Create cache key
        $cacheKey = 'business_report_' . md5($text . $addNote . ($sourceLink ?? ''));

        // Check cache first
        if (Cache::has($cacheKey)) {
            Log::info('Using cached business report');
            return Cache::get($cacheKey);
        }

        try {
            $aiReport = $this->getDefaultReportStructure();

            // Generate AI report if we have text
            if (!empty($text)) {
                $generatedReport = $this->generateReportFromGroq($text, $addNote, $sourceLink);
                $aiReport = array_merge($aiReport, $generatedReport);
            }

            // Fetch related link if we have add_note
            if (!empty($addNote) && !empty($sourceLink)) {
                $relatedLink = $this->fetchCorroborativeLink($addNote, $sourceLink);
                $aiReport['related_link'] = $relatedLink;
            }

            // Cache for 24 hours
            Cache::put($cacheKey, $aiReport, now()->addHours(24));

            return $aiReport;
        } catch (\Exception $e) {
            Log::error('Business report generation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'text_preview' => substr($text, 0, 100)
            ]);

            return $this->getErrorReportStructure($e->getMessage());
        }
    }

    /**
     * Generate report from Groq API with retry logic
     */
    protected function generateReportFromGroq(string $text, string $addNote, ?string $sourceLink = null): array
    {
        $attempt = 0;
        $lastException = null;

        while ($attempt < $this->maxRetries) {
            try {
                $attempt++;

                $prompt = $this->buildBusinessReportPrompt($text, $addNote, $sourceLink);

                Log::info("Groq API attempt {$attempt}/{$this->maxRetries}");

                $response = $this->http->post('https://api.groq.com/openai/v1/chat/completions', [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $this->groqApiKey,
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                    ],
                    'json' => [
                        'model' => 'llama-3.3-70b-versatile', // Most capable model
                        'messages' => [
                            [
                                'role' => 'system',
                                'content' => 'You are an expert business risk analyst. You MUST respond ONLY with valid JSON. No markdown, no explanations, no additional text - ONLY the JSON object.'
                            ],
                            [
                                'role' => 'user',
                                'content' => $prompt
                            ]
                        ],
                        'temperature' => 0.2, // Very low for consistency
                        'max_tokens' => 2000,
                        'top_p' => 0.9,
                    ]
                ]);

                $statusCode = $response->getStatusCode();
                $body = json_decode($response->getBody(), true);

                // Handle API errors
                if ($statusCode !== 200) {
                    $errorMsg = $body['error']['message'] ?? 'Unknown API error';
                    throw new \RuntimeException("Groq API error (Status {$statusCode}): {$errorMsg}");
                }

                // Validate response structure
                if (!isset($body['choices'][0]['message']['content'])) {
                    throw new \RuntimeException('Invalid Groq API response structure');
                }

                $content = $body['choices'][0]['message']['content'];

                // Parse and validate the response
                $parsedReport = $this->parseResponse($content);

                Log::info('Groq API success', ['attempt' => $attempt]);

                return $parsedReport;
            } catch (\Exception $e) {
                $lastException = $e;

                Log::warning("Groq API attempt {$attempt} failed", [
                    'error' => $e->getMessage(),
                    'attempt' => $attempt
                ]);

                // Wait before retry (exponential backoff)
                if ($attempt < $this->maxRetries) {
                    $waitTime = pow(2, $attempt); // 2, 4, 8 seconds
                    Log::info("Waiting {$waitTime} seconds before retry");
                    sleep($waitTime);
                }
            }
        }

        // All retries failed
        throw new \RuntimeException(
            "Groq API failed after {$this->maxRetries} attempts. Last error: " .
                ($lastException ? $lastException->getMessage() : 'Unknown error')
        );
    }

    /**
     * Build enhanced business report prompt
     */
    protected function buildBusinessReportPrompt(string $text, string $addNote, ?string $sourceLink): string
    {
        $sourceInfo = $sourceLink ? "Source: {$sourceLink}\n\n" : '';
        $contextInfo = !empty($addNote) ? "Additional Context: {$addNote}\n\n" : '';

        return <<<PROMPT
Analyze this security/business incident and provide a structured risk assessment.

Incident Description:
"{$text}"

{$contextInfo}{$sourceInfo}

Respond with ONLY this JSON structure (no markdown, no code blocks, no additional text):

{
    "business_report": "Write a clear 30-40 word executive summary of the business impact",
    "affected_industry": "List 2-4 primary affected industries (max 20 words, e.g., 'Technology, Financial Services, Healthcare')",
    "impact_level": "Rate as exactly 'Low', 'Medium', or 'High'",
    "impact_rationale": "Explain in 25-35 words why you assigned this impact level",
    "associated_risks": "List 4-6 specific business risks in 40-50 words (avoid generic terms like 'reputation damage', be specific like 'customer data exposure', 'regulatory fines', 'service disruption')",
    "business_advisory": "Provide 3-4 actionable recommendations in 35-45 words"
}

CRITICAL RULES:
1. Output ONLY the JSON object, nothing else
2. Use exact field names shown above
3. All fields must be populated with meaningful content
4. impact_level must be exactly: Low, Medium, or High
5. Be specific and actionable, avoid generic corporate speak
6. Do NOT include markdown formatting or code blocks
7. Ensure the JSON is valid and parseable
PROMPT;
    }

    /**
     * Parse and validate Groq response
     */
    protected function parseResponse(string $response): array
    {
        // Log raw response for debugging
        Log::debug('Groq raw response', ['response' => substr($response, 0, 500)]);

        // Clean response - remove any markdown code blocks
        $cleaned = $response;
        $cleaned = preg_replace('/```json\s*/s', '', $cleaned);
        $cleaned = preg_replace('/```\s*/s', '', $cleaned);
        $cleaned = trim($cleaned);

        // Try to parse as JSON
        $data = json_decode($cleaned, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            // Try to extract JSON from text
            if (preg_match('/\{.*\}/s', $cleaned, $matches)) {
                $data = json_decode($matches[0], true);
            }

            if (!$data || json_last_error() !== JSON_ERROR_NONE) {
                throw new \RuntimeException(
                    'Failed to parse Groq response as JSON: ' . json_last_error_msg() .
                        '. Response preview: ' . substr($cleaned, 0, 200)
                );
            }
        }

        // Validate required fields
        $requiredFields = [
            'business_report',
            'affected_industry',
            'impact_level',
            'impact_rationale',
            'associated_risks',
            'business_advisory'
        ];

        $missingFields = [];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty(trim($data[$field]))) {
                $missingFields[] = $field;
            }
        }

        if (!empty($missingFields)) {
            throw new \RuntimeException(
                "Missing or empty required fields in response: " . implode(', ', $missingFields)
            );
        }

        // Normalize and validate impact level
        $impactLevel = strtolower(trim($data['impact_level']));
        $validLevels = ['low', 'medium', 'high'];

        if (!in_array($impactLevel, $validLevels)) {
            Log::warning("Invalid impact level received: {$data['impact_level']}, defaulting to Medium");
            $impactLevel = 'medium';
        }

        $data['impact_level'] = ucfirst($impactLevel);

        // Clean and trim all fields
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $data[$key] = trim($value);
            }
        }

        return $data;
    }

    /**
     * Fetch similar news link using Google Custom Search
     */
    public function fetchSimilarNewsLink(string $text, string $originalUrl): ?string
    {
        if (empty($this->searchApiKey) || empty($this->searchEngineId)) {
            Log::warning('Search API credentials not configured');
            return null;
        }

        // Create cache key
        $cacheKey = 'similar_news_' . md5($text . $originalUrl);

        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        try {
            // Clean and prepare search query
            $cleanedText = $this->cleanSearchQuery($text);
            $query = urlencode($cleanedText);

            $url = "https://www.googleapis.com/customsearch/v1?" . http_build_query([
                'q' => $cleanedText,
                'key' => $this->searchApiKey,
                'cx' => $this->searchEngineId,
                'num' => 10,
                'dateRestrict' => 'd30' // Last 30 days
            ]);

            Log::info('Searching for similar news', ['query_preview' => substr($cleanedText, 0, 100)]);

            $response = $this->http->get($url);
            $statusCode = $response->getStatusCode();

            if ($statusCode !== 200) {
                Log::error('Search API returned non-200 status', ['status' => $statusCode]);
                return null;
            }

            $data = json_decode($response->getBody(), true);

            if (!isset($data['items']) || empty($data['items'])) {
                Log::info('No search results found');
                return null;
            }

            $originalDomain = $this->getBaseDomain($originalUrl);

            // Find first result from different domain
            foreach ($data['items'] as $item) {
                if (!isset($item['link'])) {
                    continue;
                }

                $link = $item['link'];
                $linkDomain = $this->getBaseDomain($link);

                // Skip if same domain
                if ($originalDomain && $linkDomain && $this->isSameDomain($linkDomain, $originalDomain)) {
                    continue;
                }

                Log::info('Found similar news link', ['link' => $link]);

                // Cache for 24 hours
                Cache::put($cacheKey, $link, now()->addHours(24));

                return $link;
            }

            Log::info('No different domain results found');
            return null;
        } catch (GuzzleException $e) {
            Log::error('Search API request failed', [
                'error' => $e->getMessage(),
                'query_preview' => substr($text, 0, 100)
            ]);
            return null;
        }
    }

    /**
     * Clean search query for better results
     */
    protected function cleanSearchQuery(string $text): string
    {
        // Remove special characters but keep important words
        $cleaned = preg_replace('/[^\p{L}\p{N}\s\-]/u', ' ', $text);

        // Remove extra whitespace
        $cleaned = preg_replace('/\s+/', ' ', $cleaned);

        // Limit length (Google has query limits)
        $cleaned = substr(trim($cleaned), 0, 500);

        return $cleaned;
    }

    /**
     * Get base domain from URL
     */
    protected function getBaseDomain(string $url): ?string
    {
        $domain = parse_url($url, PHP_URL_HOST);
        if (!$domain) {
            return null;
        }

        // Remove www.
        $domain = preg_replace('/^www\./', '', $domain);

        // Get main domain (handle co.uk, com.br, etc.)
        $parts = explode('.', $domain);
        if (count($parts) > 2) {
            return implode('.', array_slice($parts, -2));
        }

        return $domain;
    }

    /**
     * Check if two domains are the same
     */
    protected function isSameDomain(string $domain1, string $domain2): bool
    {
        $domain1 = strtolower(trim($domain1));
        $domain2 = strtolower(trim($domain2));

        return $domain1 === $domain2
            || str_ends_with($domain1, ".{$domain2}")
            || str_ends_with($domain2, ".{$domain1}");
    }

    /**
     * Fetch corroborative link (alias for fetchSimilarNewsLink)
     */
    protected function fetchCorroborativeLink(string $text, string $url): ?string
    {
        return $this->fetchSimilarNewsLink($text, $url);
    }

    /**
     * Get default report structure
     */
    protected function getDefaultReportStructure(): array
    {
        return [
            'business_report' => '',
            'affected_industry' => '',
            'impact_level' => '',
            'impact_rationale' => '',
            'associated_risks' => '',
            'business_advisory' => '',
            'related_link' => null
        ];
    }

    /**
     * Get error report structure
     */
    protected function getErrorReportStructure(string $error): array
    {
        return [
            'business_report' => 'Report generation failed',
            'affected_industry' => 'Unable to determine',
            'impact_level' => 'Unknown',
            'impact_rationale' => 'Analysis could not be completed due to technical error',
            'associated_risks' => 'Manual review required',
            'business_advisory' => 'Please review this incident manually and contact support if the issue persists',
            'related_link' => null,
            'error' => $error
        ];
    }
}
