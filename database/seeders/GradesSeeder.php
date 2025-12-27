<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GradesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $grades = [
            ['id' => 1, 'name' => '1°', 'description' => 'Primero de primaria'],
            ['id' => 2, 'name' => '2°', 'description' => 'Segundo de primaria'],
            ['id' => 3, 'name' => '3°', 'description' => 'Tercero de primaria'],
        ];

        foreach ($grades as $g) {
            DB::table('grades')->updateOrInsert(
                ['id' => $g['id']],
                array_merge($g, ['updated_at' => now(), 'created_at' => now()])
            );
        }
    }
}
