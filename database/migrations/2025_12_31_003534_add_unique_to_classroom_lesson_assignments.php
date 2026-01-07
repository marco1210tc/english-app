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
        Schema::table('classroom_lesson_assignments', function (Blueprint $table) {
            $table->unique(['classroom_id', 'lesson_id'], 'cla_classroom_lesson_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('classroom_lesson_assignments', function (Blueprint $table) {
            $table->dropUnique('cla_classroom_lesson_unique');
        });
    }
};
