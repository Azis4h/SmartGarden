<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PumpControl extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'command',
        'is_executed',
        'executed_at',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'is_executed' => 'boolean',
        'executed_at' => 'datetime',
    ];

    /**
     * Get the latest pending command (not yet executed by ESP32).
     */
    public static function latestPending(): ?self
    {
        return static::where('is_executed', false)->latest()->first();
    }
}
