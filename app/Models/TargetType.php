<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TargetType extends Model
{
    use HasFactory;
    //
    protected $table = 'target_type';

    protected $fillable = [
        'id',
        'name',
    ];

    public $timestamps = FALSE;
}
