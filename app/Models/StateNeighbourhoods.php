<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DB;

class StateNeighbourhoods extends Model
{
    use HasFactory;
    //
    protected $table = 'state_neighbourhoods';

    public $timestamps = false;

    protected $fillable = [
        'state',
        'state_lga_id',
        'neighbourhood_name',
        'latitude',
        'longitude',
    ];

    public static function handleNeighbourhoods($search_location){

        $neighbourhood_name = DB::table('state_neighbourhoods')->where('neighbourhood_name', 'Like', $search_location)->first();
        return $neighbourhood_name;
    }
}
