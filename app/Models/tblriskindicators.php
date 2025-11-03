<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class tblriskindicators extends Model
{
    use HasFactory;
    protected $table = 'tblriskindicators';
    public $timestamps = false;
    protected $guarded = [];


    public function riskFactor()
    {
        return $this->belongsTo(tblriskfactors::class, 'name', 'factors');
    }

    public function advisories()
    {
        return $this->hasMany(Advisories::class, 'indicator'); // risk_indicator_id assuming foreign key is risk_indicator_id
    }
}
