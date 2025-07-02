<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('investments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('payment_id')->nullable();
            $table->string('plan_name', 100);
            $table->integer('plan_duration');
            $table->decimal('investment_amount', 12, 2);
            $table->decimal('daily_rate', 6, 4);
            $table->decimal('expected_daily_profit', 8, 2);
            $table->decimal('total_earned', 12, 2)->default(0.00);
            $table->integer('total_days_active')->default(0);
            $table->boolean('compound_interest')->default(false);
            $table->boolean('auto_reinvest')->default(false);
            $table->decimal('reinvest_percentage', 5, 2)->default(0.00);
            $table->enum('status', ['pending', 'active', 'completed', 'cancelled', 'suspended', 'matured'])->default('pending');
            $table->date('start_date');
            $table->date('end_date');
            $table->date('actual_start_date')->nullable();
            $table->date('maturity_date')->nullable();
            $table->date('last_profit_date')->nullable();
            $table->decimal('early_withdrawal_fee', 5, 2)->default(10.00);
            $table->integer('withdrawal_allowed_after')->default(30);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('payment_id')->references('id')->on('payments')->onDelete('set null');
            $table->index(['user_id', 'status', 'start_date', 'end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('investments');
    }
};