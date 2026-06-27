<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('attendances')) {
            Schema::create('attendances', function (Blueprint $table) {
                $table->id();
                $table->date('attendance_date')->nullable();
                $table->time('attendance_time')->nullable();
                $table->string('member_id')->nullable();
                $table->string('name')->nullable();
                $table->string('status')->default('HADIR');
                $table->string('source')->default('web');
                $table->string('device_name')->nullable();
                $table->text('note')->nullable();
                $table->string('unique_hash')->nullable()->unique();
                $table->json('raw_payload')->nullable();
                $table->timestamps();
            });

            return;
        }

        Schema::table('attendances', function (Blueprint $table) {
            if (!Schema::hasColumn('attendances', 'attendance_date')) {
                $table->date('attendance_date')->nullable()->after('id');
            }

            if (!Schema::hasColumn('attendances', 'attendance_time')) {
                $table->time('attendance_time')->nullable()->after('attendance_date');
            }

            if (!Schema::hasColumn('attendances', 'member_id')) {
                $table->string('member_id')->nullable()->after('attendance_time');
            }

            if (!Schema::hasColumn('attendances', 'name')) {
                $table->string('name')->nullable()->after('member_id');
            }

            if (!Schema::hasColumn('attendances', 'status')) {
                $table->string('status')->default('HADIR')->after('name');
            }

            if (!Schema::hasColumn('attendances', 'source')) {
                $table->string('source')->default('web')->after('status');
            }

            if (!Schema::hasColumn('attendances', 'device_name')) {
                $table->string('device_name')->nullable()->after('source');
            }

            if (!Schema::hasColumn('attendances', 'note')) {
                $table->text('note')->nullable()->after('device_name');
            }

            if (!Schema::hasColumn('attendances', 'unique_hash')) {
                $table->string('unique_hash')->nullable()->after('note');
            }

            if (!Schema::hasColumn('attendances', 'raw_payload')) {
                $table->json('raw_payload')->nullable()->after('unique_hash');
            }
        });

        try {
            Schema::table('attendances', function (Blueprint $table) {
                $table->unique('unique_hash', 'attendances_unique_hash_unique');
            });
        } catch (\Throwable $e) {
            // Kalau index sudah ada, abaikan.
        }
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            try {
                $table->dropUnique('attendances_unique_hash_unique');
            } catch (\Throwable $e) {
                //
            }

            if (Schema::hasColumn('attendances', 'raw_payload')) {
                $table->dropColumn('raw_payload');
            }

            if (Schema::hasColumn('attendances', 'unique_hash')) {
                $table->dropColumn('unique_hash');
            }

            if (Schema::hasColumn('attendances', 'note')) {
                $table->dropColumn('note');
            }

            if (Schema::hasColumn('attendances', 'device_name')) {
                $table->dropColumn('device_name');
            }

            if (Schema::hasColumn('attendances', 'source')) {
                $table->dropColumn('source');
            }
        });
    }
};