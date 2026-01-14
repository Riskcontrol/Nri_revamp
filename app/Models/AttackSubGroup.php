<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttackSubGroup extends Model
{
    use HasFactory;
    protected $table = 'attack_subgroup';

    protected $fillable = [
        'id',
        'name'
    ];

    public $timestamps = FALSE;
}
