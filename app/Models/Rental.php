<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Rental extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'device_id',
        'payment_id',
        'plan_type',
        'plan_name',
        'rental_duration',
        'daily_profit_rate',
        'total_cost',
        'setup_fee',
        'expected_daily_profit',
        'actual_total_profit',
        'total_days_active',
        'performance_bonus',
        'status',
        'auto_renew',
        'start_date',
        'end_date',
        'actual_start_date',
        'actual_end_date',
        'last_profit_date',
        'cancellation_reason',
        'notes',
    ];

    protected $casts = [
        'daily_profit_rate' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'setup_fee' => 'decimal:2',
        'expected_daily_profit' => 'decimal:2',
        'actual_total_profit' => 'decimal:2',
        'performance_bonus' => 'decimal:2',
        'auto_renew' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
        'actual_start_date' => 'date',
        'actual_end_date' => 'date',
        'last_profit_date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isExpired(): bool
    {
        return $this->end_date < now()->toDateString();
    }
}