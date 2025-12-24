<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataInsights extends Model
{
    use HasFactory;
    protected $fillable = ['title', 'description', 'featureimage', 'featureimagealt', 'content', 'file', 'alt', 'morecontent','category_id','lastupdatedby', 'keywords', 'slug','state','sent_to_users'];
    public function lastUpdatedByUser()
    {
        return $this->belongsTo('App\Models\User', 'lastupdatedby');
    }

    public function category()
    {
        return $this->belongsTo(DataInsightsCategory::class);
    }
    public function state()
    {
        return $this->belongsTo(tblstatepopulation::class, 'state_id');
    }
}
