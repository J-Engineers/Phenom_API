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
        Schema::create('lesson_subjects', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('parent_id');
            $table->foreign('parent_id')->references('id')->on('parent_user')->onDelete('cascade')->onUpdate('cascade');
            $table->uuid('learner_id');
            $table->foreign('learner_id')->references('id')->on('learners')->onDelete('cascade')->onUpdate('cascade');
            $table->uuid('learner_lesson_id');
            $table->foreign('learner_lesson_id')->references('id')->on('lessons')->onDelete('cascade')->onUpdate('cascade');
            $table->uuid('education_level_id');
            $table->foreign('education_level_id')->references('id')->on('education_levels')->onDelete('cascade')->onUpdate('cascade');
            $table->uuid('education_level_subject_id');
            $table->foreign('education_level_subject_id')->references('id')->on('subjects')->onDelete('cascade')->onUpdate('cascade');
            $table->string('learner_lesson_tutor_gender');
            $table->string('learner_lesson_tutor_type');
            $table->string('learner_lesson_status')->nullable();
            $table->uuid('tutor_id')->nullable();
            $table->foreign('tutor_id')->references('id')->on('tutors')->onDelete('cascade')->onUpdate('cascade');
            $table->string('tutor_lesson_status')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lesson_subjects');
    }
};
