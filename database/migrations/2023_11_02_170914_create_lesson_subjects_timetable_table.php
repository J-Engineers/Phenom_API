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
            $table->uuid('parent_id');
            $table->foreign('parent_id')->references('id')->on('parent_user')->onDelete('cascade')->onUpdate('cascade');
            $table->uuid('learner_id');
            $table->foreign('learner_id')->references('id')->on('learners')->onDelete('cascade')->onUpdate('cascade');
            $table->uuid('learner_lesson_id');
            $table->foreign('learner_lesson_id')->references('id')->on('lessons')->onDelete('cascade')->onUpdate('cascade');
            $table->uuid('learner_lesson_subject_id');
            $table->foreign('learner_lesson_subject_id')->references('id')->on('lesson_subjects')->onDelete('cascade')->onUpdate('cascade');
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
