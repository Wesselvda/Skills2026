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
        Schema::create('chapters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->integer('credit_reward')->default(0);
            $table->integer('chapter_order')->nullable();
            $table->integer('estimated_minutes')->nullable();
            $table->enum('content_type', ['video_text', 'hands_on', 'quiz', 'project'])->nullable();

            $table->index('course_id');
            $table->index('chapter_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chapters');
    }
};
