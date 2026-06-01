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
        Schema::create('chapter_completions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('chapter_id');
            $table->integer('credits_earned')->default(0);
            $table->dateTime('completed_at', 3)->useCurrent();

            $table->unique(['user_id', 'chapter_id'], 'chapter_completions_user_id_chapter_id_key');
            $table->index('user_id', 'chapter_completions_user_id_idx');
            $table->index('chapter_id', 'chapter_completions_chapter_id_idx');

            $table->foreign('user_id', 'chapter_completions_user_id_fkey')
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
        Schema::dropIfExists('chapter_completions');
    }
};
