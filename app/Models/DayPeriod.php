<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DayPeriod extends Model
{
    use HasFactory;
    protected $table = 'day_period';

    protected $fillable = [
        'id',
        'name',
    ];

    public $timestamps = FALSE;
}
