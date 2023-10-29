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
        Schema::create('tutors_certifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('organization');
            $table->string('course');
            $table->string('duration');
            $table->string('date');
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
        Schema::dropIfExists('tutors_certifications');
    }
};
