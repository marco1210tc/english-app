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
        Schema::create('activities', function (Blueprint $table) {
            $table->id();

            $table->foreignId('topic_id')
                ->constrained('topics')
                ->onDelete('cascade');

            $table->string('name'); // Ej. "Juego de Memoria: Animales"
            $table->string('description')->nullable();

            // --- LA PARTE CLAVE ---
            // El 'tipo' de juego que el front-end debe renderizar
            $table->string('type');

            // El 'contenido' del juego, almacenado como JSON
            $table->json('content');
            // -------------------------

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
