<?php

use App\Models\Announcement;
use App\Models\Attendance;
use App\Models\Certificate;
use App\Models\ChatMessage;
use App\Models\ChatThread;
use App\Models\Event;
use App\Models\FinancialTransaction;
use App\Models\HelpTicket;
use App\Models\InventoryItem;
use App\Models\Invoice;
use App\Models\Lesson;
use App\Models\Material;
use App\Models\Package;
use App\Models\Payment;
use App\Models\Payroll;
use App\Models\Place;
use App\Models\ProgressRecord;
use App\Models\ReimbursementRequest;
use App\Models\Report;
use App\Models\Schedule;
use App\Models\School;
use App\Models\Student;
use App\Models\StudentNotification;
use App\Models\StudentProject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use App\Models\StudentDocumentation;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
/*
|--------------------------------------------------------------------------
| ATTENDANCE / ABSENSI
|--------------------------------------------------------------------------
*/
$storeAttendance = function (array $payload) {
    $timezone = 'Asia/Jakarta';

    $dateRaw = $payload['attendance_date']
        ?? $payload['date']
        ?? $payload['tanggal']
        ?? $payload['Tanggal']
        ?? now($timezone)->toDateString();

    $timeRaw = $payload['attendance_time']
        ?? $payload['time']
        ?? $payload['jam']
        ?? $payload['Jam']
        ?? now($timezone)->format('H:i:s');

    $memberId = $payload['member_id']
        ?? $payload['id']
        ?? $payload['ID']
        ?? $payload['uid']
        ?? $payload['UID']
        ?? null;

    $name = $payload['name']
        ?? $payload['nama']
        ?? $payload['Nama']
        ?? null;

    $status = $payload['status']
        ?? $payload['Status']
        ?? 'HADIR';

    $source = $payload['source']
        ?? $payload['Source']
        ?? 'web';

    $deviceName = $payload['device_name']
        ?? $payload['device']
        ?? $payload['Device']
        ?? null;

    $note = $payload['note']
        ?? $payload['catatan']
        ?? $payload['Catatan']
        ?? null;

    if (!$name) {
        throw \Illuminate\Validation\ValidationException::withMessages([
            'name' => 'Nama wajib diisi.',
        ]);
    }

    $date = Carbon::parse($dateRaw, $timezone)->toDateString();
    $time = Carbon::parse($timeRaw, $timezone)->format('H:i:s');

    $status = strtoupper(trim($status));
    $name = trim($name);
    $memberId = $memberId !== null ? trim((string) $memberId) : null;

    $uniqueHash = md5($date . '|' . $time . '|' . $memberId . '|' . strtolower($name) . '|' . $status);

    $now = now();

    $data = [];

    // Kolom baru
    if (Schema::hasColumn('attendances', 'attendance_date')) {
        $data['attendance_date'] = $date;
    }

    if (Schema::hasColumn('attendances', 'attendance_time')) {
        $data['attendance_time'] = $time;
    }

    if (Schema::hasColumn('attendances', 'member_id')) {
        $data['member_id'] = $memberId;
    }

    if (Schema::hasColumn('attendances', 'name')) {
        $data['name'] = $name;
    }

    if (Schema::hasColumn('attendances', 'status')) {
        $data['status'] = $status;
    }

    if (Schema::hasColumn('attendances', 'source')) {
        $data['source'] = $source;
    }

    if (Schema::hasColumn('attendances', 'device_name')) {
        $data['device_name'] = $deviceName;
    }

    if (Schema::hasColumn('attendances', 'note')) {
        $data['note'] = $note;
    }

    if (Schema::hasColumn('attendances', 'unique_hash')) {
        $data['unique_hash'] = $uniqueHash;
    }

    if (Schema::hasColumn('attendances', 'raw_payload')) {
        $data['raw_payload'] = json_encode($payload);
    }

    // Kolom lama dari tabel sebelumnya
    if (Schema::hasColumn('attendances', 'tanggal')) {
        $data['tanggal'] = $date;
    }

    if (Schema::hasColumn('attendances', 'jam')) {
        $data['jam'] = $time;
    }

    if (Schema::hasColumn('attendances', 'nama')) {
        $data['nama'] = $name;
    }

    // Kalau ada kolom legacy untuk ID selain primary id
    if (Schema::hasColumn('attendances', 'uid')) {
        $data['uid'] = $memberId;
    }

    if (Schema::hasColumn('attendances', 'rfid_id')) {
        $data['rfid_id'] = $memberId;
    }

    if (Schema::hasColumn('attendances', 'created_at')) {
        $data['created_at'] = $now;
    }

    if (Schema::hasColumn('attendances', 'updated_at')) {
        $data['updated_at'] = $now;
    }

    $exists = null;

    if (Schema::hasColumn('attendances', 'unique_hash')) {
        $exists = DB::table('attendances')
            ->where('unique_hash', $uniqueHash)
            ->first();
    }

    if ($exists) {
        unset($data['created_at']);

        DB::table('attendances')
            ->where('unique_hash', $uniqueHash)
            ->update($data);

        return DB::table('attendances')
            ->where('unique_hash', $uniqueHash)
            ->first();
    }

    $id = DB::table('attendances')->insertGetId($data);

    return DB::table('attendances')->where('id', $id)->first();
};
Route::get('/attendances', function (Request $request) {
    try {
        $dateColumn = Schema::hasColumn('attendances', 'attendance_date')
            ? 'attendance_date'
            : 'tanggal';

        $timeColumn = Schema::hasColumn('attendances', 'attendance_time')
            ? 'attendance_time'
            : 'jam';

        $nameColumn = Schema::hasColumn('attendances', 'name')
            ? 'name'
            : 'nama';

        $idColumn = Schema::hasColumn('attendances', 'member_id')
            ? 'member_id'
            : null;

        $query = DB::table('attendances')
            ->orderByDesc($dateColumn)
            ->orderByDesc($timeColumn)
            ->orderByDesc('id');

        if ($request->filled('date')) {
            $query->whereDate($dateColumn, $request->date);
        }

        if ($request->filled('status') && Schema::hasColumn('attendances', 'status')) {
            $query->where('status', strtoupper($request->status));
        }

        if ($request->filled('source') && Schema::hasColumn('attendances', 'source')) {
            $query->where('source', $request->source);
        }

        if ($request->filled('member_id') && $idColumn) {
            $query->where($idColumn, $request->member_id);
        }

        if ($request->filled('name')) {
            $query->where($nameColumn, 'like', '%' . $request->name . '%');
        }

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search, $nameColumn, $idColumn) {
                $q->where($nameColumn, 'like', "%{$search}%");

                if ($idColumn) {
                    $q->orWhere($idColumn, 'like', "%{$search}%");
                }

                if (Schema::hasColumn('attendances', 'status')) {
                    $q->orWhere('status', 'like', "%{$search}%");
                }

                if (Schema::hasColumn('attendances', 'source')) {
                    $q->orWhere('source', 'like', "%{$search}%");
                }
            });
        }

        $limit = (int) $request->get('limit', 500);
        $limit = max(1, min($limit, 1000));

        $rows = $query->limit($limit)->get();

        $data = $rows->map(function ($row) use ($dateColumn, $timeColumn, $nameColumn, $idColumn) {
            return [
                'id' => $row->id ?? null,
                'attendance_date' => $row->{$dateColumn} ?? null,
                'attendance_time' => $row->{$timeColumn} ?? null,
                'member_id' => $idColumn ? ($row->{$idColumn} ?? null) : null,
                'name' => $row->{$nameColumn} ?? null,
                'status' => $row->status ?? 'HADIR',
                'source' => $row->source ?? 'web',
                'device_name' => $row->device_name ?? null,
                'note' => $row->note ?? null,
                'created_at' => $row->created_at ?? null,
                'updated_at' => $row->updated_at ?? null,
            ];
        });

        $today = now('Asia/Jakarta')->toDateString();

        $todayCount = DB::table('attendances')
            ->whereDate($dateColumn, $today)
            ->count();

        $hadirToday = Schema::hasColumn('attendances', 'status')
            ? DB::table('attendances')
                ->whereDate($dateColumn, $today)
                ->where('status', 'HADIR')
                ->count()
            : $todayCount;

        $lastUpdate = Schema::hasColumn('attendances', 'updated_at')
            ? DB::table('attendances')->latest('updated_at')->value('updated_at')
            : null;

        return response()->json([
            'message' => 'Data absensi berhasil diambil.',
            'stats' => [
                'total' => DB::table('attendances')->count(),
                'today' => $todayCount,
                'hadir_today' => $hadirToday,
                'last_update' => $lastUpdate,
            ],
            'data' => $data,
        ]);

    } catch (\Throwable $e) {
        return response()->json([
            'message' => 'Attendance API error',
            'error' => $e->getMessage(),
            'line' => $e->getLine(),
            'file' => basename($e->getFile()),
        ], 500);
    }
});

