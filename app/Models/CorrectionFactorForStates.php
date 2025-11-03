<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CorrectionFactorForStates extends Model
{
    use HasFactory;
    protected $fillable = ['state', 'incident_correction', 'victim_correction','death_correction'];

}
