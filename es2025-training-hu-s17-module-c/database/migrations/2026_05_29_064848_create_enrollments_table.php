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
        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('course_id');
            $table->dateTime('enrolled_at', 3)->useCurrent();

            $table->unique(['user_id', 'course_id'], 'enrollments_user_id_course_id_key');
            $table->index('user_id', 'enrollments_user_id_idx');
            $table->index('course_id', 'enrollments_course_id_idx');

            $table->foreign('user_id', 'enrollments_user_id_fkey')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enrollments');
    }
};
