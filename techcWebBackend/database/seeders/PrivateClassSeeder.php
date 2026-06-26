<?php

namespace Database\Seeders;

use App\Models\Package;
use App\Models\School;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PrivateClassSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | ADMIN ACCOUNT
        |--------------------------------------------------------------------------
        | Biar setelah database kosong, admin tetap bisa login.
        */
        User::updateOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Adam Rizki',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'phone' => '08123456789',
                'bio' => 'TECH-C Robotic & Coding Manager',
                'country' => 'Indonesia',
                'city' => 'Cilegon',
                'postal_code' => '42453',
                'tax_id' => 'TECHC-ADMIN',
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | SCHOOL PRIVATE CLASS
        |--------------------------------------------------------------------------
        */
        $school = School::updateOrCreate(
            ['nama' => 'Private Class TECH-C'],
            [
                'slug' => 'private-class-tech-c',
                'kode' => 'PRIVATE',
                'jenjang' => 'Private',
                'alamat' => 'TECH-C Robotic & Coding',
                'kota' => 'Cilegon',
                'provinsi' => 'Banten',
                'pic_name' => 'Admin TECH-C',
                'pic_phone' => '08123456789',
                'email' => 'admin@tech-c.my.id',
                'status' => 'Aktif',
                'catatan' => 'Kategori khusus siswa private class TECH-C.',
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | PACKAGE PRIVATE CLASS
        |--------------------------------------------------------------------------
        */
        $package = Package::updateOrCreate(
            ['nama' => 'Private Class Robotic & Coding'],
            [
                'kategori' => 'Private Class',
                'jumlah_pertemuan' => 4,
                'harga_per_pertemuan' => 150000,
                'harga_paket' => 600000,
                'deskripsi' => 'Paket khusus siswa private class TECH-C.',
                'benefits' => json_encode([
                    'Kelas private',
                    'Materi robotic dan coding',
                    'Progress belajar personal',
                    'Project siswa',
                    'Dashboard siswa',
                ]),
                'status' => 'Aktif',
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | STUDENT LIST
        |--------------------------------------------------------------------------
        */
        $students = [
            'Steven',
            'Ludwig',
            'Abi',
            'Rafa',
            'Charles',
            'Ulul',
            'Biyon',
            'Glenn',
            'Rayka',
            'Aryan',
            'Adma',
            'Shaka',
            'Kenzie',
            'Kaisar',
            'Syahmi',
            'Lenena',
            'Barra',
            'Zharif',
            'Ladina',
            'Hilya',
            'Zayn',
            'Rayhan',
            'Azka',
            'Rafka',
            'Hanin',
            'Gemintang',
            'Ganesha',
            'Saka',
            'Amsyar',
            'Insan',
            'Rasya',
            'Farhan',
            'Darel',
            'Malka',
        ];

        $accounts = [];

        foreach ($students as $index => $name) {
            $slug = Str::slug($name, '.');

            /*
            |--------------------------------------------------------------------------
            | Email & Password
            |--------------------------------------------------------------------------
            | Email dibuat otomatis.
            | Password random, lalu dicatat ke JSON.
            */
            $email = $slug . '.private@techc.local';
            $plainPassword = 'TC-' . strtoupper(Str::random(8));

            $user = User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'password' => Hash::make($plainPassword),
                    'role' => 'siswa',
                    'phone' => null,
                    'bio' => 'Siswa Private Class TECH-C',
                    'country' => 'Indonesia',
                    'city' => 'Cilegon',
                ]
            );

            $student = Student::updateOrCreate(
                ['name' => $name],
                [
                    'user_id' => $user->id,
                    'school_id' => $school->id,
                    'package_id' => $package->id,
                    'nis' => 'PVC-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                    'photo' => null,
                    'kelas' => 'Private Class',
                    'kategori_level' => 'Private Class',
                    'jenis_kelamin' => null,
                    'tanggal_lahir' => null,
                    'parent_name' => null,
                    'parent_phone' => null,
                    'parent_email' => null,
                    'student_type' => 'private',
                    'progress_belajar' => 0,
                    'tagihan' => 0,
                    'jadwal' => json_encode([
                        [
                            'kategori' => 'Private Class',
                            'materi' => 'Robotic & Coding',
                            'status' => 'Belum dijadwalkan',
                        ],
                    ]),
                    'pengumuman' => 'Selamat datang di Private Class TECH-C.',
                    'catatan' => 'Data siswa private class.',
                    'status' => 'Aktif',
                ]
            );

            $accounts[] = [
                'no' => $index + 1,
                'student_id' => $student->id,
                'user_id' => $user->id,
                'name' => $name,
                'email' => $email,
                'password' => $plainPassword,
                'role' => 'siswa',
                'kategori' => 'Private Class',
                'kelas' => 'Private Class',
                'sekolah' => 'Private Class TECH-C',
                'nis' => 'PVC-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT),
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | SAVE ACCOUNT JSON
        |--------------------------------------------------------------------------
        */
        $jsonPath = storage_path('app/private_class_accounts.json');

        File::put(
            $jsonPath,
            json_encode($accounts, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );

        $this->command?->info('Private class students created: ' . count($accounts));
        $this->command?->info('Account JSON saved to: ' . $jsonPath);
    }
}