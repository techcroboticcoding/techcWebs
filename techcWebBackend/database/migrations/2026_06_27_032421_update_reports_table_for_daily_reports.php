<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('reports')) {
            Schema::create('reports', function (Blueprint $table) {
                $table->id();
                $table->date('report_date')->nullable();
                $table->string('partner_name')->nullable();
                $table->string('title')->nullable();
                $table->string('category')->nullable();
                $table->longText('content')->nullable();
                $table->string('status')->default('Selesai');
                $table->string('reporter_name')->nullable();
                $table->string('reporter_email')->nullable();
                $table->timestamps();
            });

            return;
        }

        Schema::table('reports', function (Blueprint $table) {
            if (!Schema::hasColumn('reports', 'report_date')) {
                $table->date('report_date')->nullable()->after('id');
            }

            if (!Schema::hasColumn('reports', 'partner_name')) {
                $table->string('partner_name')->nullable()->after('report_date');
            }

            if (!Schema::hasColumn('reports', 'title')) {
                $table->string('title')->nullable()->after('partner_name');
            }

            if (!Schema::hasColumn('reports', 'category')) {
                $table->string('category')->nullable()->after('title');
            }

            if (!Schema::hasColumn('reports', 'content')) {
                $table->longText('content')->nullable()->after('category');
            }

            if (!Schema::hasColumn('reports', 'status')) {
                $table->string('status')->default('Selesai')->after('content');
            }

            if (!Schema::hasColumn('reports', 'reporter_name')) {
                $table->string('reporter_name')->nullable()->after('status');
            }

            if (!Schema::hasColumn('reports', 'reporter_email')) {
                $table->string('reporter_email')->nullable()->after('reporter_name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            if (Schema::hasColumn('reports', 'report_date')) {
                $table->dropColumn('report_date');
            }

            if (Schema::hasColumn('reports', 'partner_name')) {
                $table->dropColumn('partner_name');
            }

            if (Schema::hasColumn('reports', 'title')) {
                $table->dropColumn('title');
            }

            if (Schema::hasColumn('reports', 'category')) {
                $table->dropColumn('category');
            }

            if (Schema::hasColumn('reports', 'content')) {
                $table->dropColumn('content');
            }

            if (Schema::hasColumn('reports', 'status')) {
                $table->dropColumn('status');
            }

            if (Schema::hasColumn('reports', 'reporter_name')) {
                $table->dropColumn('reporter_name');
            }

            if (Schema::hasColumn('reports', 'reporter_email')) {
                $table->dropColumn('reporter_email');
            }
        });
    }
};