Route::post('/attendances', function (Request $request) use ($storeAttendance) {
    try {
        $attendance = $storeAttendance($request->all());

        return response()->json([
            'message' => 'Absensi berhasil disimpan.',
            'data' => $attendance,
        ], 201);

    } catch (\Throwable $e) {
        return response()->json([
            'message' => 'Save attendance error',
            'error' => $e->getMessage(),
            'line' => $e->getLine(),
            'file' => basename($e->getFile()),
        ], 500);
    }
});

/*
| Buat ESP32 yang lebih gampang kirim GET URL
| Contoh:
| /api/attendances/receive?Tanggal=2026-03-27&Jam=08:04:52&ID=1&Nama=Adit&Status=HADIR
*/
Route::get('/attendances/receive', function (Request $request) use ($storeAttendance) {
    try {
        $payload = $request->query();
        $payload['source'] = $payload['source'] ?? 'esp32';

        $attendance = $storeAttendance($payload);

        return response()->json([
            'message' => 'Absensi dari device berhasil diterima.',
            'data' => $attendance,
        ], 201);

    } catch (\Throwable $e) {
        return response()->json([
            'message' => 'Receive attendance error',
            'error' => $e->getMessage(),
            'line' => $e->getLine(),
            'file' => basename($e->getFile()),
        ], 500);
    }
});

