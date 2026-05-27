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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->integer('amount');
            $table->enum('transaction_type', ['credit_earned', 'credit_spent', 'manual_adjustment']);
            $table->text('description')->nullable();
            $table->enum('related_entity_type', ['course', 'chapter', 'mentor_session', 'manual_adjustment'])->nullable();
            $table->integer('related_entity_id')->nullable();
            $table->dateTime('created_at')->useCurrent();
            $table->string('processed_by', 100)->default('system');

            $table->index('user_id');
            $table->index('transaction_type');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
