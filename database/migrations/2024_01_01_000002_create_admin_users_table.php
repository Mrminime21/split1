<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_users', function (Blueprint $table) {
            $table->id();
            $table->string('username', 50)->unique();
            $table->string('email', 100)->unique();
            $table->string('password');
            $table->enum('role', ['super_admin', 'admin', 'moderator', 'support'])->default('admin');
            $table->json('permissions')->nullable();
            $table->string('two_factor_secret', 32)->nullable();
            $table->boolean('two_factor_enabled')->default(false);
            $table->enum('status', ['active', 'suspended', 'inactive'])->default('active');
            $table->timestamp('last_login')->nullable();
            $table->timestamp('last_activity')->nullable();
            $table->integer('login_attempts')->default(0);
            $table->timestamp('locked_until')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index(['role', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_users');
    }
};