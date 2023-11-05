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
        Schema::create('lesson_subjects_timetable', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('lesson_subject_id');
            $table->foreign('lesson_subject_id')->references('id')->on('lesson_subjects')->onDelete('cascade')->onUpdate('cascade');
            $table->uuid('lesson_day_id');
            $table->foreign('lesson_day_id')->references('id')->on('lesson_day')->onDelete('cascade')->onUpdate('cascade');
            $table->string('lesson_day_hours');
            $table->string('lesson_day_start_time');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lesson_subjects_timetable');
    }
};
