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
        Schema::create('mentor_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('mentor_name', 255);
            $table->string('expertise', 255);
            $table->enum('experience_level', ['junior', 'mid', 'senior'])->default('mid');
            $table->dateTime('session_date');
            $table->integer('duration_minutes')->default(60);
            $table->integer('credit_cost');
            $table->boolean('is_available')->default(true);
            $table->dateTime('created_at', 3)->useCurrent();

            $table->index('session_date', 'mentor_sessions_session_date_idx');
            $table->index('is_available', 'mentor_sessions_is_available_idx');
            $table->index('expertise', 'mentor_sessions_expertise_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mentor_sessions');
    }
};
