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
        Schema::create('builds', function (Blueprint $table) {
            $table->id();
            $table->string('title', 40);
            $table->string('intro', 200);
            $table->text('description');
            $table->boolean('active');
            $table->string('thumbnail_filename');
            $table->string('background_filename');
            $table->string('signature_filename');
            $table->string('slug', 44)->unique();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('builds');
    }
};
