<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class NewsletterSubscriber extends Model
{
    protected $fillable = [
        'email',
        'token',
        'confirmed',
        'confirmed_at',
    ];

    protected $casts = [
        'confirmed'    => 'boolean',
        'confirmed_at' => 'datetime',
    ];

    /**
     * Generate a unique unsubscribe/confirm token.
     */
    public static function generateToken(): string
    {
        return Str::random(64);
    }
}
