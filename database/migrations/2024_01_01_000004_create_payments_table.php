<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->string('transaction_id', 100)->nullable()->unique();
            $table->string('external_id', 100)->nullable();
            $table->decimal('amount', 12, 2);
            $table->string('currency', 10)->default('USD');
            $table->string('crypto_currency', 20)->nullable();
            $table->decimal('crypto_amount', 20, 8)->nullable();
            $table->decimal('exchange_rate', 15, 8)->nullable();
            $table->enum('payment_method', ['crypto', 'binance', 'card', 'bank_transfer', 'balance', 'manual']);
            $table->string('payment_provider', 50)->nullable();
            $table->string('provider_transaction_id', 200)->nullable();
            $table->json('provider_response')->nullable();
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'cancelled', 'refunded', 'expired'])->default('pending');
            $table->enum('type', ['rental', 'investment', 'withdrawal', 'referral_bonus', 'deposit', 'fee', 'refund']);
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();
            $table->decimal('fee_amount', 12, 2)->default(0.00);
            $table->decimal('net_amount', 12, 2)->nullable();
            $table->boolean('webhook_received')->default(false);
            $table->json('webhook_data')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['user_id', 'status', 'type', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};