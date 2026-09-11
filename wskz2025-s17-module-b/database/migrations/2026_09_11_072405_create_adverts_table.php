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
        // title,text,status,price,views_count,category,author,photos,paid_services
        Schema::create('adverts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('text');
            $table->string('status');
            $table->decimal('price', 10, 2);
            $table->integer('views_count')->default(0);
            $table->string('category_id');
            $table->string('author_email');
            $table->json('photos')->nullable();
            $table->json('paid_services')->nullable();
            $table->timestamps();

            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
            $table->foreign('author_email')->references('email')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('adverts');
    }
};
