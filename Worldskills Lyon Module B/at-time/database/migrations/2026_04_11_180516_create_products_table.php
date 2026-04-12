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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->string('gtin', 14)->unique();
            $table->string('name_en');
            $table->string('name_fr');
            $table->text('description_en');
            $table->text('description_fr');
            $table->string('brand');
            $table->string('country_of_origin');
            $table->decimal('gross_weight', 10, 2);
            $table->decimal('net_weight', 10, 2);
            $table->string('weight_unit', 10);
            $table->string('image_path')->nullable();
            $table->boolean('is_hidden')->default(false);
            $table->timestamps();

            $table->index('gtin');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
