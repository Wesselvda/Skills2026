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
        Schema::create('api_tokens', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('token', 255);
            $table->dateTime('created_at', 3)->useCurrent();
            $table->dateTime('revoked_at', 3)->nullable();

            $table->unique('token', 'api_tokens_token_key');
            $table->index('user_id', 'api_tokens_user_id_idx');
            $table->index('revoked_at', 'api_tokens_revoked_at_idx');

            $table->foreign('user_id', 'api_tokens_user_id_fkey')
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
        Schema::dropIfExists('api_tokens');
    }
};
