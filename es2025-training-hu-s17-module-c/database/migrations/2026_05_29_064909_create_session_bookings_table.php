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
        Schema::create('session_bookings', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('mentor_session_id');
            $table->enum('status', ['pending', 'confirmed', 'rejected', 'completed', 'cancelled'])->default('pending');
            $table->integer('credits_paid');
            $table->dateTime('booked_at', 3)->useCurrent();

            $table->index('user_id', 'session_bookings_user_id_idx');
            $table->index('status', 'session_bookings_status_idx');
            $table->index('mentor_session_id', 'session_bookings_mentor_session_id_fkey');

            $table->foreign('user_id', 'session_bookings_user_id_fkey')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->foreign('mentor_session_id', 'session_bookings_mentor_session_id_fkey')
                ->references('id')
                ->on('mentor_sessions')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('session_bookings');
    }
};
