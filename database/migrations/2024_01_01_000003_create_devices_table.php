<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('devices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('device_id', 50)->unique();
            $table->string('name', 100);
            $table->string('model', 50)->default('Starlink Standard');
            $table->string('serial_number', 100)->nullable()->unique();
            $table->string('location', 100)->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->enum('status', ['available', 'rented', 'maintenance', 'offline', 'reserved'])->default('available');
            $table->decimal('daily_rate', 8, 2)->default(15.00);
            $table->decimal('setup_fee', 8, 2)->default(0.00);
            $table->integer('max_speed_down')->default(200);
            $table->integer('max_speed_up')->default(20);
            $table->decimal('uptime_percentage', 5, 2)->default(99.00);
            $table->decimal('total_earnings', 12, 2)->default(0.00);
            $table->integer('total_rentals')->default(0);
            $table->json('specifications')->nullable();
            $table->json('features')->nullable();
            $table->json('images')->nullable();
            $table->date('installation_date')->nullable();
            $table->date('warranty_expires')->nullable();
            $table->string('maintenance_schedule', 20)->default('monthly');
            $table->date('last_maintenance')->nullable();
            $table->date('next_maintenance')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['device_id', 'status', 'location']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};