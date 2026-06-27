<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'photo')) {
                $table->string('photo')->nullable()->after('role');
            }

            if (!Schema::hasColumn('users', 'phone')) {
                $table->string('phone')->nullable()->after('photo');
            }

            if (!Schema::hasColumn('users', 'whatsapp')) {
                $table->string('whatsapp')->nullable()->after('phone');
            }

            if (!Schema::hasColumn('users', 'position')) {
                $table->string('position')->nullable()->after('whatsapp');
            }

            if (!Schema::hasColumn('users', 'address')) {
                $table->text('address')->nullable()->after('position');
            }

            if (!Schema::hasColumn('users', 'bio')) {
                $table->longText('bio')->nullable()->after('address');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'photo')) {
                $table->dropColumn('photo');
            }

            if (Schema::hasColumn('users', 'phone')) {
                $table->dropColumn('phone');
            }

            if (Schema::hasColumn('users', 'whatsapp')) {
                $table->dropColumn('whatsapp');
            }

            if (Schema::hasColumn('users', 'position')) {
                $table->dropColumn('position');
            }

            if (Schema::hasColumn('users', 'address')) {
                $table->dropColumn('address');
            }

            if (Schema::hasColumn('users', 'bio')) {
                $table->dropColumn('bio');
            }
        });
    }
};