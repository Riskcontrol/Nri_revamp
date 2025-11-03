<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class tblriskfactors extends Model
{
    use HasFactory;
    protected $table = 'tblriskfactors';
    public $timestamps = false;
    protected $guarded = [];

    // Get Risk Indicators of each risk factors
    public function getRiskIndicators()
    {
    	return $this->hasMany(tblriskindicators::class, 'factors', 'name');
    }
}
