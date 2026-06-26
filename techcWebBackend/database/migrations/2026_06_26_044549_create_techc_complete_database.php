<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function addColumn(string $tableName, string $columnName, callable $callback): void
    {
        if (Schema::hasTable($tableName) && !Schema::hasColumn($tableName, $columnName)) {
            Schema::table($tableName, function (Blueprint $table) use ($callback) {
                $callback($table);
            });
        }
    }

    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | USERS PATCH
        |--------------------------------------------------------------------------
        */
        $this->addColumn('users', 'role', fn (Blueprint $table) => $table->string('role')->default('admin')->after('password'));
        $this->addColumn('users', 'phone', fn (Blueprint $table) => $table->string('phone')->nullable()->after('email'));
        $this->addColumn('users', 'bio', fn (Blueprint $table) => $table->text('bio')->nullable()->after('phone'));
        $this->addColumn('users', 'country', fn (Blueprint $table) => $table->string('country')->nullable()->after('bio'));
        $this->addColumn('users', 'city', fn (Blueprint $table) => $table->string('city')->nullable()->after('country'));
        $this->addColumn('users', 'postal_code', fn (Blueprint $table) => $table->string('postal_code')->nullable()->after('city'));
        $this->addColumn('users', 'tax_id', fn (Blueprint $table) => $table->string('tax_id')->nullable()->after('postal_code'));
        $this->addColumn('users', 'photo', fn (Blueprint $table) => $table->string('photo')->nullable()->after('tax_id'));

        /*
        |--------------------------------------------------------------------------
        | SCHOOLS
        |--------------------------------------------------------------------------
        */
        if (!Schema::hasTable('schools')) {
            Schema::create('schools', function (Blueprint $table) {
                $table->id();
                $table->string('nama')->nullable();
                $table->string('slug')->unique()->nullable();
                $table->string('kode')->nullable();
                $table->string('logo')->nullable();
                $table->string('jenjang')->nullable();
                $table->text('alamat')->nullable();
                $table->string('kota')->nullable();
                $table->string('provinsi')->nullable();
                $table->string('pic_name')->nullable();
                $table->string('pic_phone')->nullable();
                $table->string('email')->nullable();
                $table->string('status')->default('Aktif');
                $table->text('catatan')->nullable();
                $table->timestamps();
            });
        } else {
            $this->addColumn('schools', 'nama', fn (Blueprint $table) => $table->string('nama')->nullable()->after('id'));
            $this->addColumn('schools', 'slug', fn (Blueprint $table) => $table->string('slug')->unique()->nullable()->after('nama'));
            $this->addColumn('schools', 'kode', fn (Blueprint $table) => $table->string('kode')->nullable()->after('slug'));
            $this->addColumn('schools', 'logo', fn (Blueprint $table) => $table->string('logo')->nullable()->after('kode'));
            $this->addColumn('schools', 'jenjang', fn (Blueprint $table) => $table->string('jenjang')->nullable()->after('logo'));
            $this->addColumn('schools', 'alamat', fn (Blueprint $table) => $table->text('alamat')->nullable()->after('jenjang'));
            $this->addColumn('schools', 'kota', fn (Blueprint $table) => $table->string('kota')->nullable()->after('alamat'));
            $this->addColumn('schools', 'provinsi', fn (Blueprint $table) => $table->string('provinsi')->nullable()->after('kota'));
            $this->addColumn('schools', 'pic_name', fn (Blueprint $table) => $table->string('pic_name')->nullable()->after('provinsi'));
            $this->addColumn('schools', 'pic_phone', fn (Blueprint $table) => $table->string('pic_phone')->nullable()->after('pic_name'));
            $this->addColumn('schools', 'email', fn (Blueprint $table) => $table->string('email')->nullable()->after('pic_phone'));
            $this->addColumn('schools', 'status', fn (Blueprint $table) => $table->string('status')->default('Aktif')->after('email'));
            $this->addColumn('schools', 'catatan', fn (Blueprint $table) => $table->text('catatan')->nullable()->after('status'));
        }

        /*
        |--------------------------------------------------------------------------
        | PLACES
        |--------------------------------------------------------------------------
        */
        if (!Schema::hasTable('places')) {
            Schema::create('places', function (Blueprint $table) {
                $table->id();
                $table->foreignId('school_id')->nullable()->constrained('schools')->nullOnDelete();
                $table->string('nama');
                $table->string('tipe')->nullable();
                $table->integer('kapasitas')->default(0);
                $table->text('alamat')->nullable();
                $table->string('status')->default('Aktif');
                $table->text('catatan')->nullable();
                $table->timestamps();
            });
        }

        /*
        |--------------------------------------------------------------------------
        | PACKAGES
        |--------------------------------------------------------------------------
        */
        if (!Schema::hasTable('packages')) {
            Schema::create('packages', function (Blueprint $table) {
                $table->id();
                $table->string('nama');
                $table->string('kategori')->nullable();
                $table->integer('jumlah_pertemuan')->default(4);
                $table->integer('harga_per_pertemuan')->default(0);
                $table->integer('harga_paket')->default(0);
                $table->text('deskripsi')->nullable();
                $table->json('benefits')->nullable();
                $table->string('status')->default('Aktif');
                $table->timestamps();
            });
        }

        /*
        |--------------------------------------------------------------------------
        | TEACHERS
        |--------------------------------------------------------------------------
        */
        if (!Schema::hasTable('teachers')) {
            Schema::create('teachers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('name');
                $table->string('email')->nullable();
                $table->string('phone')->nullable();
                $table->string('photo')->nullable();
                $table->string('specialization')->nullable();
                $table->json('skills')->nullable();
                $table->string('address')->nullable();
                $table->date('join_date')->nullable();
                $table->integer('salary_base')->default(0);
                $table->integer('fee_per_session')->default(0);
                $table->string('status')->default('Aktif');
                $table->text('catatan')->nullable();
                $table->timestamps();
            });
        }

        /*
        |--------------------------------------------------------------------------
        | STUDENTS
        |--------------------------------------------------------------------------
        */
        if (!Schema::hasTable('students')) {
            Schema::create('students', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('school_id')->nullable()->constrained('schools')->nullOnDelete();
                $table->foreignId('package_id')->nullable()->constrained('packages')->nullOnDelete();
                $table->string('name')->nullable();
                $table->string('nis')->nullable();
                $table->string('photo')->nullable();
                $table->string('kelas')->nullable();
                $table->string('kategori_level')->nullable();
                $table->string('jenis_kelamin')->nullable();
                $table->date('tanggal_lahir')->nullable();
                $table->string('parent_name')->nullable();
                $table->string('parent_phone')->nullable();
                $table->string('parent_email')->nullable();
                $table->string('student_type')->default('sekolah');
                $table->integer('progress_belajar')->default(0);
                $table->integer('tagihan')->default(0);
                $table->json('jadwal')->nullable();
                $table->text('pengumuman')->nullable();
                $table->text('catatan')->nullable();
                $table->string('status')->default('Aktif');
                $table->timestamps();
            });
        } else {
            $this->addColumn('students', 'user_id', fn (Blueprint $table) => $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete()->after('id'));
            $this->addColumn('students', 'school_id', fn (Blueprint $table) => $table->foreignId('school_id')->nullable()->constrained('schools')->nullOnDelete()->after('user_id'));
            $this->addColumn('students', 'package_id', fn (Blueprint $table) => $table->foreignId('package_id')->nullable()->constrained('packages')->nullOnDelete()->after('school_id'));
            $this->addColumn('students', 'name', fn (Blueprint $table) => $table->string('name')->nullable()->after('package_id'));
            $this->addColumn('students', 'nis', fn (Blueprint $table) => $table->string('nis')->nullable()->after('name'));
            $this->addColumn('students', 'photo', fn (Blueprint $table) => $table->string('photo')->nullable()->after('nis'));
            $this->addColumn('students', 'kelas', fn (Blueprint $table) => $table->string('kelas')->nullable()->after('photo'));
            $this->addColumn('students', 'kategori_level', fn (Blueprint $table) => $table->string('kategori_level')->nullable()->after('kelas'));
            $this->addColumn('students', 'jenis_kelamin', fn (Blueprint $table) => $table->string('jenis_kelamin')->nullable()->after('kategori_level'));
            $this->addColumn('students', 'tanggal_lahir', fn (Blueprint $table) => $table->date('tanggal_lahir')->nullable()->after('jenis_kelamin'));
            $this->addColumn('students', 'parent_name', fn (Blueprint $table) => $table->string('parent_name')->nullable()->after('tanggal_lahir'));
            $this->addColumn('students', 'parent_phone', fn (Blueprint $table) => $table->string('parent_phone')->nullable()->after('parent_name'));
            $this->addColumn('students', 'parent_email', fn (Blueprint $table) => $table->string('parent_email')->nullable()->after('parent_phone'));
            $this->addColumn('students', 'student_type', fn (Blueprint $table) => $table->string('student_type')->default('sekolah')->after('parent_email'));
            $this->addColumn('students', 'progress_belajar', fn (Blueprint $table) => $table->integer('progress_belajar')->default(0)->after('student_type'));
            $this->addColumn('students', 'tagihan', fn (Blueprint $table) => $table->integer('tagihan')->default(0)->after('progress_belajar'));
            $this->addColumn('students', 'jadwal', fn (Blueprint $table) => $table->json('jadwal')->nullable()->after('tagihan'));
            $this->addColumn('students', 'pengumuman', fn (Blueprint $table) => $table->text('pengumuman')->nullable()->after('jadwal'));
            $this->addColumn('students', 'catatan', fn (Blueprint $table) => $table->text('catatan')->nullable()->after('pengumuman'));
            $this->addColumn('students', 'status', fn (Blueprint $table) => $table->string('status')->default('Aktif')->after('catatan'));
        }

        /*
        |--------------------------------------------------------------------------
        | PERSONAL STUDENTS
        |--------------------------------------------------------------------------
        */
        if (!Schema::hasTable('personal_students')) {
            Schema::create('personal_students', function (Blueprint $table) {
                $table->id();
                $table->foreignId('student_id')->nullable()->constrained('students')->nullOnDelete();
                $table->string('nama');
                $table->string('kelas')->nullable();
                $table->string('asal_sekolah')->nullable();
                $table->string('phone')->nullable();
                $table->string('parent_name')->nullable();
                $table->string('parent_phone')->nullable();
                $table->string('alamat')->nullable();
                $table->string('status')->default('Aktif');
                $table->text('catatan')->nullable();
                $table->timestamps();
            });
        }

        /*
        |--------------------------------------------------------------------------
        | LESSON CATEGORIES
        |--------------------------------------------------------------------------
        */
        if (!Schema::hasTable('lesson_categories')) {
            Schema::create('lesson_categories', function (Blueprint $table) {
                $table->id();
                $table->string('nama');
                $table->string('slug')->unique();
                $table->string('icon')->nullable();
                $table->string('color')->nullable();
                $table->integer('sort_order')->default(0);
                $table->string('status')->default('Aktif');
                $table->timestamps();
            });
        }

        /*
        |--------------------------------------------------------------------------
        | LESSONS
        |--------------------------------------------------------------------------
        */
        if (!Schema::hasTable('lessons')) {
            Schema::create('lessons', function (Blueprint $table) {
                $table->id();
                $table->foreignId('lesson_category_id')->nullable()->constrained('lesson_categories')->nullOnDelete();
                $table->string('title')->nullable();
                $table->string('slug')->unique()->nullable();
                $table->string('category')->nullable();
                $table->string('level')->default('Beginner');
                $table->string('duration')->nullable();
                $table->string('image')->nullable();
                $table->json('tools')->nullable();
                $table->json('topics')->nullable();
                $table->text('description')->nullable();
                $table->text('output')->nullable();
                $table->integer('sort_order')->default(0);
                $table->string('status')->default('Aktif');
                $table->timestamps();
            });
        } else {
            $this->addColumn('lessons', 'lesson_category_id', fn (Blueprint $table) => $table->foreignId('lesson_category_id')->nullable()->constrained('lesson_categories')->nullOnDelete()->after('id'));
            $this->addColumn('lessons', 'title', fn (Blueprint $table) => $table->string('title')->nullable()->after('lesson_category_id'));
            $this->addColumn('lessons', 'slug', fn (Blueprint $table) => $table->string('slug')->unique()->nullable()->after('title'));
            $this->addColumn('lessons', 'category', fn (Blueprint $table) => $table->string('category')->nullable()->after('slug'));
            $this->addColumn('lessons', 'level', fn (Blueprint $table) => $table->string('level')->default('Beginner')->after('category'));
            $this->addColumn('lessons', 'duration', fn (Blueprint $table) => $table->string('duration')->nullable()->after('level'));
            $this->addColumn('lessons', 'image', fn (Blueprint $table) => $table->string('image')->nullable()->after('duration'));
            $this->addColumn('lessons', 'tools', fn (Blueprint $table) => $table->json('tools')->nullable()->after('image'));
            $this->addColumn('lessons', 'topics', fn (Blueprint $table) => $table->json('topics')->nullable()->after('tools'));
            $this->addColumn('lessons', 'description', fn (Blueprint $table) => $table->text('description')->nullable()->after('topics'));
            $this->addColumn('lessons', 'output', fn (Blueprint $table) => $table->text('output')->nullable()->after('description'));
            $this->addColumn('lessons', 'sort_order', fn (Blueprint $table) => $table->integer('sort_order')->default(0)->after('output'));
            $this->addColumn('lessons', 'status', fn (Blueprint $table) => $table->string('status')->default('Aktif')->after('sort_order'));
        }

        /*
        |--------------------------------------------------------------------------
        | MATERIALS
        |--------------------------------------------------------------------------
        */
        if (!Schema::hasTable('materials')) {
            Schema::create('materials', function (Blueprint $table) {
                $table->id();
                $table->foreignId('lesson_id')->nullable()->constrained('lessons')->nullOnDelete();
                $table->string('title');
                $table->string('type')->default('file');
                $table->string('file')->nullable();
                $table->string('url')->nullable();
                $table->text('content')->nullable();
                $table->string('status')->default('Aktif');
                $table->timestamps();
            });
        }

        /*
        |--------------------------------------------------------------------------
        | SCHEDULES
        |--------------------------------------------------------------------------
        */
        if (!Schema::hasTable('schedules')) {
            Schema::create('schedules', function (Blueprint $table) {
                $table->id();
                $table->foreignId('school_id')->nullable()->constrained('schools')->nullOnDelete();
                $table->foreignId('place_id')->nullable()->constrained('places')->nullOnDelete();
                $table->foreignId('teacher_id')->nullable()->constrained('teachers')->nullOnDelete();
                $table->foreignId('lesson_id')->nullable()->constrained('lessons')->nullOnDelete();
                $table->foreignId('package_id')->nullable()->constrained('packages')->nullOnDelete();
                $table->string('title');
                $table->string('kelas')->nullable();
                $table->string('kategori_level')->nullable();
                $table->string('hari')->nullable();
                $table->date('tanggal')->nullable();
                $table->time('jam_mulai')->nullable();
                $table->time('jam_selesai')->nullable();
                $table->string('room')->nullable();
                $table->string('status')->default('Aktif');
                $table->text('catatan')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('schedule_student')) {
            Schema::create('schedule_student', function (Blueprint $table) {
                $table->id();
                $table->foreignId('schedule_id')->constrained('schedules')->cascadeOnDelete();
                $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
                $table->string('status')->default('Aktif');
                $table->timestamps();

                $table->unique(['schedule_id', 'student_id']);
            });
        }

        /*
        |--------------------------------------------------------------------------
        | ATTENDANCES
        |--------------------------------------------------------------------------
        */
        if (!Schema::hasTable('attendances')) {
            Schema::create('attendances', function (Blueprint $table) {
                $table->id();
                $table->foreignId('schedule_id')->nullable()->constrained('schedules')->nullOnDelete();
                $table->foreignId('student_id')->nullable()->constrained('students')->nullOnDelete();
                $table->foreignId('teacher_id')->nullable()->constrained('teachers')->nullOnDelete();
                $table->date('tanggal');
                $table->string('status')->default('Hadir');
                $table->text('catatan')->nullable();
                $table->timestamps();
            });
        }

        /*
        |--------------------------------------------------------------------------
        | PROGRESS RECORDS
        |--------------------------------------------------------------------------
        */
        if (!Schema::hasTable('progress_records')) {
            Schema::create('progress_records', function (Blueprint $table) {
                $table->id();
                $table->foreignId('student_id')->nullable()->constrained('students')->nullOnDelete();
                $table->foreignId('teacher_id')->nullable()->constrained('teachers')->nullOnDelete();
                $table->foreignId('lesson_id')->nullable()->constrained('lessons')->nullOnDelete();
                $table->integer('pertemuan_ke')->default(1);
                $table->integer('progress')->default(0);
                $table->string('predikat')->nullable();
                $table->text('deskripsi')->nullable();
                $table->text('catatan_guru')->nullable();
                $table->date('tanggal')->nullable();
                $table->timestamps();
            });
        }

        /*
        |--------------------------------------------------------------------------
        | STUDENT PROJECTS
        |--------------------------------------------------------------------------
        */
        if (!Schema::hasTable('student_projects')) {
            Schema::create('student_projects', function (Blueprint $table) {
                $table->id();
                $table->foreignId('student_id')->nullable()->constrained('students')->nullOnDelete();
                $table->foreignId('lesson_id')->nullable()->constrained('lessons')->nullOnDelete();
                $table->string('title');
                $table->text('description')->nullable();
                $table->string('file')->nullable();
                $table->string('url')->nullable();
                $table->string('status')->default('Draft');
                $table->integer('score')->nullable();
                $table->text('feedback')->nullable();
                $table->timestamps();
            });
        }

        /*
        |--------------------------------------------------------------------------
        | CERTIFICATES
        |--------------------------------------------------------------------------
        */
        if (!Schema::hasTable('certificates')) {
            Schema::create('certificates', function (Blueprint $table) {
                $table->id();
                $table->foreignId('student_id')->nullable()->constrained('students')->nullOnDelete();
                $table->foreignId('lesson_id')->nullable()->constrained('lessons')->nullOnDelete();
                $table->string('certificate_no')->unique();
                $table->string('title');
                $table->date('issued_at')->nullable();
                $table->string('file')->nullable();
                $table->string('status')->default('Terbit');
                $table->timestamps();
            });
        }

        /*
        |--------------------------------------------------------------------------
        | INVOICES
        |--------------------------------------------------------------------------
        */
        if (!Schema::hasTable('invoices')) {
            Schema::create('invoices', function (Blueprint $table) {
                $table->id();
                $table->foreignId('student_id')->nullable()->constrained('students')->nullOnDelete();
                $table->foreignId('school_id')->nullable()->constrained('schools')->nullOnDelete();
                $table->foreignId('package_id')->nullable()->constrained('packages')->nullOnDelete();
                $table->string('invoice_no')->unique();
                $table->date('date');
                $table->date('due_date')->nullable();
                $table->string('student_name')->nullable();
                $table->string('student_class')->nullable();
                $table->string('student_school')->nullable();
                $table->string('package_name')->nullable();
                $table->integer('meeting_count')->default(0);
                $table->integer('price_per_meeting')->default(0);
                $table->integer('main_total')->default(0);
                $table->integer('extra_fee')->default(0);
                $table->integer('subtotal')->default(0);
                $table->integer('total')->default(0);
                $table->json('extra_items')->nullable();
                $table->text('note')->nullable();
                $table->string('status')->default('Belum Dibayar');
                $table->string('invoice_image')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('invoice_items')) {
            Schema::create('invoice_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
                $table->string('description');
                $table->integer('price')->default(0);
                $table->integer('qty')->default(1);
                $table->integer('total')->default(0);
                $table->timestamps();
            });
        }

        /*
        |--------------------------------------------------------------------------
        | RECURRING INVOICES
        |--------------------------------------------------------------------------
        */
        if (!Schema::hasTable('recurring_invoices')) {
            Schema::create('recurring_invoices', function (Blueprint $table) {
                $table->id();
                $table->foreignId('student_id')->nullable()->constrained('students')->nullOnDelete();
                $table->foreignId('package_id')->nullable()->constrained('packages')->nullOnDelete();
                $table->string('title');
                $table->integer('meeting_count')->default(4);
                $table->integer('price_per_meeting')->default(0);
                $table->integer('extra_fee')->default(0);
                $table->string('frequency')->default('monthly');
                $table->date('start_date')->nullable();
                $table->date('next_generate_date')->nullable();
                $table->string('status')->default('Aktif');
                $table->json('extra_items')->nullable();
                $table->text('note')->nullable();
                $table->timestamps();
            });
        }

        /*
        |--------------------------------------------------------------------------
        | PAYMENTS
        |--------------------------------------------------------------------------
        */
        if (!Schema::hasTable('payments')) {
            Schema::create('payments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
                $table->foreignId('student_id')->nullable()->constrained('students')->nullOnDelete();
                $table->date('payment_date')->nullable();
                $table->integer('amount')->default(0);
                $table->string('method')->nullable();
                $table->string('proof_file')->nullable();
                $table->string('status')->default('Pending');
                $table->text('note')->nullable();
                $table->timestamps();
            });
        }

        /*
        |--------------------------------------------------------------------------
        | FINANCIAL TRANSACTIONS
        |--------------------------------------------------------------------------
        */
        if (!Schema::hasTable('financial_transactions')) {
            Schema::create('financial_transactions', function (Blueprint $table) {
                $table->id();
                $table->string('type');
                $table->string('category')->nullable();
                $table->string('title');
                $table->integer('amount')->default(0);
                $table->date('transaction_date')->nullable();
                $table->string('method')->nullable();
                $table->string('reference_no')->nullable();
                $table->text('note')->nullable();
                $table->timestamps();
            });
        }

        /*
        |--------------------------------------------------------------------------
        | PAYROLLS
        |--------------------------------------------------------------------------
        */
        if (!Schema::hasTable('payrolls')) {
            Schema::create('payrolls', function (Blueprint $table) {
                $table->id();
                $table->foreignId('teacher_id')->nullable()->constrained('teachers')->nullOnDelete();
                $table->string('month');
                $table->integer('total_sessions')->default(0);
                $table->integer('fee_per_session')->default(0);
                $table->integer('bonus')->default(0);
                $table->integer('deduction')->default(0);
                $table->integer('total_salary')->default(0);
                $table->string('status')->default('Belum Dibayar');
                $table->date('paid_at')->nullable();
                $table->text('note')->nullable();
                $table->timestamps();
            });
        }

        /*
        |--------------------------------------------------------------------------
        | REIMBURSEMENTS
        |--------------------------------------------------------------------------
        */
        if (!Schema::hasTable('reimbursement_requests')) {
            Schema::create('reimbursement_requests', function (Blueprint $table) {
                $table->id();
                $table->foreignId('teacher_id')->nullable()->constrained('teachers')->nullOnDelete();
                $table->foreignId('student_id')->nullable()->constrained('students')->nullOnDelete();
                $table->string('title');
                $table->integer('amount')->default(0);
                $table->date('request_date')->nullable();
                $table->string('proof_file')->nullable();
                $table->string('status')->default('Pending');
                $table->text('description')->nullable();
                $table->text('admin_note')->nullable();
                $table->timestamps();
            });
        }

        /*
        |--------------------------------------------------------------------------
        | INVENTORY
        |--------------------------------------------------------------------------
        */
        if (!Schema::hasTable('inventory_items')) {
            Schema::create('inventory_items', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('category')->nullable();
                $table->integer('qty')->default(0);
                $table->integer('price')->default(0);
                $table->string('condition')->default('Baik');
                $table->string('location')->nullable();
                $table->string('photo')->nullable();
                $table->text('note')->nullable();
                $table->timestamps();
            });
        }

        /*
        |--------------------------------------------------------------------------
        | ANNOUNCEMENTS
        |--------------------------------------------------------------------------
        */
        if (!Schema::hasTable('announcements')) {
            Schema::create('announcements', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->text('content');
                $table->string('target_role')->default('all');
                $table->foreignId('school_id')->nullable()->constrained('schools')->nullOnDelete();
                $table->dateTime('published_at')->nullable();
                $table->string('status')->default('Published');
                $table->timestamps();
            });
        }

        /*
        |--------------------------------------------------------------------------
        | NOTIFICATIONS
        |--------------------------------------------------------------------------
        */
        if (!Schema::hasTable('student_notifications')) {
            Schema::create('student_notifications', function (Blueprint $table) {
                $table->id();
                $table->foreignId('student_id')->nullable()->constrained('students')->nullOnDelete();
                $table->string('type')->default('info');
                $table->string('title');
                $table->text('message')->nullable();
                $table->string('url')->nullable();
                $table->boolean('is_read')->default(false);
                $table->timestamps();
            });
        }

        /*
        |--------------------------------------------------------------------------
        | CHAT
        |--------------------------------------------------------------------------
        */
        if (!Schema::hasTable('chat_threads')) {
            Schema::create('chat_threads', function (Blueprint $table) {
                $table->id();
                $table->foreignId('student_id')->nullable()->constrained('students')->nullOnDelete();
                $table->foreignId('teacher_id')->nullable()->constrained('teachers')->nullOnDelete();
                $table->foreignId('invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
                $table->string('subject')->nullable();
                $table->string('status')->default('Open');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('chat_messages')) {
            Schema::create('chat_messages', function (Blueprint $table) {
                $table->id();
                $table->foreignId('chat_thread_id')->constrained('chat_threads')->cascadeOnDelete();
                $table->foreignId('sender_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('sender_role')->default('siswa');
                $table->text('message');
                $table->string('attachment')->nullable();
                $table->boolean('is_read')->default(false);
                $table->timestamps();
            });
        }

        /*
        |--------------------------------------------------------------------------
        | EVENTS
        |--------------------------------------------------------------------------
        */
        if (!Schema::hasTable('events')) {
            Schema::create('events', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->string('type')->nullable();
                $table->text('description')->nullable();
                $table->date('event_date')->nullable();
                $table->string('location')->nullable();
                $table->string('poster')->nullable();
                $table->string('status')->default('Aktif');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('event_participants')) {
            Schema::create('event_participants', function (Blueprint $table) {
                $table->id();
                $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
                $table->foreignId('student_id')->nullable()->constrained('students')->nullOnDelete();
                $table->string('status')->default('Registered');
                $table->timestamps();
            });
        }

        /*
        |--------------------------------------------------------------------------
        | HELP TICKETS
        |--------------------------------------------------------------------------
        */
        if (!Schema::hasTable('help_tickets')) {
            Schema::create('help_tickets', function (Blueprint $table) {
                $table->id();
                $table->foreignId('student_id')->nullable()->constrained('students')->nullOnDelete();
                $table->string('subject');
                $table->text('message');
                $table->string('category')->default('Umum');
                $table->string('status')->default('Open');
                $table->text('admin_reply')->nullable();
                $table->timestamps();
            });
        }

        /*
        |--------------------------------------------------------------------------
        | REPORTS
        |--------------------------------------------------------------------------
        */
        if (!Schema::hasTable('reports')) {
            Schema::create('reports', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->string('type')->nullable();
                $table->date('period_start')->nullable();
                $table->date('period_end')->nullable();
                $table->json('data')->nullable();
                $table->string('file')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
        Schema::dropIfExists('help_tickets');
        Schema::dropIfExists('event_participants');
        Schema::dropIfExists('events');
        Schema::dropIfExists('chat_messages');
        Schema::dropIfExists('chat_threads');
        Schema::dropIfExists('student_notifications');
        Schema::dropIfExists('announcements');
        Schema::dropIfExists('inventory_items');
        Schema::dropIfExists('reimbursement_requests');
        Schema::dropIfExists('payrolls');
        Schema::dropIfExists('financial_transactions');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('recurring_invoices');
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('certificates');
        Schema::dropIfExists('student_projects');
        Schema::dropIfExists('progress_records');
        Schema::dropIfExists('attendances');
        Schema::dropIfExists('schedule_student');
        Schema::dropIfExists('schedules');
        Schema::dropIfExists('materials');
        Schema::dropIfExists('lesson_categories');
        Schema::dropIfExists('personal_students');
        Schema::dropIfExists('teachers');
        Schema::dropIfExists('packages');
        Schema::dropIfExists('places');

        // Sengaja tidak drop users, schools, students, lessons
        // karena table itu sudah dibuat migration lama.
    }
};