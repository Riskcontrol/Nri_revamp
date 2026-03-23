<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * DataImport
 *
 * Tracks every bulk import job: the file name, how many rows were inserted or
 * failed, per-row error details, processing time, and status.
 *
 * WHY THE CAST ON failed_rows WAS REMOVED:
 *
 * The original model had `'failed_rows' => 'array'` in $casts.  Eloquent's
 * 'array' cast automatically JSON-decodes the value when you read it and
 * JSON-encodes it when you write it.  But DataImportController was already
 * calling json_encode() on the errors array before passing it to update():
 *
 *   $import->update(['failed_rows' => json_encode($result['errors'])]);
 *
 * With the cast active, Eloquent then double-encoded it on the way in
 * (storing a JSON-encoded string of a JSON-encoded string), and on the way
 * out it decoded once — leaving a raw JSON string instead of the expected
 * PHP array.  The controller then called json_decode() again on it, which
 * worked, but the saved value in the DB was always double-wrapped.
 *
 * Fix: store `failed_rows` as a plain string (raw JSON).  The controller
 * always calls json_encode() before storing and json_decode() after reading,
 * which is consistent and correct.  The cast is simply removed.
 */
class DataImport extends Model
{
    protected $table = 'data_import';

    protected $fillable = [
        'sheet_name',
        'rows_inserted',
        'rows_failed',
        'total_rows',
        'failed_rows',      // stored as a raw JSON string — no Eloquent cast
        'processing_time',
        'user_id',
        'status',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ── Computed helpers ──────────────────────────────────────────────────────

    /**
     * Percentage of rows that succeeded (0–100, two decimals).
     */
    public function getSuccessRateAttribute(): float
    {
        if ($this->total_rows == 0) return 0.0;
        return round(($this->rows_inserted / $this->total_rows) * 100, 2);
    }

    /**
     * True when every row was inserted successfully.
     */
    public function isSuccessful(): bool
    {
        return $this->rows_failed == 0 && $this->rows_inserted > 0;
    }

    /**
     * True when some rows succeeded and some failed.
     */
    public function isPartial(): bool
    {
        return $this->rows_failed > 0 && $this->rows_inserted > 0;
    }

    /**
     * True when no rows were inserted at all.
     */
    public function isFailed(): bool
    {
        return $this->rows_inserted == 0 && $this->rows_failed > 0;
    }

    /**
     * Safely decode the failed_rows JSON string to an array.
     * Returns an empty array on any decode error.
     */
    public function getFailedRowsArrayAttribute(): array
    {
        $decoded = json_decode($this->failed_rows ?? '[]', true);
        return is_array($decoded) ? $decoded : [];
    }
}
