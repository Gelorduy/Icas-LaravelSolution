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
        $admin = User::firstOrCreate(
            ['email' => 'admin@icas.local'],
            [
                'name' => 'Administrator',
                'password' => bcrypt('inmate.2025'),
                'role' => 'administrator',
            ],
        );

        // Optionally ensure the role is set even if the user already existed
        if ($admin->role !== 'administrator') {
            $admin->forceFill(['role' => 'administrator'])->save();
        }
    }
}
