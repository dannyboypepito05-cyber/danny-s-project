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
        Schema::create('weather_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('weather_location_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status');
            $table->string('message')->nullable();
            $table->timestamp('synced_at');
            $table->timestamps();

            $table->index(['weather_location_id', 'synced_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weather_sync_logs');
    }
};
