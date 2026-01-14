<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttackGroup extends Model
{
    use HasFactory;
    protected $table = 'attack_group';

    protected $fillable = [
        'id',
        'name',
        'attack_subgroup_id'
    ];

    public $timestamps = FALSE;
}
