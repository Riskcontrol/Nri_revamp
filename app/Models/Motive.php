<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Motive extends Model
{
    use HasFactory;
    protected $table = 'motive';

    protected $fillable = [
        'id',
        'name',
    ];

    public $timestamps = FALSE;
}
