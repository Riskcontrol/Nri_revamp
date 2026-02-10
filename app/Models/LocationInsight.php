<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LocationInsight extends Model
{
    protected $fillable = [
        'state',
        'year',
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
