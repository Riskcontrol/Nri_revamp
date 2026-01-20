<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportRecipient extends Model
{
    protected $fillable = [
        'email',
        'last_state_requested',
        'last_lga_requested',
        'request_count',
        'last_request_at'
    ];
}
