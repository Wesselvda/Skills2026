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
        Schema::create('payment_sessions', function (Blueprint $table) {
            $table->uuid("id")->primary();
            $table->decimal('amount', 10, 2);
            $table->string('order_id');
            $table->timestamp('expires_at');
            $table->string('callback_url');
            $table->enum('status', ['pending', 'failed', 'successful'])->default('pending');
            $table->string('error_code')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_sessions');
    }
};
