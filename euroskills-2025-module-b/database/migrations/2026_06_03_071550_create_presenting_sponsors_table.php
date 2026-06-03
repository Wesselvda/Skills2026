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
        Schema::create('presenting_sponsors', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('investment_id')->nullable();
            $table->enum('status', ['pending', 'approved', 'blocked'])->default('pending');
            $table->string('logo_filename');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presenting_sponsors');
    }
};
