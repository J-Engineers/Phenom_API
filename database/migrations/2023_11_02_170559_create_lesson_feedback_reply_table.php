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
        Schema::create('lesson_feedback_reply', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('feedback_id');
            $table->foreign('feedback_id')->references('id')->on('lesson_feedback')->onDelete('cascade')->onUpdate('cascade');
            $table->uuid('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->string('parent_tutor_admin');
            $table->text('response_reply');
            $table->text('response_status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lesson_feedback_reply');
    }
};
