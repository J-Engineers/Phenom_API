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
        Schema::create('tutors_identities', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('acquired_date');
            $table->string('expiration_date');
            $table->string('link');
            $table->boolean('status')->nullable();
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
        Schema::dropIfExists('tutors_identities');
    }
};
