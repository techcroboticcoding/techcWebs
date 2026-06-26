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

/*
|--------------------------------------------------------------------------
| HELPER
|--------------------------------------------------------------------------
*/
function techc_user_from_request(Request $request)
{
    $email = $request->header('X-User-Email')
        ?? $request->query('email')
        ?? $request->input('email');

    if ($email) {
        $user = User::where('email', $email)->first();

        if ($user) {
            return $user;
        }
    }

    $role = $request->header('X-User-Role')
        ?? $request->query('role');

    if ($role) {
        $user = User::where('role', $role)->first();

        if ($user) {
            return $user;
        }
    }

    return User::first();
}

function techc_photo_url($path)
{
    if (!$path) return null;

    if (str_starts_with($path, 'http')) {
        return $path;
    }

    return asset('storage/' . $path);
}

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

    return response()->json([
        'token' => 'dummy-token-' . $user->id,
        'role' => $user->role ?? 'admin',
        'name' => $user->name,
        'email' => $user->email,
        'user_id' => $user->id,
        'user' => $user,
    ]);
});

/*
|--------------------------------------------------------------------------
| PROFILE
|--------------------------------------------------------------------------
*/
Route::get('/profile', function (Request $request) {
    $user = techc_user_from_request($request);

    return response()->json([
        'id' => $user?->id,
        'name' => $user?->name ?? 'Admin TECH-C',
        'email' => $user?->email,
        'phone' => $user?->phone,
        'bio' => $user?->bio,
        'country' => $user?->country,
        'city' => $user?->city,
        'postal_code' => $user?->postal_code,
        'tax_id' => $user?->tax_id,
        'role' => $user?->role ?? 'admin',
        'photo' => $user?->photo,
        'photo_url' => techc_photo_url($user?->photo),
    ]);
});

/*
|--------------------------------------------------------------------------
| ADMIN DASHBOARD
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
| SCHOOLS / SEKOLAH
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
    $school = School::create([
        'nama' => $request->nama ?? $request->name,
        'slug' => $request->slug,
        'kode' => $request->kode,
        'logo' => $request->logo,
        'jenjang' => $request->jenjang,
        'alamat' => $request->alamat,
        'kota' => $request->kota,
        'provinsi' => $request->provinsi,
        'pic_name' => $request->pic_name,
        'pic_phone' => $request->pic_phone,
        'email' => $request->email,
        'status' => $request->status ?? 'Aktif',
        'catatan' => $request->catatan,
    ]);

    return response()->json($school, 201);
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
| STUDENTS / PELAJAR
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
    $student = Student::create([
        'user_id' => $request->user_id,
        'school_id' => $request->school_id,
        'package_id' => $request->package_id,
        'name' => $request->name ?? $request->nama,
        'nis' => $request->nis,
        'photo' => $request->photo,
        'kelas' => $request->kelas,
        'kategori_level' => $request->kategori_level,
        'jenis_kelamin' => $request->jenis_kelamin,
        'tanggal_lahir' => $request->tanggal_lahir,
        'parent_name' => $request->parent_name,
        'parent_phone' => $request->parent_phone,
        'parent_email' => $request->parent_email,
        'student_type' => $request->student_type ?? 'sekolah',
        'progress_belajar' => $request->progress_belajar ?? 0,
        'tagihan' => $request->tagihan ?? 0,
        'jadwal' => $request->jadwal,
        'pengumuman' => $request->pengumuman,
        'catatan' => $request->catatan,
        'status' => $request->status ?? 'Aktif',
    ]);

    return response()->json($student->load(['school', 'package']), 201);
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
| TEACHERS / PENGAJAR
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
    $teacher = Teacher::create([
        'user_id' => $request->user_id,
        'name' => $request->name ?? $request->nama,
        'email' => $request->email,
        'phone' => $request->phone,
        'photo' => $request->photo,
        'specialization' => $request->specialization ?? $request->spesialisasi,
        'skills' => is_array($request->skills) ? json_encode($request->skills) : $request->skills,
        'address' => $request->address,
        'join_date' => $request->join_date,
        'salary_base' => $request->salary_base ?? 0,
        'fee_per_session' => $request->fee_per_session ?? 0,
        'status' => $request->status ?? 'Aktif',
        'catatan' => $request->catatan,
    ]);

    return response()->json($teacher, 201);
});

Route::put('/teachers/{teacher}', function (Teacher $teacher, Request $request) {
    $data = $request->all();

    if (isset($data['skills']) && is_array($data['skills'])) {
        $data['skills'] = json_encode($data['skills']);
    }

    $teacher->update($data);

    return response()->json($teacher);
});

Route::delete('/teachers/{teacher}', function (Teacher $teacher) {
    $teacher->delete();
    return response()->json(['message' => 'Pengajar berhasil dihapus']);
});

/*
|--------------------------------------------------------------------------
| LESSONS / PELAJARAN
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

Route::get('/lessons/{lesson}', function (Lesson $lesson) {
    return $lesson->load(['categoryData', 'materials']);
});

Route::post('/lessons', function (Request $request) {
    $lesson = Lesson::create([
        'lesson_category_id' => $request->lesson_category_id,
        'title' => $request->title ?? $request->nama,
        'slug' => $request->slug,
        'category' => $request->category ?? $request->kategori,
        'level' => $request->level ?? 'Beginner',
        'duration' => $request->duration ?? $request->durasi,
        'image' => $request->image ?? $request->gambar,
        'tools' => is_array($request->tools) ? json_encode($request->tools) : $request->tools,
        'topics' => is_array($request->topics) ? json_encode($request->topics) : $request->topics,
        'description' => $request->description ?? $request->deskripsi,
        'output' => $request->output,
        'sort_order' => $request->sort_order ?? 0,
        'status' => $request->status ?? 'Aktif',
    ]);

    return response()->json($lesson, 201);
});

Route::put('/lessons/{lesson}', function (Lesson $lesson, Request $request) {
    $data = $request->all();

    if (isset($data['tools']) && is_array($data['tools'])) {
        $data['tools'] = json_encode($data['tools']);
    }

    if (isset($data['topics']) && is_array($data['topics'])) {
        $data['topics'] = json_encode($data['topics']);
    }

    $lesson->update($data);

    return response()->json($lesson);
});

Route::delete('/lessons/{lesson}', function (Lesson $lesson) {
    $lesson->delete();
    return response()->json(['message' => 'Pelajaran berhasil dihapus']);
});

/*
|--------------------------------------------------------------------------
| PLACES / TEMPAT
|--------------------------------------------------------------------------
*/
Route::get('/places', fn () => Place::with('school')->latest()->get());
Route::get('/tempat', fn () => Place::with('school')->latest()->get());

