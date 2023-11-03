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
        Schema::create('lessons', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('parent_id');
            $table->foreign('parent_id')->references('id')->on('parent_user')->onDelete('cascade')->onUpdate('cascade');
            $table->uuid('learner_id');
            $table->foreign('learner_id')->references('id')->on('learners')->onDelete('cascade')->onUpdate('cascade');
            $table->string('lesson_address');
            $table->text('lesson_goals');
            $table->string('lesson_mode');
            $table->string('lesson_period');
            $table->text('description_of_learner');
            $table->uuid('education_level_id');
            $table->foreign('education_level_id')->references('id')->on('education_levels')->onDelete('cascade')->onUpdate('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lessons');
    }
};
