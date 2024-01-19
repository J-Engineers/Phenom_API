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
        Schema::create('book_store', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('store_user_id');
            $table->foreign('store_user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->string('book_name');
            $table->string('book_author_name');
            $table->string('book_isbn');
            $table->string('book_cover');
            $table->uuid('book_category');
            $table->foreign('book_category')->references('id')->on('store_category')->onDelete('cascade')->onUpdate('cascade');
            $table->string('book_quantity');
            $table->string('status');
            $table->string('book_price');
            $table->text('book_description');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('book_store');
    }
};
