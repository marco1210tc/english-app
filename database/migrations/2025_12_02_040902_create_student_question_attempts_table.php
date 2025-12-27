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
        Schema::create('student_question_attempts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('activity_attempt_id');
            $table->unsignedBigInteger('question_id')->nullable();
            $table->unsignedBigInteger('selected_option_id')->nullable();
            $table->boolean('is_correct')->default(false);
            $table->unsignedInteger('score_obtained')->default(0);
            $table->integer('time_spent_seconds')->default(0);
            $table->integer('hints_used')->default(0);
            $table->timestamp('answered_at')->nullable();
            $table->timestamps();

            $table->foreign('activity_attempt_id')
                ->references('id')->on('student_activity_attempts')
                ->onDelete('cascade');

            $table->foreign('question_id')
                ->references('id')->on('questions')
                ->onDelete('cascade');

            $table->foreign('selected_option_id')
                ->references('id')->on('question_options')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_question_attempts');
    }
};
