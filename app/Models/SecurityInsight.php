<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SecurityInsight extends Model
{
    protected $fillable = [
        'year',
        'index_type',
        'indicator',
        'summary',
        'insights',
        'model',
        'hash',
        'source',
        'generated_at',
    ];

    protected $casts = [
        'summary'  => 'array',
        'insights' => 'array',
        'generated_at' => 'datetime',
    ];
}
