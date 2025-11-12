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
        Schema::create('classroom_user', function (Blueprint $table) {
            // Clave foránea al estudiante (User)
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade'); // Si se borra el estudiante, se elimina de la clase

            // Clave foránea al aula (Classroom)
            $table->foreignId('classroom_id')
                ->constrained('classrooms')
                ->onDelete('cascade'); // Si se borra el aula, se van los estudiantes

            // Definimos la clave primaria compuesta
            $table->primary(['user_id', 'classroom_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('classroom_user');
    }
};
