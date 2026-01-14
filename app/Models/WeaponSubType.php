<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WeaponSubType extends Model
{
    use HasFactory;
    //
    protected $table = 'weapon_subtype';

    protected $fillable = [
        'id',
        'name',
        'weapon_type_id'
    ];

    public $timestamps = FALSE;
}
