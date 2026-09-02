<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SensorReading extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'soil_moisture',
        'soil_adc',
        'is_raining',
        'pump_status',
        'recorded_at',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'soil_moisture' => 'integer',
        'soil_adc' => 'integer',
        'is_raining' => 'boolean',
        'pump_status' => 'boolean',
        'recorded_at' => 'datetime',
    ];
}
