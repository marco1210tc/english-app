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
        Schema::create('test_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_id')
                ->constrained('tests')
                ->cascadeOnDelete();

            $table->foreignId('item_type_id')
                ->constrained('item_types')
                ->restrictOnDelete();

            // ID del recurso evaluado
            $table->unsignedBigInteger('ref_id');

            $table->integer('order_index')->default(0);
            $table->timestamps();

            // Índice compuesto para queries rápidos
            $table->index(['item_type', 'ref_id']);
            // evita duplicar mismo ítem en el mismo test
            $table->unique(['test_id', 'item_type_id', 'ref_id']); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('test_items');
    }
};
