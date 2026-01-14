<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DB;

class tbllga extends Model
{
    use HasFactory;
    protected $table = 'tbllga';

    public $timestamps = false;

    public static function handleLga($search_location){
        $lga = DB::table('tbllga')->where('LGA', 'Like', $search_location)->first();
        return $lga;
    }
}
