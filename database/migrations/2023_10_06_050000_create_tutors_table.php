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
        Schema::create('tutors', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('delivery_mode');
            $table->string('birthday');
            $table->string('nationality');
            $table->string('state');
            $table->string('current_location');
            $table->text('how_did_you_know_about_us');
            $table->string('status')->nullable();
            $table->text('other_subjects')->nullable();
            $table->text('comment')->nullable();
            $table->uuid('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tutors');
    }
};
