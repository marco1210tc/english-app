<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TestsSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = DB::table('users')->where('role','admin')->value('id');

        foreach ([1,2,3] as $gradeId) {
            DB::table('tests')->updateOrInsert(
                ['grade_id' => $gradeId, 'type' => 'pre'],
                ['title' => "Pre-test Grado {$gradeId}", 'created_by' => $adminId, 'created_at' => now(), 'updated_at' => now()]
            );

            DB::table('tests')->updateOrInsert(
                ['grade_id' => $gradeId, 'type' => 'post'],
                ['title' => "Post-test Grado {$gradeId}", 'created_by' => $adminId, 'created_at' => now(), 'updated_at' => now()]
            );
        }
    }
}
