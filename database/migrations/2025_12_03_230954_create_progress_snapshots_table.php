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
        Schema::create('progress_snapshots', function (Blueprint $table) {
            // Protagonista principal: el estudiante
            $table->foreignId('student_id')
                ->constrained()
                ->cascadeOnDelete();

            // NUEVO respecto al ER original: relacionar directamente con un aula (opcional)
            $table->foreignId('classroom_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            // Contexto de aprendizaje (opcionales)
            $table->foreignId('module_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('lesson_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            // Momento en el que se toma el snapshot
            $table->timestamp('taken_at')->useCurrent();

            // JSON flexible con los datos de progreso
            // ejemplo: { "completed_activities": 10, "avg_score": 78, "stars": 25 }
            $table->json('data_json')->nullable();

            $table->timestamps();

            // Índices útiles para consultas típicas
            $table->index(['student_id', 'module_id']);
            $table->index(['student_id', 'lesson_id']);
            $table->index(['classroom_id', 'taken_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('progress_snapshots');
    }
};
