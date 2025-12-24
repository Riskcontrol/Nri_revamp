<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataInsightsCategory extends Model
{
    use HasFactory;
    protected $fillable = ['name'];

    public function dataInsights()
    {
        return $this->hasMany(DataInsights::class);
    }
}
