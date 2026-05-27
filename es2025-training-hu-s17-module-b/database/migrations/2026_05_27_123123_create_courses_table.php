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
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->integer('total_credits')->nullable();
            $table->enum('difficulty_level', ['beginner', 'intermediate', 'advanced'])->nullable();
            $table->integer('estimated_hours')->nullable();
            $table->enum('status', ['draft', 'active', 'archived'])->default('draft');
            $table->string('category', 100)->nullable();
            $table->dateTime('created_date')->nullable();
            $table->string('instructor_name', 100)->nullable();

            $table->index('status');
            $table->index('category');
            $table->index('difficulty_level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
