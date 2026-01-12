<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DataImport extends Model
{
    protected $table = 'data_import';

    protected $fillable = [
        'sheet_name',
        'rows_inserted',
        'rows_failed',
        'total_rows',
        'failed_rows',
        'processing_time',
        'user_id',
        'status',
    ];

    protected $casts = [
        'failed_rows' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user who created this import
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get success rate percentage
     */
    public function getSuccessRateAttribute(): float
    {
        if ($this->total_rows == 0) return 0;
        return round(($this->rows_inserted / $this->total_rows) * 100, 2);
    }

    /**
     * Check if import was successful
     */
    public function isSuccessful(): bool
    {
        return $this->rows_failed == 0 && $this->rows_inserted > 0;
    }

    /**
     * Check if import was partial
     */
    public function isPartial(): bool
    {
        return $this->rows_failed > 0 && $this->rows_inserted > 0;
    }

    /**
     * Check if import completely failed
     */
    public function isFailed(): bool
    {
        return $this->rows_inserted == 0 && $this->rows_failed > 0;
    }
}
