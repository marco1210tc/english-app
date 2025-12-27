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
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('activity_id');
            $table->unsignedBigInteger('vocabulary_id');
            $table->text('prompt')->nullable();
            $table->string('image_path')->nullable();
            $table->string('audio_path')->nullable();
            $table->unsignedInteger('order_index')->default(0);
            $table->timestamps();

            $table->foreign('activity_id')
                ->references('id')->on('activities')
                ->onDelete('cascade');

                $table->foreign('vocabulary_id')
                ->references('id')->on('vocabulary')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
