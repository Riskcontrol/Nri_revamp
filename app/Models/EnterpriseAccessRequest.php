<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EnterpriseAccessRequest extends Model
{
    protected $fillable = [
        'organization_name',
        'organization_type',
        'industry_sector',
        'company_size',

        'primary_use_case',
        'primary_use_case_other',
        'geographic_focus',
        'focus_states',
        'focus_sectors_regions',
        'focus_cities_lgas',
        'features_of_interest',

        'contact_name',
        'contact_email',
        'contact_phone',
        'preferred_contact_method',

        'source_page',
        'attempted_risk_type',
        'attempted_year',

        'status',
        'internal_notes',
    ];

    protected $casts = [
        'geographic_focus' => 'array',
        'focus_states' => 'array',
        'features_of_interest' => 'array',
    ];
}
