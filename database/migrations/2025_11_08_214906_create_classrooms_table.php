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
        Schema::create('classrooms', function (Blueprint $table) {
            $table->id();
            // Relación con el maestro (User)
            $table->foreignId('teacher_id')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null'); // Si se borra el maestro, el aula queda "sin maestro"
            $table->string('name'); // Ej. "Grado 1 - A"
            $table->integer('grade_level'); // 1, 2, o 3
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('classrooms');
    }
};
