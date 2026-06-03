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
        Schema::create('turbines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('investment_id')->nullable();
            $table->string('name');
            $table->enum('status', ['available', 'pending', 'approved'])->default('available');
            $table->string('logo_filename')->nullable();
            $table->string('displayed_text')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('turbines');
    }
};
