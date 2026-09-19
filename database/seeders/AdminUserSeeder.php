<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Create default admin user with Pemilik role.
     */
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@apotek.com'],
            [
                'name' => 'Administrator Apotek',
                'password' => Hash::make('password'),
                'phone' => '081234567890',
                'is_active' => true,
            ]
        );

        $admin->assignRole('Pemilik');
    }
}
