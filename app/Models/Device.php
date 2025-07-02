<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Device extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'device_id',
        'name',
        'model',
        'serial_number',
        'location',
        'latitude',
        'longitude',
        'status',
        'daily_rate',
        'setup_fee',
        'max_speed_down',
        'max_speed_up',
        'uptime_percentage',
        'total_earnings',
        'total_rentals',
        'specifications',
        'features',
        'images',
        'installation_date',
        'warranty_expires',
        'maintenance_schedule',
        'last_maintenance',
        'next_maintenance',
        'notes',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'daily_rate' => 'decimal:2',
        'setup_fee' => 'decimal:2',
        'uptime_percentage' => 'decimal:2',
        'total_earnings' => 'decimal:2',
        'specifications' => 'array',
        'features' => 'array',
        'images' => 'array',
        'installation_date' => 'date',
        'warranty_expires' => 'date',
        'last_maintenance' => 'date',
        'next_maintenance' => 'date',
    ];

    public function rentals(): HasMany
    {
        return $this->hasMany(Rental::class);
    }

    public function activeRentals(): HasMany
    {
        return $this->hasMany(Rental::class)->where('status', 'active');
    }

    public function isAvailable(): bool
    {
        return $this->status === 'available';
    }
}