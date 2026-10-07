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
        Schema::create('classrooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('instructor_id')->constrained()->restrictOnDelete();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('subject');
            $table->string('grade_level');
            $table->unsignedSmallInteger('capacity');
            $table->string('schedule');
            $table->enum('delivery_mode', ['Physical', 'Online', 'Hybrid'])->default('Physical');
            $table->enum('status', ['Active', 'Draft', 'Archived'])->default('Active');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('classrooms');
    }
};
