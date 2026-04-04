<?php

// File: app/Jobs/WhatsAppAlertJob.php

namespace App\Jobs;

use App\Models\WhatsAppSubscriber;
use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WhatsAppAlertJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // Retry config — 3 attempts, 60-second back-off between each
    public int $tries   = 3;
    public int $backoff = 60;

    // The job will time out after 90 seconds
    public int $timeout = 90;

    public function __construct(
        private readonly string $eventId,
        private readonly string $riskLevel   // 'High' or 'Critical'
    ) {}

    public function handle(WhatsAppService $whatsApp): void
    {
        // ── 1. Guard: don't re-blast the same eventid today ───────────────────
        $alreadySent = DB::table('whatsapp_alert_log')
            ->where('eventid', $this->eventId)
            ->where('created_at', '>=', now()->startOfDay())
            ->whereIn('status', ['sent', 'delivered'])
            ->exists();

        if ($alreadySent) {
            Log::info("[WhatsApp] Alert already sent today for {$this->eventId} — skipping.");
            return;
        }

        // ── 2. Load the parent incident ───────────────────────────────────────
        $incident = DB::table('tbldataentry')
            ->where('eventid', $this->eventId)
            ->first();

        if (! $incident) {
            Log::warning("[WhatsApp] Incident not found: {$this->eventId}");
            return;
        }

        // ── 3. Load matching subscribers ──────────────────────────────────────
        $subscribers = WhatsAppSubscriber::active()
            ->forRiskLevel($this->riskLevel)
            ->get();

        if ($subscribers->isEmpty()) {
            Log::info("[WhatsApp] No active subscribers for {$this->riskLevel} alerts.");
            return;
        }

        // ── 4. Send to each subscriber ────────────────────────────────────────
        foreach ($subscribers as $subscriber) {

            // Skip if subscriber has a state filter that excludes this incident
            if (! $subscriber->wantsAlertsForState($incident->location ?? '')) {
                continue;
            }

            $body = WhatsAppService::buildAlertMessage(
                $incident,
                $this->riskLevel,
                $subscriber->name
            );

            $sid = $whatsApp->send($subscriber->phone_number, $body);

            DB::table('whatsapp_alert_log')->insert([
                'eventid'      => $this->eventId,
                'phone_number' => $subscriber->phone_number,
                'risk_level'   => $this->riskLevel,
                'twilio_sid'   => $sid,
                'status'       => $sid ? 'sent' : 'failed',
                'retry_count'  => $this->attempts() - 1,
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);

            // Brief pause to stay inside Twilio's 14 msg/sec WhatsApp rate limit
            // Only needed when subscriber list grows large (>14)
            if ($subscribers->count() > 14) {
                usleep(80_000); // 80 ms ≈ 12.5 sends/sec
            }
        }

        Log::info("[WhatsApp] Alert dispatched", [
            'eventid'    => $this->eventId,
            'risk_level' => $this->riskLevel,
            'recipients' => $subscribers->count(),
        ]);
    }

    public function failed(\Throwable $e): void
    {
        Log::critical("[WhatsApp] Job permanently failed for {$this->eventId}: {$e->getMessage()}");
    }
}
