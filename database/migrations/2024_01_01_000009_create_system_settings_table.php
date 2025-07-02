<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('setting_key', 100)->unique();
            $table->text('setting_value');
            $table->enum('setting_type', ['string', 'number', 'boolean', 'json', 'text'])->default('string');
            $table->string('category', 50)->default('general');
            $table->text('description')->nullable();
            $table->boolean('is_public')->default(false);
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->index(['category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};