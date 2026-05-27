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
        Schema::create('course_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mentor_id')->constrained('mentors')->cascadeOnDelete();
            $table->foreignId('student_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('session_date');
            $table->time('session_time');
            $table->enum('status', ['available', 'booked', 'completed', 'cancelled'])->default('available');
            $table->integer('credit_cost');
            $table->string('topic', 200)->nullable();
            $table->integer('student_rating')->nullable();
            $table->text('student_feedback')->nullable();
            $table->text('mentor_notes')->nullable();
            $table->dateTime('created_at')->useCurrent();

            $table->index('mentor_id');
            $table->index('student_id');
            $table->index('status');
            $table->index('session_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_sessions');
    }
};
