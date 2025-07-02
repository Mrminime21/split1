<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasUuids;

    protected $fillable = [
        'username',
        'email',
        'password',
        'referral_code',
        'referred_by',
        'telegram_id',
        'telegram_username',
        'telegram_first_name',
        'telegram_last_name',
        'telegram_photo_url',
        'balance',
        'total_earnings',
        'total_invested',
        'total_withdrawn',
        'referral_earnings',
        'rental_earnings',
        'investment_earnings',
        'phone',
        'country',
        'timezone',
        'language',
        'status',
        'telegram_verified',
        'kyc_status',
        'kyc_documents',
        'last_login',
        'last_activity',
        'ip_address',
        'user_agent',
        'crypto_wallets',
        'preferred_crypto',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'balance' => 'decimal:2',
        'total_earnings' => 'decimal:2',
        'total_invested' => 'decimal:2',
        'total_withdrawn' => 'decimal:2',
        'referral_earnings' => 'decimal:2',
        'rental_earnings' => 'decimal:2',
        'investment_earnings' => 'decimal:2',
        'telegram_verified' => 'boolean',
        'kyc_documents' => 'array',
        'last_login' => 'datetime',
        'last_activity' => 'datetime',
        'crypto_wallets' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            if (empty($user->referral_code)) {
                $user->referral_code = strtoupper(substr(md5(uniqid()), 0, 10));
            }
        });

        static::created(function ($user) {
            if ($user->referred_by) {
                $user->createReferralRelationships();
            }
        });
    }

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_by');
    }

    public function referrals(): HasMany
    {
        return $this->hasMany(User::class, 'referred_by');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function rentals(): HasMany
    {
        return $this->hasMany(Rental::class);
    }

    public function investments(): HasMany
    {
        return $this->hasMany(Investment::class);
    }

    public function withdrawalRequests(): HasMany
    {
        return $this->hasMany(WithdrawalRequest::class);
    }

    public function referralRelationships(): HasMany
    {
        return $this->hasMany(Referral::class, 'referrer_id');
    }

    public function createReferralRelationships()
    {
        if (!$this->referred_by) return;

        // Level 1 referral
        Referral::create([
            'referrer_id' => $this->referred_by,
            'referred_id' => $this->id,
            'level' => 1,
            'commission_rate' => config('app.referral_level_1_rate', 7.00),
            'status' => 'active',
        ]);

        // Level 2 referral
        $level2Referrer = User::find($this->referred_by)?->referred_by;
        if ($level2Referrer) {
            Referral::create([
                'referrer_id' => $level2Referrer,
                'referred_id' => $this->id,
                'level' => 2,
                'commission_rate' => config('app.referral_level_2_rate', 5.00),
                'status' => 'active',
            ]);

            // Level 3 referral
            $level3Referrer = User::find($level2Referrer)?->referred_by;
            if ($level3Referrer) {
                Referral::create([
                    'referrer_id' => $level3Referrer,
                    'referred_id' => $this->id,
                    'level' => 3,
                    'commission_rate' => config('app.referral_level_3_rate', 3.00),
                    'status' => 'active',
                ]);
            }
        }
    }
}