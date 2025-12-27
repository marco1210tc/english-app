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
            $table->unsignedBigInteger('lesson_id');
            $table->string('title', 100);
            $table->text('description')->nullable();
            $table->foreignId('item_type_id')
                ->constrained('item_types')
                ->restrictOnDelete();
            $table->enum('difficulty', ['easy', 'medium', 'hard'])->default('easy');
            $table->unsignedInteger('max_score')->default(0);
            $table->unsignedInteger('order_index')->default(0);
            $table->boolean('is_active')->default(true);
            $table->json('config_json')->nullable();
            $table->timestamps();

            $table->foreign('lesson_id')
                ->references('id')->on('lessons')
                ->onDelete('cascade');
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
