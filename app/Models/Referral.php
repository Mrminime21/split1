<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Referral extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'referrer_id',
        'referred_id',
        'level',
        'commission_rate',
        'total_commission_earned',
        'total_referral_volume',
        'status',
        'first_earning_date',
        'last_earning_date',
    ];

    protected $casts = [
        'commission_rate' => 'decimal:2',
        'total_commission_earned' => 'decimal:2',
        'total_referral_volume' => 'decimal:2',
        'first_earning_date' => 'date',
        'last_earning_date' => 'date',
    ];

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    public function referred(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_id');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}