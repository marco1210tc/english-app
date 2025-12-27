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
        Schema::create('test_result_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_result_id')
                ->constrained('test_results')
                ->cascadeOnDelete();

            $table->foreignId('item_type_id')
                ->constrained('item_types')
                ->restrictOnDelete();

            $table->unsignedBigInteger('ref_id');

            $table->boolean('is_correct')->default(false);
            $table->json('response_json')->nullable();
            $table->integer('time_spent_seconds')->default(0);

            $table->timestamps();

            $table->index(['item_type_id', 'ref_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('test_result_items');
    }
};
