<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MotivesSpecific extends Model
{
    use HasFactory;
    protected $table = 'motives_specific';

    protected $fillable = [
        'id',
        'name',
    ];

    public $timestamps = FALSE;
}
