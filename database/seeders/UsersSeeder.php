<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Admin EnglishApp',
                'email' => 'admin@englishapp.test',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'status' => 'active',
            ],
            [
                'name' => 'Docente 1',
                'email' => 'teacher1@englishapp.test',
                'password' => Hash::make('password'),
                'role' => 'teacher',
                'status' => 'active',
            ],
            [
                'name' => 'Docente 2',
                'email' => 'teacher2@englishapp.test',
                'password' => Hash::make('password'),
                'role' => 'teacher',
                'status' => 'active',
            ],
            [
                'name' => 'Docente 3',
                'email' => 'teacher3@englishapp.test',
                'password' => Hash::make('password'),
                'role' => 'teacher',
                'status' => 'active',
            ],
        ];

        foreach ($users as $u) {
            DB::table('users')->updateOrInsert(
                ['email' => $u['email']],
                array_merge($u, ['updated_at' => now(), 'created_at' => now()])
            );
        }
    }
}
