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
        Schema::create('investment_requests', function (Blueprint $table) {
            $table->id();
            $table->enum('status', ['pending', 'approved', 'blocked'])->default('pending');
            $table->string('investor_name');
            $table->string('investor_email');
            $table->string('investor_address');
            $table->string('investor_phone');
            $table->enum('investment_type', ['turbine', 'presenting', 'support']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('investment_requests');
    }
};
