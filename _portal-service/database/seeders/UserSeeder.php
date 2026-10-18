<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'asd@asd.asd'],
            [
                'name' => 'Test User',
                'password' => Hash::make('asdasd'),
                'access_type' => 1, // Super Admin
                'is_active' => 1,
            ]
        );
    }
}
