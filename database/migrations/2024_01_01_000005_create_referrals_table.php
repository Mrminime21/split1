<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('referrals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('referrer_id');
            $table->uuid('referred_id');
            $table->tinyInteger('level')->check('level IN (1, 2, 3)');
            $table->decimal('commission_rate', 5, 2);
            $table->decimal('total_commission_earned', 12, 2)->default(0.00);
            $table->decimal('total_referral_volume', 12, 2)->default(0.00);
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
            $table->date('first_earning_date')->nullable();
            $table->date('last_earning_date')->nullable();
            $table->timestamps();

            $table->foreign('referrer_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('referred_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['referrer_id', 'referred_id']);
            $table->index(['referrer_id', 'referred_id', 'level']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referrals');
    }
};