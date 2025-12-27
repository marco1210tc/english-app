<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\FAcades\DB;

class ClassroomsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         $teacher1 = DB::table('users')->where('email', 'teacher1@englishapp.test')->value('id');
        $teacher2 = DB::table('users')->where('email', 'teacher2@englishapp.test')->value('id');
        $teacher3 = DB::table('users')->where('email', 'teacher3@englishapp.test')->value('id');

        $classrooms = [
            ['grade_id' => 1, 'teacher_id' => $teacher1, 'name' => '1° A', 'class_code' => '1A'],
            ['grade_id' => 2, 'teacher_id' => $teacher2, 'name' => '2° A', 'class_code' => '2A'],
            ['grade_id' => 3, 'teacher_id' => $teacher3, 'name' => '3° A', 'class_code' => '3A'],
        ];

        foreach ($classrooms as $c) {
            DB::table('classrooms')->updateOrInsert(
                ['name' => $c['name']],
                array_merge($c, ['updated_at' => now(), 'created_at' => now()])
            );
        }
    }
}
