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
        Schema::create('modules', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('grade_id');
            $table->string('title', 100);
            $table->text('description')->nullable();
            $table->unsignedInteger('order_index')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('grade_id')
                ->references('id')->on('grades')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('modules');
    }
};
