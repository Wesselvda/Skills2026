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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_key')->unique();
            $table->unsignedTinyInteger('rating')->nullable();
            $table->integer('price')->nullable();
            $table->unsignedTinyInteger('percentageOfWear')->nullable();
            $table->dateTime('startedAt')->nullable();
            $table->dateTime('endedAt')->nullable();
            $table->string('bicycle_ref');
            $table->string('tariff_ref');
            $table->string('user_ref');
            $table->json('photos')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