/*
| Buat import banyak data dari Google Sheet / Apps Script
*/
Route::post('/attendances/bulk', function (Request $request) use ($storeAttendance) {
    try {
        $rows = $request->input('rows', []);

        if (!is_array($rows)) {
            return response()->json([
                'message' => 'Format rows harus array.',
            ], 422);
        }

        $saved = [];
        $failed = [];

        foreach ($rows as $index => $row) {
            try {
                $row['source'] = $row['source'] ?? 'sheet';
                $saved[] = $storeAttendance($row);
            } catch (\Throwable $e) {
                $failed[] = [
                    'index' => $index,
                    'row' => $row,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return response()->json([
            'message' => 'Import absensi selesai.',
            'saved_count' => count($saved),
            'failed_count' => count($failed),
            'failed' => $failed,
        ]);

    } catch (\Throwable $e) {
        return response()->json([
            'message' => 'Bulk attendance error',
            'error' => $e->getMessage(),
            'line' => $e->getLine(),
            'file' => basename($e->getFile()),
        ], 500);
    }
});

Route::delete('/attendances/{attendance}', function (Attendance $attendance) {
    $attendance->delete();

    return response()->json([
        'message' => 'Absensi berhasil dihapus.',
    ]);
});
/*
|--------------------------------------------------------------------------
| AUTH
|--------------------------------------------------------------------------
*/
Route::post('/login', function (Request $request) {
    $request->validate([
        'email' => 'required',
        'password' => 'required',
    ]);

    $user = User::where('email', $request->email)->first();

    if (!$user || !Hash::check($request->password, $user->password)) {
        return response()->json([
            'message' => 'Email atau password salah',
        ], 401);
    }

    $student = null;

    if (($user->role ?? '') === 'siswa') {
        $student = Student::where('user_id', $user->id)->first();
    }

    return response()->json([
        'token' => 'dummy-token-' . $user->id,
        'role' => $user->role ?? 'admin',
        'name' => $user->name,
        'email' => $user->email,
        'user_id' => $user->id,
        'student_id' => $student?->id,
        'user' => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'student_id' => $student?->id,
        ],
    ]);
});

/*
|--------------------------------------------------------------------------
| PROFILE
|--------------------------------------------------------------------------
*/
Route::get('/profile', function (Request $request) {
    try {
        $user = null;

        // Ambil user dari Authorization: Bearer dummy-token-xx
        $authorization = $request->header('Authorization');

        if ($authorization && str_contains($authorization, 'dummy-token-')) {
            $userId = trim(str_replace('Bearer dummy-token-', '', $authorization));

            if (is_numeric($userId)) {
                $user = User::find((int) $userId);
            }
        }

        // Fallback ambil dari header email
        if (!$user) {
            $email = $request->header('X-User-Email')
                ?? $request->query('email')
                ?? $request->input('email');

            if ($email) {
                $user = User::where('email', $email)->first();
            }
        }

        // Fallback terakhir
        if (!$user) {
            $user = User::first();
        }

        if (!$user) {
            return response()->json([
                'message' => 'User tidak ditemukan',
            ], 404);
        }

        $student = null;

        if (($user->role ?? '') === 'siswa') {
            $student = Student::where('user_id', $user->id)->first();
        }

        $photo = $user->photo_url
            ?? $user->photo
            ?? $user->foto
            ?? null;

        $photoUrl = null;

        if ($photo) {
            if (str_starts_with($photo, 'http://') || str_starts_with($photo, 'https://')) {
                $photoUrl = $photo;
            } else {
                $photo = ltrim($photo, '/');

                if (str_starts_with($photo, 'storage/')) {
                    $photoUrl = asset($photo);
                } else {
                    $photoUrl = asset('storage/' . $photo);
                }
            }
        }

        return response()->json([
            'id' => $user->id,
            'user_id' => $user->id,
            'student_id' => $student?->id,

            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,

            'photo' => $user->photo ?? null,
            'foto' => $user->foto ?? null,
            'photo_url' => $photoUrl,

            'student' => $student,
        ]);

    } catch (\Throwable $e) {
        return response()->json([
            'message' => 'Profile API error',
            'error' => $e->getMessage(),
            'line' => $e->getLine(),
            'file' => basename($e->getFile()),
        ], 500);
    }
});

/*
|--------------------------------------------------------------------------
| ADMIN PROFILE / BIODATA
|--------------------------------------------------------------------------
*/
/*
|--------------------------------------------------------------------------
| PUBLIC FILE VIEWER - ADMIN PROFILE
|--------------------------------------------------------------------------
*/

Route::get('/files/admin-profiles/{filename}', function ($filename) {
    try {
        $filename = basename($filename);

        $path = storage_path('app/public/admin-profiles/' . $filename);

        if (!file_exists($path)) {
            return response()->json([
                'message' => 'File foto admin tidak ditemukan.',
                'filename' => $filename,
                'path' => $path,
            ], 404);
        }

        return response()->file($path, [
            'Cache-Control' => 'public, max-age=86400',
        ]);

    } catch (\Throwable $e) {
        return response()->json([
            'message' => 'File viewer error',
            'error' => $e->getMessage(),
            'line' => $e->getLine(),
            'file' => basename($e->getFile()),
        ], 500);
    }
});
Route::get('/admin/profile', function (Request $request) {
    try {
        $user = techc_user_from_request($request);

        if (!$user) {
            return response()->json([
                'message' => 'User admin tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'photo' => $user->photo ?? null,
            'photo_url' => techc_admin_photo_url($user->photo ?? null),
            'phone' => $user->phone ?? null,
            'whatsapp' => $user->whatsapp ?? null,
            'position' => $user->position ?? null,
            'address' => $user->address ?? null,
            'bio' => $user->bio ?? null,
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
        ]);

    } catch (\Throwable $e) {
        return response()->json([
            'message' => 'Admin profile API error',
            'error' => $e->getMessage(),
            'line' => $e->getLine(),
            'file' => basename($e->getFile()),
        ], 500);
    }
});

Route::post('/admin/profile', function (Request $request) {
    try {
        $user = techc_user_from_request($request);

        if (!$user) {
            return response()->json([
                'message' => 'User admin tidak ditemukan.',
            ], 404);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
            'whatsapp' => 'nullable|string|max:50',
            'position' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'bio' => 'nullable|string',
            'password' => 'nullable|string|min:6',
        ]);

        $emailExists = User::where('email', $request->email)
            ->where('id', '!=', $user->id)
            ->exists();

        if ($emailExists) {
            return response()->json([
                'message' => 'Email sudah digunakan admin lain.',
            ], 422);
        }

        $payload = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'whatsapp' => $request->whatsapp,
            'position' => $request->position,
            'address' => $request->address,
            'bio' => $request->bio,
        ];

        if ($request->filled('password')) {
            $payload['password'] = Hash::make($request->password);
        }

        $user->update($payload);

        return response()->json([
            'message' => 'Profile admin berhasil diperbarui.',
            'user' => [
                'id' => $user->id,
                'name' => $user->fresh()->name,
                'email' => $user->fresh()->email,
                'role' => $user->fresh()->role,
                'photo' => $user->fresh()->photo,
                'photo_url' => techc_admin_photo_url($user->fresh()->photo),
                'phone' => $user->fresh()->phone,
                'whatsapp' => $user->fresh()->whatsapp,
                'position' => $user->fresh()->position,
                'address' => $user->fresh()->address,
                'bio' => $user->fresh()->bio,
            ],
        ]);

    } catch (\Throwable $e) {
        return response()->json([
            'message' => 'Update profile admin error',
            'error' => $e->getMessage(),
            'line' => $e->getLine(),
            'file' => basename($e->getFile()),
        ], 500);
    }
});


Route::post('/admin/profile/photo', function (Request $request) {
    try {
        $user = techc_user_from_request($request);

        if (!$user) {
            return response()->json([
                'message' => 'User admin tidak ditemukan.',
            ], 404);
        }

        $request->validate([
            'photo' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        $path = $request->file('photo')->store('admin-profiles', 'public');

        $user->update([
            'photo' => $path,
        ]);

        $freshUser = $user->fresh();

        return response()->json([
            'message' => 'Foto profile berhasil diperbarui.',
            'photo' => $path,
            'photo_url' => techc_admin_photo_url($path),
            'user' => [
                'id' => $freshUser->id,
                'name' => $freshUser->name,
                'email' => $freshUser->email,
                'role' => $freshUser->role,
                'photo' => $freshUser->photo,
                'photo_url' => techc_admin_photo_url($freshUser->photo),
                'phone' => $freshUser->phone ?? null,
                'whatsapp' => $freshUser->whatsapp ?? null,
                'position' => $freshUser->position ?? null,
                'address' => $freshUser->address ?? null,
                'bio' => $freshUser->bio ?? null,
            ],
        ]);

    } catch (\Throwable $e) {
        return response()->json([
            'message' => 'Upload foto profile error',
            'error' => $e->getMessage(),
            'line' => $e->getLine(),
            'file' => basename($e->getFile()),
        ], 500);
    }
});
/*
|--------------------------------------------------------------------------
| DASHBOARD ADMIN
|--------------------------------------------------------------------------
*/
Route::get('/dashboard', function () {
    $totalPelajar = Student::count();
    $totalSekolah = School::count();
    $totalPengajar = Teacher::count();
    $totalPelajaran = Lesson::count();

    $invoiceTotal = Invoice::sum('total');
    $invoiceLunas = Invoice::where('status', 'Lunas')->sum('total');
    $invoiceBelumDibayar = Invoice::where('status', 'Belum Dibayar')->sum('total');

    return response()->json([
        'totalPelajar' => $totalPelajar,
        'totalSekolah' => $totalSekolah,
        'totalPengajar' => $totalPengajar,
        'totalPelajaran' => $totalPelajaran,
        'pemesananSiswa' => Student::whereDate('created_at', now()->toDateString())->count(),
        'pendapatan' => $invoiceLunas,
        'totalInvoice' => $invoiceTotal,
        'tagihanBelumDibayar' => $invoiceBelumDibayar,
        'invoiceCount' => Invoice::count(),
        'invoiceLunasCount' => Invoice::where('status', 'Lunas')->count(),
        'invoiceBelumDibayarCount' => Invoice::where('status', 'Belum Dibayar')->count(),
        'pengumuman' => Announcement::where('status', 'Published')->count(),
        'permintaanPenggantian' => ReimbursementRequest::where('status', 'Pending')->count(),
        'latest_students' => Student::with('school')->latest()->take(6)->get(),
        'latest_invoices' => Invoice::with('student')->latest()->take(6)->get(),
        'chart' => [
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun'],
            'data' => [
                max(1, round($totalPelajar * 0.20)),
                max(1, round($totalPelajar * 0.35)),
                max(1, round($totalPelajar * 0.50)),
                max(1, round($totalPelajar * 0.70)),
                max(1, round($totalPelajar * 0.85)),
                max(1, $totalPelajar),
            ],
        ],
    ]);
});

/*
|--------------------------------------------------------------------------
| SEKOLAH
|--------------------------------------------------------------------------
*/
Route::get('/sekolah', function () {
    return School::withCount('students')
        ->orderBy('nama')
        ->get()
        ->map(function ($school) {
            $school->name = $school->nama;
            $school->total_siswa = $school->students_count;
            $school->logo_url = techc_photo_url($school->logo);
            return $school;
        });
});

Route::get('/schools', function () {
    return School::withCount('students')
        ->orderBy('nama')
        ->get()
        ->map(function ($school) {
            $school->name = $school->nama;
            $school->total_siswa = $school->students_count;
            $school->logo_url = techc_photo_url($school->logo);
            return $school;
        });
});

Route::get('/schools/{school}', function (School $school) {
    return $school->load(['students', 'places', 'schedules']);
});

Route::post('/schools', function (Request $request) {
    return response()->json(School::create($request->all()), 201);
});

Route::put('/schools/{school}', function (School $school, Request $request) {
    $school->update($request->all());
    return response()->json($school);
});

Route::delete('/schools/{school}', function (School $school) {
    $school->delete();
    return response()->json(['message' => 'Sekolah berhasil dihapus']);
});

/*
|--------------------------------------------------------------------------
| STUDENTS
|--------------------------------------------------------------------------
*/
Route::get('/students', function (Request $request) {
    $query = Student::with(['school', 'package']);

    if ($request->school_id) {
        $query->where('school_id', $request->school_id);
    }

    return $query->latest()->get()->map(function ($student) {
        $student->nama = $student->name;
        $student->school_name = $student->school?->nama;
        $student->asal_sekolah = $student->school?->nama;
        $student->package_name = $student->package?->nama;
        $student->photo_url = techc_photo_url($student->photo);
        return $student;
    });
});

Route::get('/pelajar', function (Request $request) {
    $query = Student::with(['school', 'package']);

    if ($request->school_id) {
        $query->where('school_id', $request->school_id);
    }

    return $query->latest()->get()->map(function ($student) {
        $student->nama = $student->name;
        $student->school_name = $student->school?->nama;
        $student->asal_sekolah = $student->school?->nama;
        $student->package_name = $student->package?->nama;
        $student->photo_url = techc_photo_url($student->photo);
        return $student;
    });
});

Route::get('/students/{student}', function (Student $student) {
    return $student->load([
        'school',
        'package',
        'invoices.items',
        'progressRecords.lesson',
        'notifications',
        'projects',
        'certificates',
    ]);
});

Route::post('/students', function (Request $request) {
    return response()->json(Student::create($request->all()), 201);
});

Route::put('/students/{student}', function (Student $student, Request $request) {
    $student->update($request->all());
    return response()->json($student->load(['school', 'package']));
});

Route::delete('/students/{student}', function (Student $student) {
    $student->delete();
    return response()->json(['message' => 'Pelajar berhasil dihapus']);
});

/*
|--------------------------------------------------------------------------
| TEACHERS
|--------------------------------------------------------------------------
*/
Route::get('/teachers', function () {
    return Teacher::latest()->get()->map(function ($teacher) {
        $teacher->nama = $teacher->name;
        $teacher->spesialisasi = $teacher->specialization;
        $teacher->photo_url = techc_photo_url($teacher->photo);
        return $teacher;
    });
});

Route::get('/pengajar', function () {
    return Teacher::latest()->get()->map(function ($teacher) {
        $teacher->nama = $teacher->name;
        $teacher->spesialisasi = $teacher->specialization;
        $teacher->photo_url = techc_photo_url($teacher->photo);
        return $teacher;
    });
});

Route::get('/teachers/{teacher}', function (Teacher $teacher) {
    return $teacher->load(['schedules', 'payrolls']);
});

Route::post('/teachers', function (Request $request) {
    $data = $request->all();

    if (isset($data['skills']) && is_array($data['skills'])) {
        $data['skills'] = json_encode($data['skills']);
    }

    return response()->json(Teacher::create($data), 201);
});

/*
|--------------------------------------------------------------------------
| LESSONS
|--------------------------------------------------------------------------
*/
Route::get('/lessons', function () {
    return Lesson::with('categoryData')
        ->orderBy('sort_order')
        ->get()
        ->map(function ($lesson) {
            $lesson->nama = $lesson->title;
            $lesson->kategori = $lesson->category;
            $lesson->deskripsi = $lesson->description;

            if (is_string($lesson->tools)) {
                $decoded = json_decode($lesson->tools, true);
                $lesson->tools = is_array($decoded) ? $decoded : [];
            }

            if (is_string($lesson->topics)) {
                $decoded = json_decode($lesson->topics, true);
                $lesson->topics = is_array($decoded) ? $decoded : [];
            }

            return $lesson;
        });
});

Route::get('/pelajaran', function () {
    return Lesson::with('categoryData')
        ->orderBy('sort_order')
        ->get();
});

Route::post('/lessons', function (Request $request) {
    $data = $request->all();

    if (isset($data['tools']) && is_array($data['tools'])) {
        $data['tools'] = json_encode($data['tools']);
    }

    if (isset($data['topics']) && is_array($data['topics'])) {
        $data['topics'] = json_encode($data['topics']);
    }

    return response()->json(Lesson::create($data), 201);
});

/*
|--------------------------------------------------------------------------
| INVOICES
|--------------------------------------------------------------------------
*/
Route::get('/invoices', function () {
    return Invoice::with(['student.school', 'items', 'payments'])
        ->latest()
        ->get();
});

Route::get('/invoices/{invoice}', function (Invoice $invoice) {
    return $invoice->load(['student.school', 'items', 'payments']);
});

Route::post('/invoices', function (Request $request) {
    $student = Student::with('school')->find($request->student_id);

    $meetingCount = (int) ($request->meeting_count ?? 0);
    $pricePerMeeting = (int) ($request->price_per_meeting ?? 0);
    $mainTotal = (int) ($request->main_total ?? ($meetingCount * $pricePerMeeting));
    $extraFee = (int) ($request->extra_fee ?? 0);

    $extraItems = $request->extra_items ?? [];

    if (is_string($extraItems)) {
        $decoded = json_decode($extraItems, true);
        $extraItems = is_array($decoded) ? $decoded : [];
    }

    $extraItemTotal = collect($extraItems)->sum(function ($item) {
        $price = (int) ($item['price'] ?? 0);
        $qty = (int) ($item['qty'] ?? 1);
        return (int) ($item['total'] ?? ($price * $qty));
    });

    $subtotal = (int) ($request->subtotal ?? ($mainTotal + $extraItemTotal));
    $total = (int) ($request->total ?? ($subtotal + $extraFee));

    $invoice = Invoice::create([
        'student_id' => $student?->id,
        'school_id' => $student?->school_id,
        'package_id' => $student?->package_id,
        'invoice_no' => $request->invoice_no ?? 'INV-TECHC-' . now()->format('YmdHis'),
        'date' => $request->date ?? now()->toDateString(),
        'due_date' => $request->due_date,
        'student_name' => $request->student_name ?? $student?->name,
        'student_class' => $request->student_class ?? $student?->kelas,
        'student_school' => $request->student_school ?? $student?->school?->nama,
        'package_name' => $request->package_name ?? 'Kelas Robotic & Coding',
        'meeting_count' => $meetingCount,
        'price_per_meeting' => $pricePerMeeting,
        'main_total' => $mainTotal,
        'extra_fee' => $extraFee,
        'subtotal' => $subtotal,
        'total' => $total,
        'extra_items' => json_encode($extraItems),
        'note' => $request->note,
        'status' => $request->status ?? 'Belum Dibayar',
        'invoice_image' => $request->invoice_image,
    ]);

    $invoice->items()->create([
        'description' => ($request->package_name ?? 'Kelas Robotic & Coding') . ' (' . $meetingCount . ' Pertemuan)',
        'price' => $pricePerMeeting,
        'qty' => $meetingCount,
        'total' => $mainTotal,
    ]);

    foreach ($extraItems as $item) {
        $price = (int) ($item['price'] ?? 0);
        $qty = (int) ($item['qty'] ?? 1);

        $invoice->items()->create([
            'description' => $item['description'] ?? 'Item tambahan',
            'price' => $price,
            'qty' => $qty,
            'total' => (int) ($item['total'] ?? ($price * $qty)),
        ]);
    }

    if ($student) {
        $student->update([
            'tagihan' => Invoice::where('student_id', $student->id)
                ->where('status', 'Belum Dibayar')
                ->sum('total'),
        ]);

        StudentNotification::create([
            'student_id' => $student->id,
            'type' => 'invoice',
            'title' => 'Tagihan Baru',
            'message' => 'Invoice baru ' . $invoice->invoice_no . ' dengan total Rp ' . number_format($invoice->total, 0, ',', '.'),
            'url' => 'tagihan-siswa.html',
            'is_read' => false,
        ]);
    }

    return response()->json($invoice->load(['student.school', 'items']), 201);
});

Route::post('/invoices/{invoice}/mark-paid', function (Invoice $invoice) {
    $invoice->update(['status' => 'Lunas']);

    if ($invoice->student_id) {
        $student = Student::find($invoice->student_id);

        if ($student) {
            $student->update([
                'tagihan' => Invoice::where('student_id', $student->id)
                    ->where('status', 'Belum Dibayar')
                    ->sum('total'),
            ]);
        }
    }

    return response()->json($invoice);
});
Route::get('/admin/unpaid-invoices', function () {
    return Invoice::with(['student.school', 'items', 'payments'])
        ->whereIn('status', ['Belum Dibayar', 'Pending'])
        ->latest()
        ->get()
        ->map(function ($invoice) {
            $latestPayment = $invoice->payments
                ->filter(fn ($payment) => !empty($payment->proof_file))
                ->sortByDesc('created_at')
                ->first();

            $invoice->student_name_display = $invoice->student?->name ?? $invoice->student_name;
            $invoice->student_school_display = $invoice->student?->school?->nama ?? $invoice->student_school;
            $invoice->payment_proof_url = $latestPayment ? techc_storage_url($latestPayment->proof_file) : null;

            return $invoice;
        });
});

Route::post('/invoices/{invoice}/upload-proof-paid', function (Invoice $invoice, Request $request) {
    $request->validate([
        'proof_file' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
    ]);

    $path = $request->file('proof_file')->store('payment-proofs', 'public');

    $payment = Payment::create([
        'invoice_id' => $invoice->id,
        'student_id' => $invoice->student_id,
        'payment_date' => now()->toDateString(),
        'amount' => $request->amount ?? $invoice->total,
        'method' => $request->method ?? 'Manual Admin',
        'proof_file' => $path,
        'status' => 'Approved',
        'note' => $request->note ?? 'Pembayaran dikonfirmasi oleh admin.',
    ]);

    $invoice->update([
        'status' => 'Lunas',
    ]);

    if ($invoice->student_id) {
        $student = Student::find($invoice->student_id);

        if ($student) {
            $student->update([
                'tagihan' => Invoice::where('student_id', $student->id)
                    ->whereIn('status', ['Belum Dibayar', 'Pending'])
                    ->sum('total'),
            ]);

            StudentNotification::create([
                'student_id' => $student->id,
                'type' => 'invoice_paid',
                'title' => 'Pembayaran Berhasil',
                'message' => 'Invoice ' . $invoice->invoice_no . ' sudah dikonfirmasi lunas.',
                'url' => 'student-pages.html?page=tagihan',
                'is_read' => false,
            ]);
        }
    }

    return response()->json([
        'message' => 'Bukti pembayaran berhasil diupload dan invoice sudah lunas.',
        'invoice' => $invoice->fresh(['student.school', 'items', 'payments']),
        'payment' => $payment,
        'proof_url' => techc_storage_url($path),
    ]);
});
/*
|--------------------------------------------------------------------------
| STUDENT DASHBOARD
|--------------------------------------------------------------------------
/*/

Route::get('/student/dashboard', function (Request $request) {
    try {
        $user = techc_user_from_request($request);

        $student = null;

        $studentId = $request->header('X-Student-Id')
            ?? $request->query('student_id')
            ?? $request->input('student_id');

        if ($studentId) {
            $student = Student::where('id', $studentId)->first();
        }

        if (!$student && $user) {
            $student = Student::where('user_id', $user->id)->first();
        }

        if (!$student) {
            return response()->json([
                'message' => 'Data siswa tidak ditemukan untuk user login ini.',
                'debug' => [
                    'user_id' => $request->query('user_id'),
                    'student_id' => $request->query('student_id'),
                    'x_user_id' => $request->header('X-User-Id'),
                    'x_student_id' => $request->header('X-Student-Id'),
                    'x_user_email' => $request->header('X-User-Email'),
                    'authorization' => $request->header('Authorization'),
                ],
            ], 404);
        }

        $studentData = $student->toArray();

        try {
            $school = $student->school;
            $studentData['school'] = $school;
            $studentData['school_name'] = $school?->nama;
            $studentData['asal_sekolah'] = $school?->nama;
        } catch (\Throwable $e) {
            $studentData['school'] = null;
            $studentData['school_name'] = $student->asal_sekolah ?? null;
            $studentData['asal_sekolah'] = $student->asal_sekolah ?? null;
        }

        try {
            $package = $student->package;
            $studentData['package'] = $package;
            $studentData['package_name'] = $package?->nama;
        } catch (\Throwable $e) {
            $studentData['package'] = null;
            $studentData['package_name'] = null;
        }

        $invoices = collect();

        try {
            $invoices = Invoice::where('student_id', $student->id)
                ->latest()
                ->get()
                ->map(function ($invoice) {
                    $invoiceData = $invoice->toArray();

                    try {
                        $invoiceData['items'] = $invoice->items()->get();
                    } catch (\Throwable $e) {
                        $invoiceData['items'] = [];
                    }

                    try {
                        $invoiceData['payments'] = $invoice->payments()->get()->map(function ($payment) {
                            $paymentData = $payment->toArray();
                            $paymentData['proof_url'] = techc_storage_url($payment->proof_file ?? null);
                            return $paymentData;
                        });
                    } catch (\Throwable $e) {
                        $invoiceData['payments'] = [];
                    }

                    return $invoiceData;
                });
        } catch (\Throwable $e) {
            $invoices = collect();
        }

        $unpaidTotal = $invoices
            ->filter(function ($invoice) {
                $status = $invoice['status'] ?? 'Belum Dibayar';
                return in_array($status, ['Belum Dibayar', 'Pending']);
            })
            ->sum(function ($invoice) {
                return (int) ($invoice['total'] ?? 0);
            });

        $notifications = [];

        try {
            $notifications = StudentNotification::where('student_id', $student->id)
                ->latest()
                ->take(10)
                ->get();
        } catch (\Throwable $e) {
            $notifications = [];
        }

        $jadwal = [];

        try {
            if (is_array($student->jadwal)) {
                $jadwal = $student->jadwal;
            } elseif (is_string($student->jadwal) && $student->jadwal !== '') {
                $decoded = json_decode($student->jadwal, true);
                $jadwal = is_array($decoded) ? $decoded : [];
            }
        } catch (\Throwable $e) {
            $jadwal = [];
        }

        return response()->json([
            'student' => $studentData,
            'jumlah_anak' => 1,
            'progress_belajar' => (int) ($student->progress_belajar ?? 0),
            'tagihan' => $unpaidTotal,
            'catatan' => $student->catatan ?? null,
            'jadwal' => $jadwal,
            'invoices' => $invoices->values(),
            'notifications' => $notifications,
        ]);

    } catch (\Throwable $e) {
        return response()->json([
            'message' => 'Student dashboard API error',
            'error' => $e->getMessage(),
            'line' => $e->getLine(),
            'file' => basename($e->getFile()),
        ], 500);
    }
});
Route::get('/student/invoices', function (Request $request) {
    try {
        $user = techc_user_from_request($request);

        $studentId = $request->header('X-Student-Id')
            ?? $request->query('student_id')
            ?? $request->input('student_id');

        $student = null;

        if ($studentId) {
            $student = Student::find($studentId);
        }

        if (!$student && $user) {
            $student = Student::where('user_id', $user->id)->first();
        }

        if (!$student) {
            return response()->json([]);
        }

        return Invoice::where('student_id', $student->id)
            ->latest()
            ->get()
            ->map(function ($invoice) {
                $invoiceData = $invoice->toArray();

                try {
                    $invoiceData['items'] = $invoice->items()->get();
                } catch (\Throwable $e) {
                    $invoiceData['items'] = [];
                }

                try {
                    $invoiceData['payments'] = $invoice->payments()->get()->map(function ($payment) {
                        $paymentData = $payment->toArray();
                        $paymentData['proof_url'] = techc_storage_url($payment->proof_file ?? null);
                        return $paymentData;
                    });
                } catch (\Throwable $e) {
                    $invoiceData['payments'] = [];
                }

                return $invoiceData;
            });

    } catch (\Throwable $e) {
        return response()->json([
            'message' => 'Student invoices API error',
            'error' => $e->getMessage(),
            'line' => $e->getLine(),
            'file' => basename($e->getFile()),
        ], 500);
    }
});

Route::get('/students/{student}/invoices', function (Student $student) {
    return $student->invoices()
        ->with(['items', 'payments'])
        ->latest()
        ->get();
});

/*
|--------------------------------------------------------------------------
| PAYMENTS
|--------------------------------------------------------------------------
*/
Route::post('/invoices/{invoice}/pay', function (Invoice $invoice, Request $request) {
    $payment = Payment::create([
        'invoice_id' => $invoice->id,
        'student_id' => $invoice->student_id,
        'payment_date' => now()->toDateString(),
        'amount' => $request->amount ?? $invoice->total,
        'method' => $request->method ?? 'BCA',
        'proof_file' => $request->proof_file,
        'status' => 'Pending',
        'note' => $request->note,
    ]);

    $invoice->update(['status' => 'Pending']);

    return response()->json($payment, 201);
});

Route::get('/payments', fn () => Payment::with(['invoice', 'student'])->latest()->get());

/*
|--------------------------------------------------------------------------
| STUDENT FEATURES
|--------------------------------------------------------------------------
*/
Route::get('/student/notifications', function (Request $request) {
    $studentId = $request->header('X-Student-Id')
        ?? $request->query('student_id');

    $query = StudentNotification::latest();

    if ($studentId) {
        $query->where('student_id', $studentId);
    }

    return $query->get();
});

Route::post('/student/notifications/{notification}/read', function (StudentNotification $notification) {
    $notification->update(['is_read' => true]);
    return response()->json($notification);
});

Route::get('/materials', fn () => Material::with('lesson')->latest()->get());
Route::get('/certificates', fn () => Certificate::with(['student', 'lesson'])->latest()->get());
Route::get('/projects', fn () => StudentProject::with(['student', 'lesson'])->latest()->get());
Route::get('/progress-records', fn () => ProgressRecord::with(['student', 'lesson', 'teacher'])->latest()->get());
use Illuminate\Support\Facades\File;

Route::get('/debug-storage', function () {
    return [
        'public_storage_exists' => File::exists(public_path('storage')),
        'public_storage_is_link' => is_link(public_path('storage')),
        'public_storage_path' => public_path('storage'),
        'storage_target' => @readlink(public_path('storage')),
    ];
});

Route::get('/debug-file', function () {

    $path = 'student-documentations/5e0wMxwWa6xy1NAjtMtuZZXhMFVT01m9ehIlNe6k.jpg';

    return [
        'exists' => Storage::disk('public')->exists($path),
        'path' => Storage::disk('public')->path($path),
        'url' => Storage::disk('public')->url($path),
        'size' => Storage::disk('public')->exists($path)
            ? Storage::disk('public')->size($path)
            : null,
    ];

});
Route::get('/debug-files', function () {
    return Storage::disk('public')->allFiles();
});
use Cloudinary\Cloudinary;
Route::get('/debug-public', function () {
    return [
        'favicon_exists' => file_exists(public_path('favicon.ico')),
        'logo_exists' => file_exists(public_path('images/logo.png')),
        'public_path' => public_path(),
    ];
});
Route::get('/cloudinary-test', function () {

    $cloudinary = new Cloudinary([
        'cloud' => [
            'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
            'api_key' => env('CLOUDINARY_API_KEY'),
            'api_secret' => env('CLOUDINARY_API_SECRET'),
        ],
        'url' => [
            'secure' => true,
        ],
    ]);

    $result = $cloudinary->uploadApi()->upload(
        public_path('favicon.ico'),
        [
            'folder' => 'techc-test'
        ]
    );

    return $result['secure_url'];
});
/*
|--------------------------------------------------------------------------
| DOCUMENTATION PLACEHOLDER
|--------------------------------------------------------------------------
*/
/*
|--------------------------------------------------------------------------
| STUDENT DOCUMENTATIONS
|--------------------------------------------------------------------------
*/

Route::get('/admin/documentations', function (Request $request) {
    return StudentDocumentation::with('student.school')
        ->latest()
        ->get()
        ->map(function ($doc) {
            $doc->student_name = $doc->student?->name;
            $doc->student_class = $doc->student?->kelas;
            $doc->student_school = $doc->student?->school?->nama;
            return $doc;
        });
});

Route::post('/admin/documentations', function (Request $request) {
    $request->validate([
        'student_id' => 'required|exists:students,id',
        'title' => 'required|string|max:255',
        'description' => 'nullable|string',
        'image' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
    ]);

    $path = $request->file('image')->store('student-documentations', 'public');

    $doc = StudentDocumentation::create([
        'student_id' => $request->student_id,
        'title' => $request->title,
        'description' => $request->description,
        'image_path' => $path,
        'status' => 'Published',
    ]);

    if (class_exists(StudentNotification::class)) {
        StudentNotification::create([
            'student_id' => $request->student_id,
            'type' => 'documentation',
            'title' => 'Dokumentasi Baru',
            'message' => 'Admin TECH-C mengirim dokumentasi baru: ' . $request->title,
            'url' => 'student-pages.html?page=dokumentasi',
            'is_read' => false,
        ]);
    }

    return response()->json([
        'message' => 'Dokumentasi berhasil dikirim ke siswa.',
        'data' => $doc->load('student.school'),
    ], 201);
});

Route::delete('/admin/documentations/{documentation}', function (StudentDocumentation $documentation) {
    if ($documentation->image_path && Storage::disk('public')->exists($documentation->image_path)) {
        Storage::disk('public')->delete($documentation->image_path);
    }

    $documentation->delete();

    return response()->json([
        'message' => 'Dokumentasi berhasil dihapus.',
    ]);
});Route::get('/student/documentations', function (Request $request) {
    try {
        $studentId = $request->header('X-Student-Id')
            ?? $request->query('student_id')
            ?? $request->input('student_id');

        $student = null;

        if ($studentId) {
            $student = Student::find($studentId);
        }

        if (!$student) {
            $user = techc_user_from_request($request);

            if ($user) {
                $student = Student::where('user_id', $user->id)->first();
            }
        }

        if (!$student) {
            return response()->json([]);
        }

        return StudentDocumentation::where('student_id', $student->id)
            ->where('status', 'Published')
            ->latest()
            ->get()
            ->map(function ($doc) {
                $doc->image_url = techc_storage_url($doc->image_path);
                $doc->uploaded_at = $doc->created_at?->format('Y-m-d H:i:s');
                return $doc;
            });

    } catch (\Throwable $e) {
        return response()->json([
            'message' => 'Student documentations API error',
            'error' => $e->getMessage(),
            'line' => $e->getLine(),
            'file' => basename($e->getFile()),
        ], 500);
    }
});

/*
|--------------------------------------------------------------------------
| CHAT
|--------------------------------------------------------------------------
*/
Route::get('/chat/threads', function (Request $request) {
    $query = ChatThread::with(['student', 'teacher', 'invoice', 'messages'])->latest();

    if ($request->student_id) {
        $query->where('student_id', $request->student_id);
    }

    if ($request->invoice_id) {
        $query->where('invoice_id', $request->invoice_id);
    }

    return $query->get();
});

Route::post('/chat/threads', function (Request $request) {
    $thread = ChatThread::create([
        'student_id' => $request->student_id,
        'teacher_id' => $request->teacher_id,
        'invoice_id' => $request->invoice_id,
        'subject' => $request->subject ?? 'Chat TECH-C',
        'status' => 'Open',
    ]);

    return response()->json($thread->load('messages'), 201);
});

Route::post('/chat/messages', function (Request $request) {
    $threadId = $request->chat_thread_id;

    if (!$threadId) {
        $thread = ChatThread::create([
            'student_id' => $request->student_id,
            'invoice_id' => $request->invoice_id,
            'subject' => $request->subject ?? 'Chat TECH-C',
            'status' => 'Open',
        ]);

        $threadId = $thread->id;
    }

    $message = ChatMessage::create([
        'chat_thread_id' => $threadId,
        'sender_user_id' => $request->sender_user_id,
        'sender_role' => $request->sender_role ?? 'siswa',
        'message' => $request->message,
        'attachment' => $request->attachment,
        'is_read' => false,
    ]);

    return response()->json($message, 201);
});

/*
|--------------------------------------------------------------------------
| SIMPLE DATA
|--------------------------------------------------------------------------
*/
Route::get('/places', fn () => Place::with('school')->latest()->get());
Route::get('/tempat', fn () => Place::with('school')->latest()->get());
Route::get('/packages', fn () => Package::latest()->get());
Route::get('/paket', fn () => Package::latest()->get());

Route::get('/financial-transactions', fn () => FinancialTransaction::latest()->get());
Route::get('/keuangan', fn () => FinancialTransaction::latest()->get());

Route::get('/payrolls', fn () => Payroll::with('teacher')->latest()->get());
Route::get('/penggajian', fn () => Payroll::with('teacher')->latest()->get());

Route::get('/reimbursements', fn () => ReimbursementRequest::with(['teacher', 'student'])->latest()->get());
Route::get('/pengajuan-penggantian', fn () => ReimbursementRequest::with(['teacher', 'student'])->latest()->get());

Route::get('/inventory', fn () => InventoryItem::latest()->get());
Route::get('/announcements', fn () => Announcement::latest()->get());
Route::get('/pengumuman', fn () => Announcement::latest()->get());
Route::get('/events', fn () => Event::latest()->get());
Route::get('/help-tickets', fn () => HelpTicket::latest()->get());
/*
|--------------------------------------------------------------------------
| DAILY REPORTS
|--------------------------------------------------------------------------
*/

Route::get('/reports', function (Request $request) {
    $query = Report::query()->latest();

    if ($request->filled('partner_name')) {
        $query->where('partner_name', $request->partner_name);
    }

    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }

    if ($request->filled('date')) {
        $query->whereDate('report_date', $request->date);
    }

    if ($request->filled('search')) {
        $search = $request->search;

        $query->where(function ($q) use ($search) {
            $q->where('partner_name', 'like', "%{$search}%")
              ->orWhere('title', 'like', "%{$search}%")
              ->orWhere('category', 'like', "%{$search}%")
              ->orWhere('content', 'like', "%{$search}%")
              ->orWhere('status', 'like', "%{$search}%");
        });
    }

    return response()->json($query->get());
});

Route::post('/reports', function (Request $request) {
    $request->validate([
        'report_date' => 'nullable|date',
        'date' => 'nullable|date',
        'partner_name' => 'required|string|max:255',
        'title' => 'required|string|max:255',
        'category' => 'nullable|string|max:255',
        'content' => 'required|string',
        'status' => 'nullable|string|max:255',
        'reporter_name' => 'nullable|string|max:255',
        'reporter_email' => 'nullable|string|max:255',
    ]);

    $report = Report::create([
        'report_date' => $request->report_date
            ?? $request->date
            ?? now()->toDateString(),

        'partner_name' => $request->partner_name
            ?? $request->rekan
            ?? $request->nama_rekan,

        'title' => $request->title
            ?? $request->judul
            ?? 'Laporan Harian',

        'category' => $request->category
            ?? $request->kategori
            ?? 'Kegiatan Harian',

        'content' => $request->content
            ?? $request->isi_laporan
            ?? $request->description
            ?? $request->deskripsi,

        'status' => $request->status ?? 'Selesai',

        'reporter_name' => $request->reporter_name
            ?? $request->header('X-User-Name')
            ?? 'Admin TECH-C',

        'reporter_email' => $request->reporter_email
            ?? $request->header('X-User-Email'),
    ]);

    return response()->json([
        'message' => 'Laporan berhasil disimpan.',
        'data' => $report,
    ], 201);
});

Route::get('/reports/{report}', function (Report $report) {
    return response()->json($report);
});

Route::put('/reports/{report}', function (Report $report, Request $request) {
    $report->update([
        'report_date' => $request->report_date ?? $request->date ?? $report->report_date,
        'partner_name' => $request->partner_name ?? $report->partner_name,
        'title' => $request->title ?? $report->title,
        'category' => $request->category ?? $report->category,
        'content' => $request->content ?? $report->content,
        'status' => $request->status ?? $report->status,
        'reporter_name' => $request->reporter_name ?? $report->reporter_name,
        'reporter_email' => $request->reporter_email ?? $report->reporter_email,
    ]);

    return response()->json([
        'message' => 'Laporan berhasil diperbarui.',
        'data' => $report,
    ]);
});

Route::delete('/reports/{report}', function (Report $report) {
    $report->delete();

    return response()->json([
        'message' => 'Laporan berhasil dihapus.',
    ]);
});

Route::post('/announcements', fn (Request $request) => response()->json(Announcement::create($request->all()), 201));
Route::post('/events', fn (Request $request) => response()->json(Event::create($request->all()), 201));
Route::post('/help-tickets', fn (Request $request) => response()->json(HelpTicket::create($request->all()), 201));
Route::post('/financial-transactions', fn (Request $request) => response()->json(FinancialTransaction::create($request->all()), 201));
Route::post('/payrolls', fn (Request $request) => response()->json(Payroll::create($request->all()), 201));
Route::post('/reimbursements', fn (Request $request) => response()->json(ReimbursementRequest::create($request->all()), 201));