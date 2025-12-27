<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1) Agregar columna item_type_id (nullable al inicio para poder backfillear)
        Schema::table('activities', function (Blueprint $table) {
            $table->foreignId('item_type_id')
                ->nullable()
                ->after('lesson_id')
                ->constrained('item_types')
                ->restrictOnDelete();
        });

        // 2) Backfill: activity_type (enum) -> item_types.key -> item_type_id
        // Asegúrate de que item_types tenga keys iguales al enum.
        DB::statement("
            UPDATE activities a
            JOIN item_types it ON it.`key` = a.activity_type
            SET a.item_type_id = it.id
            WHERE a.item_type_id IS NULL
        ");

        // 3) Hacer item_type_id NOT NULL
        Schema::table('activities', function (Blueprint $table) {
            $table->foreignId('item_type_id')->nullable(false)->change();
        });

        // 4) Eliminar la columna activity_type (enum)
        // OJO: esto requiere doctrine/dbal para dropColumn en algunos entornos.
        Schema::table('activities', function (Blueprint $table) {
            $table->dropColumn('activity_type');
        });
    }

    public function down(): void
    {
        // 1) Volver a crear activity_type (enum) - mismo set que tenías
        Schema::table('activities', function (Blueprint $table) {
            $table->enum('activity_type', [
                'multiple_choice',
                'true_false',
                'matching',
                'listening',
                'ordering',
                'drag_drop',
                'memory_cards',
            ])->default('multiple_choice')->after('lesson_id');
        });

        // 2) Backfill inverso: item_types.key -> activity_type
        DB::statement("
            UPDATE activities a
            JOIN item_types it ON it.id = a.item_type_id
            SET a.activity_type = it.`key`
        ");

        // 3) Drop FK y columna item_type_id
        Schema::table('activities', function (Blueprint $table) {
            $table->dropConstrainedForeignId('item_type_id');
        });
    }
};
