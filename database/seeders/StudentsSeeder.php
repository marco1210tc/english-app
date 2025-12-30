<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;


class StudentsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('es_ES');        
        $pinHash = Hash::make('1234');

        $classrooms = DB::table('classrooms')->get(['id', 'name']);
        foreach ($classrooms as $classroom) {
            for ($i = 1; $i <= 6; $i++) {
                $code = strtoupper(str_replace(['°', ' '], '', $classroom->name)) . str_pad((string)$i, 2, '0', STR_PAD_LEFT);
                // Ej: "1A01", "1A02"...

                $avatarUrl = "https://api.dicebear.com/7.x/fun-emoji/svg?seed={$code}";

                DB::table('students')->updateOrInsert(
                    ['code' => $code],
                    [
                        'first_name' => $faker->firstName,
                        'last_name' => $faker->lastName,
                        'pin_hash' => $pinHash,
                        'classroom_id' => $classroom->id,
                        'avatar' => $avatarUrl,
                        'status' => 'active',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }
    }
}
