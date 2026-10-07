<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('weather_settings', function (Blueprint $table) {
            $table->id();
            $table->string('temperature_unit')->default('celsius');
            $table->string('wind_speed_unit')->default('kmh');
            $table->unsignedSmallInteger('refresh_interval_minutes')->default(15);
            $table->decimal('heavy_rain_threshold_mm', 6, 2)->default(10);
            $table->decimal('high_temperature_threshold_c', 5, 2)->default(35);
            $table->decimal('strong_wind_threshold_kmh', 6, 2)->default(50);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weather_settings');
    }
};
