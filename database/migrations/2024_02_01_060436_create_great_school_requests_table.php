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

        Schema::create('great_school_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('great_school_id');
            $table->foreign('great_school_id')->references('id')->on('great_schools')->onDelete('cascade')->onUpdate('cascade');
            $table->string('name');
            $table->string('dob');
            $table->string('phone');
            $table->string('address');
            $table->string('email');
            $table->string('gender');
            $table->string('description');
            $table->string('picture');
            $table->string('transcript');
            $table->string('prev_school');
            $table->string('prev_school_note');
            $table->string('token');
            $table->string('status')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('great_school_requests');
    }
};
