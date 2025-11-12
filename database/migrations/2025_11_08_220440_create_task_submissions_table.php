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
        Schema::create('task_submissions', function (Blueprint $table) {
            $table->id();
            // Qué tarea
            $table->foreignId('task_id')
                ->constrained('tasks')
                ->onDelete('cascade');

            // Qué estudiante
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade');

            $table->unsignedInteger('score');
            $table->enum('status', ['pending', 'completed'])->default('completed');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            // Evitar entregas duplicadas
            $table->unique(['task_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_submissions');
    }
};
