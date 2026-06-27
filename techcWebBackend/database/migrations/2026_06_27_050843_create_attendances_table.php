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

                $table->string('source')->default('web'); // web, esp32, sheet
                $table->string('device_name')->nullable();
                $table->text('note')->nullable();

                $table->string('unique_hash')->nullable()->unique();
                $table->json('raw_payload')->nullable();

                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};