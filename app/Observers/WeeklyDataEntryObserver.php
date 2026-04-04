<?php

// File: app/Observers/WeeklyDataEntryObserver.php
// Full replacement — fixes the risk threshold issue for testing

namespace App\Observers;

use App\Jobs\WhatsAppAlertJob;
use App\Models\tblweeklydataentry;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WeeklyDataEntryObserver
{
    public function updated(tblweeklydataentry $entry): void
    {
        // Log EVERY call so we can confirm the observer is firing at all
        Log::info('[Observer] updated() called', [
            'eventid'     => $entry->eventid,
            'news_value'  => $entry->news,
            'was_changed' => $entry->wasChanged('news'),
            'dirty'       => $entry->getDirty(),
        ]);

        // Only act when 'news' changes TO 'Yes'
        if (! $entry->wasChanged('news') || $entry->news !== 'Yes') {
            Log::info('[Observer] Skipping — news not changed to Yes', [
                'eventid'    => $entry->eventid,
                'news'       => $entry->news,
                'wasChanged' => $entry->wasChanged('news'),
            ]);
            return;
        }

        Log::info("[Observer] Breaking News toggled ON — loading incident", [
            'eventid' => $entry->eventid,
        ]);

        // Load parent incident for risk classification
        $incident = DB::table('tbldataentry')
            ->where('eventid', $entry->eventid)
            ->select([
                'Casualties_count',
                'Injuries_count',
                'riskfactors',
                'riskindicators',
                'impact',
            ])
            ->first();

        if (! $incident) {
            Log::warning("[Observer] No tbldataentry row for eventid: {$entry->eventid}");
            return;
        }

        Log::info('[Observer] Incident loaded for classification', [
            'eventid'    => $entry->eventid,
            'casualties' => $incident->Casualties_count,
            'injuries'   => $incident->Injuries_count,
            'riskfactor' => $incident->riskfactors,
            'indicator'  => $incident->riskindicators,
            'impact'     => $incident->impact,
        ]);

        $riskLevel = $this->classifyRisk(
            (int) ($incident->Casualties_count ?? 0),
            (int) ($incident->Injuries_count   ?? 0),
            $incident->riskfactors,
            $incident->riskindicators,
            $incident->impact
        );

        Log::info("[Observer] Risk level classified: {$riskLevel}", [
            'eventid' => $entry->eventid,
        ]);

        // ── TESTING MODE: Lower threshold ─────────────────────────────────────
        // During local testing, most incidents are 'Low' or 'Medium' because
        // test data rarely has 5+ casualties. While testing, we dispatch for
        // ALL risk levels so you can verify the full pipeline works.
        //
        // When going to production, change this back to:
        // if (! in_array($riskLevel, ['High', 'Critical'], true)) { return; }
        // ─────────────────────────────────────────────────────────────────────

        // FOR TESTING — dispatch for any risk level
        // Comment this out and uncomment the production check below when live
        $dispatchForTesting = true;

        // FOR PRODUCTION — only High and Critical
        // $dispatchForTesting = in_array($riskLevel, ['High', 'Critical'], true);

        if (! $dispatchForTesting) {
            Log::info("[Observer] Risk level '{$riskLevel}' below threshold — no alert dispatched.");
            return;
        }

        // Dispatch the job to the 'whatsapp' queue
        WhatsAppAlertJob::dispatch($entry->eventid, $riskLevel)
            ->onQueue('whatsapp')
            ->delay(now()->addSeconds(5));

        Log::info('[Observer] WhatsAppAlertJob dispatched to queue', [
            'eventid'    => $entry->eventid,
            'risk_level' => $riskLevel,
            'queue'      => 'whatsapp',
        ]);
    }

    private function classifyRisk(
        int     $casualties,
        int     $injuries,
        ?string $riskFactor,
        ?string $indicator,
        ?string $impactLevel
    ): string {
        if ($casualties > 10 || $injuries > 10) {
            return 'Critical';
        }
        if (
            $casualties > 5
            || $injuries > 5
            || $impactLevel === 'High'
            || ($riskFactor === 'Violent Threats' && $casualties > 2)
        ) {
            return 'High';
        }
        if ($casualties >= 1 || $injuries > 2 || $impactLevel === 'Medium') {
            return 'Medium';
        }
        return 'Low';
    }
}
