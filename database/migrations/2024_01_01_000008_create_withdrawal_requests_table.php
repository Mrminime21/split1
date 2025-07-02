<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('withdrawal_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->decimal('amount', 12, 2);
            $table->decimal('fee_amount', 8, 2)->default(0.00);
            $table->decimal('net_amount', 12, 2);
            $table->enum('withdrawal_method', ['crypto', 'bank_transfer', 'paypal', 'binance']);
            $table->text('withdrawal_address')->nullable();
            $table->json('bank_details')->nullable();
            $table->enum('status', ['pending', 'approved', 'processing', 'completed', 'rejected', 'cancelled'])->default('pending');
            $table->text('admin_notes')->nullable();
            $table->text('user_notes')->nullable();
            $table->unsignedBigInteger('processed_by')->nullable();
            $table->string('transaction_hash', 200)->nullable();
            $table->string('external_transaction_id', 200)->nullable();
            $table->timestamp('requested_at')->useCurrent();
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('processed_by')->references('id')->on('admin_users')->onDelete('set null');
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('withdrawal_requests');
    }
};