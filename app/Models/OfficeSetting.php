<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfficeSetting extends Model
{
    protected $fillable = [
        'name',
        'latitude',
        'longitude',
        'radius',
        'check_in_time',
        'check_out_time',
        'late_tolerance',
        'radius_enforced',  
    ];

    protected $casts = [
        'radius_enforced' => 'boolean',
    ];
}