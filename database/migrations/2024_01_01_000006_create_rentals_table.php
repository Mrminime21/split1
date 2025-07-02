<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rentals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('device_id');
            $table->uuid('payment_id')->nullable();
            $table->enum('plan_type', ['basic', 'standard', 'premium', 'custom']);
            $table->string('plan_name', 100)->nullable();
            $table->integer('rental_duration');
            $table->decimal('daily_profit_rate', 5, 2);
            $table->decimal('total_cost', 12, 2);
            $table->decimal('setup_fee', 8, 2)->default(0.00);
            $table->decimal('expected_daily_profit', 8, 2);
            $table->decimal('actual_total_profit', 12, 2)->default(0.00);
            $table->integer('total_days_active')->default(0);
            $table->decimal('performance_bonus', 8, 2)->default(0.00);
            $table->enum('status', ['pending', 'active', 'completed', 'cancelled', 'suspended', 'expired'])->default('pending');
            $table->boolean('auto_renew')->default(false);
            $table->date('start_date');
            $table->date('end_date');
            $table->date('actual_start_date')->nullable();
            $table->date('actual_end_date')->nullable();
            $table->date('last_profit_date')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('device_id')->references('id')->on('devices')->onDelete('cascade');
            $table->foreign('payment_id')->references('id')->on('payments')->onDelete('set null');
            $table->index(['user_id', 'device_id', 'status', 'start_date', 'end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rentals');
    }
};