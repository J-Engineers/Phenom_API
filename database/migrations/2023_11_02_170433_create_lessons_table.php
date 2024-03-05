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
            $table->string('lesson_address');
            $table->text('lesson_goals');
            $table->string('lesson_mode');
            $table->string('lesson_period');
            $table->string('other_subjects');
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
