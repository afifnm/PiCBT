<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['username' => 'admin'],
            [
                'name'      => 'Administrator',
                'password'  => Hash::make('admin123'),
                'role'      => 'admin',
                'is_active' => true,
            ]
        );

        User::firstOrCreate(
            ['username' => 'guru_ipa'],
            [
                'name'      => 'Budi Santoso, S.Pd',
                'password'  => Hash::make('guru123'),
                'role'      => 'guru',
                'is_active' => true,
            ]
        );

        User::firstOrCreate(
            ['username' => 'guru_mat'],
            [
                'name'      => 'Siti Rahayu, S.Pd',
                'password'  => Hash::make('guru123'),
                'role'      => 'guru',
                'is_active' => true,
            ]
        );
    }
}
