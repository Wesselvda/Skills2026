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
        Schema::create('mentors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('expertise_areas')->nullable();
            $table->integer('hourly_credit_rate')->default(10);
            $table->text('bio')->nullable();
            $table->integer('years_experience')->nullable();
            $table->enum('availability_status', ['available', 'limited', 'unavailable'])->default('available');
            $table->enum('approval_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->dateTime('approval_date')->nullable();
            $table->integer('total_sessions_completed')->default(0);
            $table->decimal('average_rating', 3, 2)->nullable();

            $table->index('user_id');
            $table->index('approval_status');
            $table->index('availability_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mentors');
    }
};
