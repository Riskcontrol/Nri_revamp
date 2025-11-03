<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StateInsight extends Model
{
    use HasFactory;
    protected $table = 'state_insights';
    protected $fillable = [
        'state',
        'insights'
    ];
}
