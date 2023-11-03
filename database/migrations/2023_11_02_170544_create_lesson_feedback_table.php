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
        Schema::create('lesson_feedback', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('learner_lesson_id');
            $table->foreign('learner_lesson_id')->references('id')->on('lessons')->onDelete('cascade')->onUpdate('cascade');
            $table->uuid('learner_lesson_subject_id');
            $table->foreign('learner_lesson_subject_id')->references('id')->on('lesson_subjects')->onDelete('cascade')->onUpdate('cascade');
            $table->string('parent_tutor');
            $table->uuid('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->text('feedback');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lesson_feedback');
    }
};
