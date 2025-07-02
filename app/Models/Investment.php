<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Investment extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'payment_id',
        'plan_name',
        'plan_duration',
        'investment_amount',
        'daily_rate',
        'expected_daily_profit',
        'total_earned',
        'total_days_active',
        'compound_interest',
        'auto_reinvest',
        'reinvest_percentage',
        'status',
        'start_date',
        'end_date',
        'actual_start_date',
        'maturity_date',
        'last_profit_date',
        'early_withdrawal_fee',
        'withdrawal_allowed_after',
        'notes',
    ];

    protected $casts = [
        'investment_amount' => 'decimal:2',
        'daily_rate' => 'decimal:4',
        'expected_daily_profit' => 'decimal:2',
        'total_earned' => 'decimal:2',
        'reinvest_percentage' => 'decimal:2',
        'early_withdrawal_fee' => 'decimal:2',
        'compound_interest' => 'boolean',
        'auto_reinvest' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
        'actual_start_date' => 'date',
        'maturity_date' => 'date',
        'last_profit_date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isMatured(): bool
    {
        return $this->end_date < now()->toDateString();
    }
}