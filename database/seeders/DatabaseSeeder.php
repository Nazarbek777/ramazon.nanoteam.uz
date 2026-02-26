<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Default admin user
        User::firstOrCreate(
            ['email' => 'admin@test.com'],
            [
                'name' => 'Admin',
                'email' => 'admin@test.com',
                'password' => bcrypt('admin123'),
                'role' => 'admin',
            ]
        );

        // Seed all subjects, quizzes and questions
        $this->call([
            SubjectsSeeder::class,
            DemoSeeder::class,
        ]);
    }
}
