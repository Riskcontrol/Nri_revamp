<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewReport extends Model
{
    protected $table = 'new_reports';

    protected $fillable = [
        'title',
        'slug',
        'period',
        'description',
        'image_path',
        'file_path',
        'min_tier',
        'is_published',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'min_tier' => 'integer',
    ];
}
