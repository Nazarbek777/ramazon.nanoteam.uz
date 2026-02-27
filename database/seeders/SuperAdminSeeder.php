<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@test.com'],
            [
                'name'     => 'Super Admin',
                'email'    => 'admin@test.com',
                'password' => Hash::make('Admin@12345'),
                'role'     => 'super_admin',
            ]
        );

        $this->command->info('✅ Super admin yaratildi: admin@test.com / Admin@12345');
    }
}
