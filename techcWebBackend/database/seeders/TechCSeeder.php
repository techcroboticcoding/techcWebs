<?php

namespace Database\Seeders;

use App\Models\Invoice;
use App\Models\Lesson;
use App\Models\LessonCategory;
use App\Models\Package;
use App\Models\School;
use App\Models\Student;
use App\Models\StudentNotification;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
class TechCSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | USERS
        |--------------------------------------------------------------------------
        */
        $admin = User::updateOrCreate(
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
                'tax_id' => 'IDN123456',
            ]
        );

        $siswaUser = User::updateOrCreate(
            ['email' => 'siswa@gmail.com'],
            [
                'name' => 'Siswa TECH-C',
                'password' => Hash::make('password'),
                'role' => 'siswa',
                'phone' => '081200000001',
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | SCHOOLS
        |--------------------------------------------------------------------------
        */
        $schoolNames = [
            'SD Daar El Latif',
            'SDIT Al-Khairiyah',
            'SDIT BIS',
            'SDIT Al Hanif',
            'MTsN 3 Cilegon',
            'SDIT CIS',
            'SDIT Irsyadul Ibad 2',
            'SDIT Al-Izzah 1',
            'SDIT Al-Izzah 2',
            'MIN 1 Cilegon',
            'SD Peradaban',
            'SMA Al Azhar',
            'SD WR',
            'MTsN 2 Cilegon',
            'SMP CIS',
            'SIS',
            'SMP Al Azhar',
            'SD YPKS 2 Cilegon',
            'SD YPKS 4 Cilegon',
            'SD YPKS 5 Cilegon',
            'TK Bosowa',
            'SD Bosowa',
            'SDIT Bina Insani',
            'SDIT Bina Bangsa',
            'MTsN 1 Kota Serang',
            'SMP Ibnu Syam',
            'SMA Al Bayan',
        ];

        foreach ($schoolNames as $name) {
            School::updateOrCreate(
                ['nama' => $name],
                [
                    'slug' => Str::slug($name),
                    'kode' => strtoupper(substr(Str::slug($name, ''), 0, 8)),
                    'jenjang' => $this->guessJenjang($name),
                    'kota' => str_contains($name, 'Serang') ? 'Serang' : 'Cilegon',
                    'provinsi' => 'Banten',
                    'status' => 'Aktif',
                ]
            );
        }

        /*
        |--------------------------------------------------------------------------
        | PACKAGES
        |--------------------------------------------------------------------------
        */
        $package = Package::updateOrCreate(
            ['nama' => 'Kelas Robotic & Coding'],
            [
                'kategori' => 'Robotic',
                'jumlah_pertemuan' => 4,
                'harga_per_pertemuan' => 150000,
                'harga_paket' => 600000,
                'deskripsi' => 'Paket kelas robotic dan coding reguler TECH-C.',
                'benefits' => json_encode([
                    'Materi robotic dan coding',
                    'Project setiap level',
                    'Laporan progress siswa',
                    'Sertifikat setelah selesai',
                ]),
                'status' => 'Aktif',
            ]
        );

        Package::updateOrCreate(
            ['nama' => 'Kelas AI Engineering'],
            [
                'kategori' => 'AI Engineering',
                'jumlah_pertemuan' => 4,
                'harga_per_pertemuan' => 200000,
                'harga_paket' => 800000,
                'deskripsi' => 'Paket pengenalan AI, Python, Computer Vision, dan automation.',
                'benefits' => json_encode([
                    'Python AI',
                    'Computer vision',
                    'Chatbot',
                    'Final project AI',
                ]),
                'status' => 'Aktif',
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | TEACHERS
        |--------------------------------------------------------------------------
        */
        Teacher::updateOrCreate(
            ['email' => 'adam@gmail.com'],
            [
                'user_id' => $admin->id,
                'name' => 'Adam Rizki',
                'phone' => '08123456789',
                'specialization' => 'Robotic, Arduino, ESP32, AI',
                'skills' => json_encode([
                    'Scratch',
                    'Python',
                    'Arduino',
                    'ESP32',
                    'AI Engineering',
                ]),
                'fee_per_session' => 100000,
                'salary_base' => 0,
                'status' => 'Aktif',
            ]
        );

        Teacher::updateOrCreate(
            ['email' => 'teacher@techc.my.id'],
            [
                'name' => 'Pengajar TECH-C',
                'phone' => '081200000002',
                'specialization' => 'Scratch, PictoBlox, Python',
                'skills' => json_encode([
                    'Scratch',
                    'PictoBlox',
                    'Python',
                ]),
                'fee_per_session' => 85000,
                'salary_base' => 0,
                'status' => 'Aktif',
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | STUDENTS
        |--------------------------------------------------------------------------
        */
        $school = School::where('nama', 'SDIT Al-Khairiyah')->first() ?? School::first();

        $student = Student::updateOrCreate(
            ['name' => 'Ahmad Rizki'],
            [
                'user_id' => $siswaUser->id,
                'school_id' => $school?->id,
                'package_id' => $package->id,
                'kelas' => 'Junior A',
                'kategori_level' => 'Junior A',
                'jenis_kelamin' => 'Laki-laki',
                'parent_name' => 'Orang Tua Ahmad',
                'parent_phone' => '081200000003',
                'student_type' => 'sekolah',
                'progress_belajar' => 45,
                'tagihan' => 600000,
                'jadwal' => json_encode([
                    [
                        'hari' => 'Sabtu',
                        'jam' => '09:00 - 10:30',
                        'materi' => 'Scratch Game',
                    ],
                ]),
                'pengumuman' => 'Selamat datang di dashboard siswa TECH-C.',
                'catatan' => 'Siswa aktif dan mengikuti kelas reguler.',
                'status' => 'Aktif',
            ]
        );

        $dummyStudents = [
            ['name' => 'Siti Aisyah', 'kelas' => 'Junior B', 'kategori_level' => 'Junior B'],
            ['name' => 'Budi Santoso', 'kelas' => 'Senior', 'kategori_level' => 'Senior'],
            ['name' => 'Dewi Lestari', 'kelas' => 'Junior C', 'kategori_level' => 'Junior C'],
            ['name' => 'Rafa Pratama', 'kelas' => 'Junior A', 'kategori_level' => 'Junior A'],
            ['name' => 'Kenzie Ramadhan', 'kelas' => 'Junior B', 'kategori_level' => 'Junior B'],
        ];

        foreach ($dummyStudents as $item) {
            Student::updateOrCreate(
                ['name' => $item['name']],
                [
                    'school_id' => School::inRandomOrder()->first()?->id,
                    'package_id' => $package->id,
                    'kelas' => $item['kelas'],
                    'kategori_level' => $item['kategori_level'],
                    'student_type' => 'sekolah',
                    'progress_belajar' => rand(10, 90),
                    'status' => 'Aktif',
                ]
            );
        }

        /*
        |--------------------------------------------------------------------------
        | LESSON CATEGORIES
        |--------------------------------------------------------------------------
        */
        $categories = [
            ['nama' => 'Scratch Game', 'icon' => '🐱', 'color' => 'blue'],
            ['nama' => 'PictoBlox', 'icon' => '🧩', 'color' => 'cyan'],
            ['nama' => 'Python', 'icon' => '🐍', 'color' => 'green'],
            ['nama' => 'Arduino', 'icon' => '🔌', 'color' => 'orange'],
            ['nama' => 'ESP32', 'icon' => '📡', 'color' => 'violet'],
            ['nama' => 'AI Engineering', 'icon' => '🤖', 'color' => 'purple'],
            ['nama' => 'Final Project', 'icon' => '🏆', 'color' => 'yellow'],
        ];

        foreach ($categories as $index => $cat) {
            LessonCategory::updateOrCreate(
                ['slug' => Str::slug($cat['nama'])],
                [
                    'nama' => $cat['nama'],
                    'icon' => $cat['icon'],
                    'color' => $cat['color'],
                    'sort_order' => $index + 1,
                    'status' => 'Aktif',
                ]
            );
        }

        /*
        |--------------------------------------------------------------------------
        | LESSONS
        |--------------------------------------------------------------------------
        */
        $lessons = [
            [
                'title' => 'Scratch Dasar: Sprite, Motion, dan Event',
                'category' => 'Scratch Game',
                'level' => 'Beginner',
                'duration' => '1 Pertemuan',
                'tools' => ['Scratch', 'Block Coding', 'Sprite'],
                'topics' => ['Sprite', 'Backdrop', 'Motion Block', 'Event', 'Koordinat'],
                'description' => 'Pengenalan Scratch dari awal untuk membuat animasi dan interaksi sederhana.',
                'output' => 'Animasi karakter bergerak dengan event green flag.',
            ],
            [
                'title' => 'Scratch Game: Pong Starter',
                'category' => 'Scratch Game',
                'level' => 'Beginner',
                'duration' => '1 Pertemuan',
                'tools' => ['Scratch', 'Game Logic'],
                'topics' => ['Keyboard', 'Collision', 'Score', 'Game Over'],
                'description' => 'Membuat game Pong sederhana dengan paddle, bola, skor, dan kondisi kalah.',
                'output' => 'Game Pong sederhana.',
            ],
            [
                'title' => 'Scratch Game: Maze Runner',
                'category' => 'Scratch Game',
                'level' => 'Beginner',
                'duration' => '1 Pertemuan',
                'tools' => ['Scratch', 'Maze', 'Game Design'],
                'topics' => ['Touching Color', 'Start Finish', 'Restart', 'Level Design'],
                'description' => 'Membuat game labirin dengan garis pembatas, finish, dan restart.',
                'output' => 'Game maze sederhana.',
            ],
            [
                'title' => 'PictoBlox AI: Face Detection',
                'category' => 'PictoBlox',
                'level' => 'Intermediate',
                'duration' => '1 Pertemuan',
                'tools' => ['PictoBlox', 'AI', 'Camera'],
                'topics' => ['Camera', 'Face Detection', 'AI Extension'],
                'description' => 'Mengenalkan AI visual memakai PictoBlox.',
                'output' => 'Program deteksi wajah sederhana.',
            ],
            [
                'title' => 'Python Dasar: Print, Variable, dan Input',
                'category' => 'Python',
                'level' => 'Beginner',
                'duration' => '1 Pertemuan',
                'tools' => ['Python', 'VS Code'],
                'topics' => ['Print', 'Variable', 'Input', 'Tipe Data'],
                'description' => 'Dasar pemrograman Python untuk siswa.',
                'output' => 'Program biodata dan kalkulator sederhana.',
            ],
            [
                'title' => 'Python Game: Pygame Catcher',
                'category' => 'Python',
                'level' => 'Intermediate',
                'duration' => '2 Pertemuan',
                'tools' => ['Python', 'Pygame'],
                'topics' => ['Game Loop', 'Event', 'Sprite', 'Collision'],
                'description' => 'Membuat game menangkap objek dengan Pygame.',
                'output' => 'Game catcher sederhana.',
            ],
            [
                'title' => 'Arduino Dasar: LED Blink',
                'category' => 'Arduino',
                'level' => 'Beginner',
                'duration' => '1 Pertemuan',
                'tools' => ['Arduino UNO', 'LED', 'Resistor'],
                'topics' => ['Arduino IDE', 'Digital Output', 'Delay'],
                'description' => 'Pengenalan Arduino, pin digital, breadboard, LED, dan resistor.',
                'output' => 'LED berkedip.',
            ],
            [
                'title' => 'Arduino Ultrasonic Sensor',
                'category' => 'Arduino',
                'level' => 'Intermediate',
                'duration' => '1 Pertemuan',
                'tools' => ['Arduino', 'HC-SR04'],
                'topics' => ['Trigger Echo', 'Distance', 'Serial Monitor'],
                'description' => 'Membaca jarak menggunakan sensor ultrasonic.',
                'output' => 'Alat ukur jarak sederhana.',
            ],
            [
                'title' => 'ESP32 WiFi Web Server',
                'category' => 'ESP32',
                'level' => 'Intermediate',
                'duration' => '1 Pertemuan',
                'tools' => ['ESP32', 'WiFi', 'Web Server'],
                'topics' => ['WiFi', 'IP Address', 'HTML Response'],
                'description' => 'Membuat ESP32 menjadi web server sederhana.',
                'output' => 'Website lokal dari ESP32.',
            ],
            [
                'title' => 'ESP32 Robot Car L298N',
                'category' => 'ESP32',
                'level' => 'Advanced',
                'duration' => '2 Pertemuan',
                'tools' => ['ESP32', 'L298N', 'DC Motor'],
                'topics' => ['Motor Driver', 'Web Control', 'PWM'],
                'description' => 'Membuat robot mobil dengan ESP32 dan L298N.',
                'output' => 'Robot car web control.',
            ],
            [
                'title' => 'AI Vision: Object Detection YOLO',
                'category' => 'AI Engineering',
                'level' => 'Advanced',
                'duration' => '2 Pertemuan',
                'tools' => ['Python', 'YOLO', 'OpenCV'],
                'topics' => ['Object Detection', 'Bounding Box', 'Confidence'],
                'description' => 'Pengenalan computer vision dan object detection realtime.',
                'output' => 'Program deteksi objek realtime.',
            ],
            [
                'title' => 'Final Capstone: Smart AI Robot Project',
                'category' => 'Final Project',
                'level' => 'Project',
                'duration' => '3 Pertemuan',
                'tools' => ['Python', 'Arduino', 'ESP32', 'AI'],
                'topics' => ['Prototype', 'Testing', 'Presentation'],
                'description' => 'Project akhir gabungan seluruh track pembelajaran.',
                'output' => 'Prototype AI, robot, IoT, atau game.',
            ],
        ];

        foreach ($lessons as $index => $lesson) {
            $category = LessonCategory::where('nama', $lesson['category'])->first();

            Lesson::updateOrCreate(
                ['slug' => Str::slug($lesson['title'])],
                [
                    'lesson_category_id' => $category?->id,
                    'title' => $lesson['title'],
                    'category' => $lesson['category'],
                    'level' => $lesson['level'],
                    'duration' => $lesson['duration'],
                    'tools' => json_encode($lesson['tools']),
                    'topics' => json_encode($lesson['topics']),
                    'description' => $lesson['description'],
                    'output' => $lesson['output'],
                    'sort_order' => $index + 1,
                    'status' => 'Aktif',
                ]
            );
        }

        /*
        |--------------------------------------------------------------------------
        | INVOICE SAMPLE
        |--------------------------------------------------------------------------
        */
        $invoice = Invoice::updateOrCreate(
            ['invoice_no' => 'INV-TECHC-20260511-1001'],
            [
                'student_id' => $student->id,
                'school_id' => $student->school_id,
                'package_id' => $student->package_id,
                'date' => '2026-05-11',
                'due_date' => '2026-05-18',
                'student_name' => $student->name,
                'student_class' => $student->kelas,
                'student_school' => $student->school?->nama,
                'package_name' => 'Kelas Robotic & Coding',
                'meeting_count' => 4,
                'price_per_meeting' => 150000,
                'main_total' => 600000,
                'extra_fee' => 0,
                'subtotal' => 600000,
                'total' => 600000,
                'extra_items' => json_encode([]),
                'note' => 'Pembayaran dapat dilakukan melalui rekening yang tercantum pada invoice.',
                'status' => 'Belum Dibayar',
            ]
        );

        StudentNotification::updateOrCreate(
            [
                'student_id' => $student->id,
                'title' => 'Tagihan Baru',
            ],
            [
                'type' => 'invoice',
                'message' => 'Invoice baru ' . $invoice->invoice_no . ' dengan total Rp ' . number_format($invoice->total, 0, ',', '.'),
                'url' => 'tagihan-siswa.html',
                'is_read' => false,
            ]
        );
    }

    private function guessJenjang(string $name): string
    {
        if (str_starts_with($name, 'TK')) return 'TK';
        if (str_starts_with($name, 'SD')) return 'SD';
        if (str_starts_with($name, 'MIN')) return 'MI';
        if (str_starts_with($name, 'MTs')) return 'MTs';
        if (str_starts_with($name, 'SMP')) return 'SMP';
        if (str_starts_with($name, 'SMA')) return 'SMA';

        return 'Sekolah';
    }
}
