<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TargetSubType extends Model
{
    use HasFactory;
    //
    protected $table = 'target_subtype';

    protected $fillable = [
        'id',
        'name',
        'target_type_id'
    ];

    public $timestamps = FALSE;
}
