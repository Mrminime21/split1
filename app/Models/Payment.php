<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'transaction_id',
        'external_id',
        'amount',
        'currency',
        'crypto_currency',
        'crypto_amount',
        'exchange_rate',
        'payment_method',
        'payment_provider',
        'provider_transaction_id',
        'provider_response',
        'status',
        'type',
        'description',
        'metadata',
        'fee_amount',
        'net_amount',
        'webhook_received',
        'webhook_data',
        'processed_at',
        'expires_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'crypto_amount' => 'decimal:8',
        'exchange_rate' => 'decimal:8',
        'fee_amount' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'provider_response' => 'array',
        'metadata' => 'array',
        'webhook_data' => 'array',
        'webhook_received' => 'boolean',
        'processed_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
}