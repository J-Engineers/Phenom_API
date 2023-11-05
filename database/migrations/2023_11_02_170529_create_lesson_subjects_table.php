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
            $table->uuid('lesson_learner_id');
            $table->foreign('lesson_learner_id')->references('id')->on('lesson_learner')->onDelete('cascade')->onUpdate('cascade');
            $table->uuid('subject_id');
            $table->foreign('subject_id')->references('id')->on('subjects')->onDelete('cascade')->onUpdate('cascade');
            $table->string('learner_tutor_gender');
            $table->string('learner_tutor_type');
            $table->string('learner_status')->nullable();
            $table->uuid('tutor_id')->nullable();
            $table->foreign('tutor_id')->references('id')->on('tutors')->onDelete('cascade')->onUpdate('cascade');
            $table->string('tutor_status')->nullable();
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
