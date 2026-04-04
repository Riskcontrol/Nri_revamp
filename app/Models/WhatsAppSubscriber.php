<?php

// File: app/Models/WhatsAppSubscriber.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WhatsAppSubscriber extends Model
{
    use HasFactory;

    protected $table = 'whatsapp_alert_subscribers';

    protected $fillable = [
        'phone_number',
        'name',
        'subscription_tier',
        'state_filter',
        'is_active',
        'opt_in_token',
        'opted_in_at',
        'opted_out_at',
    ];

    protected $casts = [
        'state_filter' => 'array',
        'is_active'    => 'boolean',
        'opted_in_at'  => 'datetime',
        'opted_out_at' => 'datetime',
    ];

    // ── Scopes ────────────────────────────────────────────────────────────────

    /** Only confirmed, active subscribers */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->whereNotNull('opted_in_at');
    }

    /** Filter subscribers who should receive a given risk level */
    public function scopeForRiskLevel($query, string $level)
    {
        return $query->where(function ($q) use ($level) {
            $q->where('subscription_tier', 'all')
                ->orWhere('subscription_tier', strtolower($level));
        });
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /** Format the phone for Twilio: "whatsapp:+2348012345678" */
    public function twilioRecipient(): string
    {
        return 'whatsapp:' . $this->phone_number;
    }

    /** True if subscriber has opted to receive alerts for a given state */
    public function wantsAlertsForState(string $state): bool
    {
        if (empty($this->state_filter)) {
            return true; // NULL / empty = all states
        }

        return in_array($state, $this->state_filter, true);
    }
}
