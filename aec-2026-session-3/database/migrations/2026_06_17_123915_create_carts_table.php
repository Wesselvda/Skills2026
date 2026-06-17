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
        Schema::create('carts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->timestamp('expires_at');
            $table->unsignedSmallInteger('expires_in')->default(300);
            $table->string('checkout_status')->nullable();
            $table->string('payment_session_id')->nullable();
            $table->string('payment_url')->nullable();
            $table->string('payment_callback_url')->nullable();
            $table->string('payment_cart_hash')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carts');
    }
};
