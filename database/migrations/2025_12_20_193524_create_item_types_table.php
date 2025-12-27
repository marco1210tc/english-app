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
        Schema::create('item_types', function (Blueprint $table) {
            $table->id();
            // Clave técnica estable
            // ej: vocabulary, quiz_question, match, order_letters
            $table->string('key')->unique();

            // Nombre legible
            $table->string('name');

            // Descripción funcional
            $table->string('description')->nullable();

            // Para controlar disponibilidad sin borrar
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_types');
    }
};
