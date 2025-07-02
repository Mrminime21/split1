<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('username', 50)->unique();
            $table->string('email', 100)->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('referral_code', 20)->unique();
            $table->uuid('referred_by')->nullable();
            $table->bigInteger('telegram_id')->nullable()->unique();
            $table->string('telegram_username', 50)->nullable();
            $table->string('telegram_first_name', 100)->nullable();
            $table->string('telegram_last_name', 100)->nullable();
            $table->text('telegram_photo_url')->nullable();
            $table->decimal('balance', 12, 2)->default(0.00);
            $table->decimal('total_earnings', 12, 2)->default(0.00);
            $table->decimal('total_invested', 12, 2)->default(0.00);
            $table->decimal('total_withdrawn', 12, 2)->default(0.00);
            $table->decimal('referral_earnings', 12, 2)->default(0.00);
            $table->decimal('rental_earnings', 12, 2)->default(0.00);
            $table->decimal('investment_earnings', 12, 2)->default(0.00);
            $table->string('phone', 20)->nullable();
            $table->string('country', 50)->nullable();
            $table->string('timezone', 50)->default('UTC');
            $table->string('language', 10)->default('en');
            $table->enum('status', ['active', 'suspended', 'pending', 'banned'])->default('active');
            $table->boolean('telegram_verified')->default(false);
            $table->enum('kyc_status', ['none', 'pending', 'approved', 'rejected'])->default('none');
            $table->json('kyc_documents')->nullable();
            $table->timestamp('last_login')->nullable();
            $table->timestamp('last_activity')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->json('crypto_wallets')->nullable();
            $table->string('preferred_crypto', 10)->default('BTC');
            $table->rememberToken();
            $table->timestamps();

            $table->foreign('referred_by')->references('id')->on('users')->onDelete('set null');
            $table->index(['referral_code', 'telegram_id', 'status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};