Route::post('/places', function (Request $request) {
    return response()->json(Place::create($request->all()), 201);
});

/*
|--------------------------------------------------------------------------
| PACKAGES / PAKET
|--------------------------------------------------------------------------
*/
Route::get('/packages', fn () => Package::latest()->get());
Route::get('/paket', fn () => Package::latest()->get());

Route::post('/packages', function (Request $request) {
    $data = $request->all();

    if (isset($data['benefits']) && is_array($data['benefits'])) {
        $data['benefits'] = json_encode($data['benefits']);
    }

    return response()->json(Package::create($data), 201);
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

Route::put('/invoices/{invoice}', function (Invoice $invoice, Request $request) {
    $data = $request->all();

    if (isset($data['extra_items']) && is_array($data['extra_items'])) {
        $data['extra_items'] = json_encode($data['extra_items']);
    }

    $invoice->update($data);

    return response()->json($invoice->load(['student', 'items']));
});

Route::delete('/invoices/{invoice}', function (Invoice $invoice) {
    $invoice->delete();
    return response()->json(['message' => 'Invoice berhasil dihapus']);
});

Route::post('/invoices/{invoice}/mark-paid', function (Invoice $invoice) {
    $invoice->update([
        'status' => 'Lunas',
    ]);

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

/*
|--------------------------------------------------------------------------
| STUDENT INVOICES / TAGIHAN SISWA
|--------------------------------------------------------------------------
*/
Route::get('/student/invoices', function (Request $request) {
    $userId = $request->query('user_id');
    $studentId = $request->query('student_id');

    $query = Invoice::with(['student.school', 'items', 'payments'])->latest();

    if ($studentId) {
        $query->where('student_id', $studentId);
    } elseif ($userId) {
        $student = Student::where('user_id', $userId)->first();

        if ($student) {
            $query->where('student_id', $student->id);
        }
    }

    return $query->get();
});

Route::get('/students/{student}/invoices', function (Student $student) {
    return $student->invoices()
        ->with(['items', 'payments'])
        ->latest()
        ->get();
});

/*
|--------------------------------------------------------------------------
| PAYMENT
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

    $invoice->update([
        'status' => 'Pending',
    ]);

    if ($invoice->student_id) {
        StudentNotification::create([
            'student_id' => $invoice->student_id,
            'type' => 'invoice',
            'title' => 'Pembayaran Menunggu Verifikasi',
            'message' => 'Pembayaran invoice ' . $invoice->invoice_no . ' sedang menunggu verifikasi admin.',
            'url' => 'tagihan-siswa.html',
            'is_read' => false,
        ]);
    }

    return response()->json($payment, 201);
});

Route::get('/payments', fn () => Payment::with(['invoice', 'student'])->latest()->get());

/*
|--------------------------------------------------------------------------
| STUDENT DASHBOARD
|--------------------------------------------------------------------------
*/
Route::get('/student/dashboard', function (Request $request) {
    $userId = $request->query('user_id');

    $student = null;

    if ($userId) {
        $student = Student::with(['school', 'package'])->where('user_id', $userId)->first();
    }

    if (!$student) {
        $student = Student::with(['school', 'package'])->first();
    }

    $invoices = Invoice::with('items')
        ->where('student_id', $student?->id)
        ->latest()
        ->get();

    $unpaidTotal = $invoices
        ->filter(fn ($invoice) => $invoice->status === 'Belum Dibayar')
        ->sum('total');

    return response()->json([
        'student' => $student,
        'jumlah_anak' => 1,
        'progress_belajar' => $student?->progress_belajar ?? 0,
        'tagihan' => $unpaidTotal,
        'pengumuman' => Announcement::whereIn('target_role', ['siswa', 'all'])
            ->latest()
            ->take(5)
            ->get(),
        'catatan' => $student?->catatan,
        'jadwal' => $student?->jadwal ? json_decode($student->jadwal, true) : [],
        'invoices' => $invoices,
        'notifications' => StudentNotification::where('student_id', $student?->id)
            ->latest()
            ->take(10)
            ->get(),
    ]);
});

/*
|--------------------------------------------------------------------------
| NOTIFICATIONS
|--------------------------------------------------------------------------
*/
Route::get('/student/notifications', function (Request $request) {
    $studentId = $request->query('student_id');

    $query = StudentNotification::latest();

    if ($studentId) {
        $query->where('student_id', $studentId);
    }

    return $query->get();
});

Route::post('/student/notifications/{notification}/read', function (StudentNotification $notification) {
    $notification->update([
        'is_read' => true,
    ]);

    return response()->json($notification);
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

Route::get('/chat/threads/{thread}', function (ChatThread $thread) {
    return $thread->load(['student', 'teacher', 'invoice', 'messages.sender']);
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
| MATERIALS / STUDENT FEATURES
|--------------------------------------------------------------------------
*/
Route::get('/materials', fn () => Material::with('lesson')->latest()->get());
Route::get('/certificates', fn () => Certificate::with(['student', 'lesson'])->latest()->get());
Route::get('/projects', fn () => StudentProject::with(['student', 'lesson'])->latest()->get());
Route::get('/attendances', fn () => Attendance::with(['student', 'schedule'])->latest()->get());
Route::get('/progress-records', fn () => ProgressRecord::with(['student', 'lesson', 'teacher'])->latest()->get());

/*
|--------------------------------------------------------------------------
| MANAGEMENT / TRANSAKSI / REPORT
|--------------------------------------------------------------------------
*/
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
Route::get('/reports', fn () => Report::latest()->get());
Route::get('/laporan', fn () => Report::latest()->get());

/*
|--------------------------------------------------------------------------
| QUICK CREATE FOR SIMPLE TABLES
|--------------------------------------------------------------------------
*/
Route::post('/announcements', fn (Request $request) => response()->json(Announcement::create($request->all()), 201));
Route::post('/events', fn (Request $request) => response()->json(Event::create($request->all()), 201));
Route::post('/help-tickets', fn (Request $request) => response()->json(HelpTicket::create($request->all()), 201));
Route::post('/financial-transactions', fn (Request $request) => response()->json(FinancialTransaction::create($request->all()), 201));
Route::post('/payrolls', fn (Request $request) => response()->json(Payroll::create($request->all()), 201));
Route::post('/reimbursements', fn (Request $request) => response()->json(ReimbursementRequest::create($request->all()), 201));
