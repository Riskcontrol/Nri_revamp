<?php

// File: app/Http/Controllers/WhatsAppWebhookController.php
//
// WHAT WAS BROKEN:
// The old version had `use Twilio\Security\RequestValidator;`
// That class is in the Twilio SDK. Even though twilio/sdk is now installed,
// this import was failing in some environments. Replaced with PHP's built-in
// hash_hmac() which needs zero external dependencies.

namespace App\Http\Controllers;

use App\Models\WhatsAppSubscriber;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class WhatsAppWebhookController extends Controller
{
    public function handle(Request $request): Response
    {
        // ── 1. Verify this is really from Twilio ──────────────────────────────
        if (! $this->isValidTwilioRequest($request)) {
            Log::warning('[WhatsApp Webhook] Invalid signature — rejected', [
                'ip' => $request->ip(),
            ]);
            abort(403);
        }

        // ── 2. Parse the message ──────────────────────────────────────────────
        $from = str_replace('whatsapp:', '', $request->input('From', ''));
        $body = strtoupper(trim($request->input('Body', '')));

        Log::info('[WhatsApp Webhook] Incoming', [
            'from' => $from,
            'body' => $body,
        ]);

        if (empty($from)) {
            return $this->twiml('');
        }

        $subscriber = WhatsAppSubscriber::where('phone_number', $from)->first();

        if (! $subscriber) {
            Log::info('[WhatsApp Webhook] Unknown number — ignored', ['from' => $from]);
            return $this->twiml('');
        }

        // ── 3. YES → confirm opt-in ───────────────────────────────────────────
        if ($body === 'YES') {
            if ($subscriber->is_active) {
                return $this->twiml(
                    "You're already subscribed to NRI Security Alerts.\n\nReply *STOP* to unsubscribe."
                );
            }

            $subscriber->is_active   = true;
            $subscriber->opted_in_at = now();
            $subscriber->opted_out_at = null;
            $subscriber->save();

            Log::info('[WhatsApp Webhook] Opt-in confirmed ✓', ['phone' => $from]);

            return $this->twiml(
                "✅ You're now subscribed to *NRI Security Alerts*.\n\n" .
                    "You'll receive immediate notifications when high-risk or critical " .
                    "incidents are recorded across Nigeria.\n\n" .
                    "Reply *STOP* at any time to unsubscribe.\n\n" .
                    "— Nigeria Risk Index"
            );
        }

        // ── 4. STOP → opt-out ─────────────────────────────────────────────────
        if (in_array($body, ['STOP', 'UNSUBSCRIBE', 'CANCEL', 'QUIT', 'END'], true)) {
            $subscriber->is_active    = false;
            $subscriber->opted_out_at = now();
            $subscriber->save();

            Log::info('[WhatsApp Webhook] Opt-out received', ['phone' => $from]);

            return $this->twiml(
                "You've been unsubscribed from NRI Security Alerts.\n\n" .
                    "You will no longer receive incident notifications.\n\n" .
                    "— Nigeria Risk Index"
            );
        }

        // ── 5. Anything else — no reply ───────────────────────────────────────
        return $this->twiml('');
    }

    // ── Twilio signature verification using PHP's built-in hash_hmac() ────────
    // No SDK needed. Docs: https://www.twilio.com/docs/usage/webhooks/webhooks-security
    private function isValidTwilioRequest(Request $request): bool
    {
        // Always pass in local — tunnel tools like outray don't forward the
        // X-Twilio-Signature header correctly anyway
        if (config('app.env') === 'local') {
            return true;
        }

        $signature = $request->header('X-Twilio-Signature', '');
        $authToken = config('services.twilio.token');
        $url       = $request->fullUrl();

        // Build validation string: URL + alphabetically sorted POST params concatenated
        $params = $request->post();
        ksort($params);
        $data = $url . implode('', array_map(
            fn($k, $v) => $k . $v,
            array_keys($params),
            array_values($params)
        ));

        $expected = base64_encode(hash_hmac('sha1', $data, $authToken, true));

        return hash_equals($expected, $signature);
    }

    // ── TwiML helper ──────────────────────────────────────────────────────────
    private function twiml(string $message): Response
    {
        $xml = $message === ''
            ? '<?xml version="1.0" encoding="UTF-8"?><Response/>'
            : '<?xml version="1.0" encoding="UTF-8"?><Response><Message>'
            . htmlspecialchars($message, ENT_XML1, 'UTF-8')
            . '</Message></Response>';

        return response($xml, 200)->header('Content-Type', 'text/xml; charset=UTF-8');
    }
}
