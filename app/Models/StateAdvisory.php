<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * StateAdvisory model (v2)
 *
 * Represents one precomputed travel advisory for a state on a given date.
 * Unique key: (state, window_end) where window_end = today's date.
 *
 * Changed from v1:
 *   - `year` column removed; replaced by `window_end` (date)
 *   - scopeForYear() removed; replaced by scopeForDate() and scopeToday()
 *   - Added scopeLatest() for convenience
 */
class StateAdvisory extends Model
{
    use HasFactory;

    protected $table = 'state_advisories';

    protected $fillable = [
        'state',
        'window_end',
        'risk_level',
        'risk_score',
        'advisory_json',
        'payload_json',
        'ai_model',
        'payload_hash',
        'generated_at',
    ];

    protected $casts = [
        'advisory_json' => 'array',
        'payload_json'  => 'array',
        'risk_level'    => 'integer',
        'risk_score'    => 'float',
        'window_end'    => 'date',
        'generated_at'  => 'datetime',
    ];

    // ──────────────────────────────────────────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────────────────────────────────────────

    /** Filter to a specific state (case-insensitive). */
    public function scopeForState($query, string $state)
    {
        return $query->whereRaw('LOWER(state) = ?', [strtolower(trim($state))]);
    }

    /** Filter to a specific window_end date. */
    public function scopeForDate($query, string $date)
    {
        return $query->where('window_end', $date);
    }

    /** Filter to today's advisory. */
    public function scopeToday($query)
    {
        return $query->where('window_end', now()->toDateString());
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Accessors
    // ──────────────────────────────────────────────────────────────────────────

    public function getRiskLabelAttribute(): string
    {
        return match ($this->risk_level) {
            1 => 'Low Risk',
            2 => 'Moderate Risk',
            3 => 'High Risk',
            4 => 'Very High Risk',
            default => 'Unknown',
        };
    }

    public function getRiskColourClassAttribute(): string
    {
        return match ($this->risk_level) {
            1 => 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30',
            2 => 'bg-yellow-500/20 text-yellow-400 border-yellow-500/30',
            3 => 'bg-orange-500/20 text-orange-400 border-orange-500/30',
            4 => 'bg-red-500/20 text-red-400 border-red-500/30',
            default => 'bg-gray-500/20 text-gray-400 border-gray-500/30',
        };
    }

    /** The advisory_label from the AI output, or a sensible default. */
    public function getAdvisoryLabelAttribute(): string
    {
        return $this->advisory_json['advisory_label'] ?? $this->getRiskLabelAttribute();
    }
}
