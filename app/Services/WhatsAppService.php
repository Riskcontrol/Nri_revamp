<?php

// File: app/Services/WhatsAppService.php
//
// WHAT WAS BROKEN:
// Still using `use Twilio\Rest\Client` (SDK class).
// Even with twilio/sdk installed, if the autoloader isn't refreshed on the
// server this crashes. Replaced with Laravel's built-in Http facade (Guzzle)
// which calls the exact same Twilio REST API endpoint — zero SDK needed.

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    private string $sid;
    private string $token;
    private string $from;
    private string $apiUrl;

    public function __construct()
    {
        $this->sid    = config('services.twilio.sid');
        $this->token  = config('services.twilio.token');
        $this->from   = 'whatsapp:' . config('services.twilio.whatsapp_number');
        $this->apiUrl = "https://api.twilio.com/2010-04-01/Accounts/{$this->sid}/Messages.json";
    }

    // ── Send a WhatsApp message ───────────────────────────────────────────────

    public function send(string $to, string $body): ?string
    {
        try {
            $response = Http::withBasicAuth($this->sid, $this->token)
                ->asForm()
                ->timeout(30)
                ->post($this->apiUrl, [
                    'From' => $this->from,
                    'To'   => 'whatsapp:' . $to,
                    'Body' => $body,
                ]);

            if ($response->successful()) {
                $sid = $response->json('sid');
                Log::info('[WhatsApp] Sent ✓', ['to' => $to, 'sid' => $sid]);
                return $sid;
            }

            Log::error('[WhatsApp] Twilio error', [
                'to'     => $to,
                'status' => $response->status(),
                'error'  => $response->json('message') ?? $response->body(),
                'code'   => $response->json('code'),
            ]);

            return null;
        } catch (\Throwable $e) {
            Log::error('[WhatsApp] Exception', ['to' => $to, 'error' => $e->getMessage()]);
            return null;
        }
    }

    // ── Opt-in prompt ─────────────────────────────────────────────────────────

    public function sendOptInPrompt(string $to, string $name): ?string
    {
        $greeting = $name ? "Hello {$name}," : 'Hello,';

        $body = <<<MSG
{$greeting}

You requested to receive *NRI Security Alerts* — real-time notifications when high-risk or critical incidents are recorded across Nigeria.

Reply *YES* to confirm your subscription.
Reply *STOP* at any time to unsubscribe.

— Nigeria Risk Index
MSG;

        return $this->send($to, $body);
    }

    // ── Alert message formatter ───────────────────────────────────────────────

    public static function buildAlertMessage(
        object  $incident,
        string  $riskLevel,
        ?string $recipientName = null
    ): string {
        $emoji    = $riskLevel === 'Critical' ? '🚨' : '⚠️';
        $greeting = $recipientName ? "Hello {$recipientName},\n\n" : '';
        $location = trim(
            ($incident->lga ? $incident->lga . ', ' : '') .
                ($incident->location ?? 'Nigeria')
        );
        $date    = $incident->eventdateToUse ?? now()->format('Y-m-d');
        $summary = trim($incident->add_notes ?? $incident->caption ?? '');
        $summary = $summary ? "\n\n" . $summary : '';

        return <<<MSG
{$emoji} *NRI SECURITY ALERT — {$riskLevel} Risk*

{$greeting}*Incident:* {$incident->caption}
*Type:* {$incident->riskindicators}
*Factor:* {$incident->riskfactors}
*Location:* {$location}
*Date:* {$date}{$summary}

━━━━━━━━━━━━━━━
🔗 Full details: https://nigeriariskindex.com/news

_Reply STOP to unsubscribe._
MSG;
    }
}
