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
        Schema::create('tutors_qualifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('university');
            $table->string('course');
            $table->string('country');
            $table->string('date_of_graduation');
            $table->string('grade');
            $table->text('degree');
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
        Schema::dropIfExists('tutors_qualifications');
    }
};
