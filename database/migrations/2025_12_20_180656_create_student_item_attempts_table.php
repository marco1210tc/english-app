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
        Schema::create('student_item_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_attempt_id')
                ->constrained('student_activity_attempts')
                ->cascadeOnDelete();

            // Identificador lógico del ítem dentro del juego
            // Ej: "pair_1", "letter_3", "img_cat"
            $table->string('item_key');

            // Resultado del ítem
            $table->boolean('is_correct')->default(false);

            // Número de intentos sobre ese ítem
            $table->integer('attempts')->default(1);

            // Tracking obligatorio MVP
            $table->integer('time_spent_seconds')->default(0);
            $table->integer('hints_used')->default(0);

            // Respuesta flexible (pares, orden, posiciones, etc.)
            $table->json('response_json')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_item_attempts');
    }
};
