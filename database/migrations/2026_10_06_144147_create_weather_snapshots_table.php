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
        Schema::create('weather_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('weather_location_id')->constrained()->cascadeOnDelete();
            $table->string('source')->default('Open-Meteo');
            $table->json('current');
            $table->json('hourly');
            $table->json('daily');
            $table->timestamp('fetched_at');
            $table->timestamps();

            $table->index(['weather_location_id', 'fetched_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weather_snapshots');
    }
};
