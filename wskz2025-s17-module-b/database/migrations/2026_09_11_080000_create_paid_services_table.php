<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paid_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('advert_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->timestamp('activated_at');
            $table->unsignedInteger('validity_days');
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();

            $table->unique(['advert_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paid_services');
    }
};
