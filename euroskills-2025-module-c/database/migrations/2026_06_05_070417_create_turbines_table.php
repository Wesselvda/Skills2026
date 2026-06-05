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
            $table->string('name')->nullable();
            $table->float('lat')->nullable();
            $table->float('lng')->nullable();
            $table->string('status')->nullable();
            $table->float('rpm')->nullable();
            $table->float('powerMw')->nullable();
            $table->float('yaw')->nullable();
            $table->float('pitch')->nullable();
            $table->float('temperature')->nullable();
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
