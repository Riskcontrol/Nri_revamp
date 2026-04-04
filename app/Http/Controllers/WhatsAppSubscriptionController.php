<?php

// File: app/Http/Controllers/WhatsAppSubscriptionController.php

namespace App\Http\Controllers;

use App\Models\WhatsAppSubscriber;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WhatsAppSubscriptionController extends Controller
{
    // ── PUBLIC: Subscribe form submission ─────────────────────────────────────
    // Called from the /news page subscription widget (POST /whatsapp/subscribe)

    public function subscribe(Request $request, WhatsAppService $whatsApp)
    {
        $validated = $request->validate([
            'phone_number'      => ['required', 'string', 'regex:/^\+234[0-9]{10}$/'],
            'name'              => ['nullable', 'string', 'max:80'],
            'subscription_tier' => ['nullable', 'in:all,critical'],
            'state_filter'      => ['nullable', 'array'],
            'state_filter.*'    => ['string', 'max:50'],
        ], [
            'phone_number.regex' => 'Please enter a valid Nigerian number starting with +234.',
        ]);

        $phone = $validated['phone_number'];

        // Existing subscriber — check state
        $existing = WhatsAppSubscriber::where('phone_number', $phone)->first();

        if ($existing) {
            if ($existing->is_active) {
                return back()->with('whatsapp_info', 'This number is already subscribed to NRI alerts.');
            }
            // Previously unsubscribed — re-issue opt-in prompt
            $token = Str::random(32);
            $existing->update([
                'opt_in_token'  => $token,
                'opted_out_at'  => null,
            ]);
            $whatsApp->sendOptInPrompt($phone, $existing->name ?? $validated['name'] ?? '');
            return back()->with('whatsapp_success', 'A confirmation message has been sent to your WhatsApp. Reply YES to activate.');
        }

        // New subscriber — create pending record and send opt-in
        $subscriber = WhatsAppSubscriber::create([
            'phone_number'      => $phone,
            'name'              => $validated['name'] ?? null,
            'subscription_tier' => $validated['subscription_tier'] ?? 'all',
            'state_filter'      => ! empty($validated['state_filter']) ? $validated['state_filter'] : null,
            'is_active'         => false,
            'opt_in_token'      => Str::random(32),
        ]);

        $sid = $whatsApp->sendOptInPrompt($phone, $subscriber->name ?? '');

        if (! $sid) {
            Log::error('[WhatsApp Subscribe] Failed to send opt-in prompt', ['phone' => $phone]);
            return back()->with('whatsapp_error', 'Could not send confirmation. Please try again shortly.');
        }

        Log::info('[WhatsApp Subscribe] Opt-in prompt sent', ['phone' => $phone]);

        return back()->with('whatsapp_success', 'Almost there! Reply YES to the WhatsApp message we just sent you to activate your alerts.');
    }

    // ── ADMIN: List all subscribers ───────────────────────────────────────────

    public function index()
    {
        $subscribers = WhatsAppSubscriber::orderByDesc('created_at')->paginate(30);
        $logCounts   = DB::table('whatsapp_alert_log')
            ->selectRaw('phone_number, COUNT(*) as total, SUM(status = "sent" OR status = "delivered") as delivered')
            ->groupBy('phone_number')
            ->pluck('delivered', 'phone_number'); // keyed by phone

        return view('admin.whatsapp.index', compact('subscribers', 'logCounts'));
    }

    // ── ADMIN: Manually deactivate a subscriber ───────────────────────────────

    public function destroy(WhatsAppSubscriber $subscriber)
    {
        $subscriber->update(['is_active' => false, 'opted_out_at' => now()]);
        return back()->with('success', 'Subscriber deactivated.');
    }

    // ── ADMIN: Alert delivery log ─────────────────────────────────────────────

    public function log()
    {
        $logs = DB::table('whatsapp_alert_log')
            ->orderByDesc('created_at')
            ->paginate(50);

        return view('admin.whatsapp.log', compact('logs'));
    }
}
