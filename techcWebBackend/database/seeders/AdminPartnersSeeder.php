<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminPartnersSeeder extends Seeder
{
    public function run(): void
    {
        $admins = [
            [
                'name' => 'Adam Rizki Hidayatullah',
                'email' => 'adam.admin@techc.local',
                'password' => 'adam123',
                'role' => 'admin',
            ],
            [
                'name' => 'Hashina Qiamu Mumtaziah',
                'email' => 'hashina.admin@techc.local',
                'password' => 'hashina123',
                'role' => 'admin',
            ],
            [
                'name' => 'Rizki Rahayu Pratama',
                'email' => 'rizki.admin@techc.local',
                'password' => 'rizki123',
                'role' => 'admin',
            ],
            [
                'name' => 'Akbar Marzuqi',
                'email' => 'akbar.admin@techc.local',
                'password' => 'ayaka123',
                'role' => 'admin',
            ],
            [
                'name' => 'Dinda Sri Rejeki',
                'email' => 'dinda.admin@techc.local',
                'password' => 'dinda123',
                'role' => 'admin',
            ],
        ];

        foreach ($admins as $admin) {
            User::updateOrCreate(
                [
                    'email' => $admin['email'],
                ],
                [
                    'name' => $admin['name'],
                    'password' => Hash::make($admin['password']),
                    'role' => $admin['role'],
                ]
            );
        }
    }
}