<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('server', function (Blueprint $table) {
            if (! Schema::hasColumn('server', 'rack_size_u')) {
                $table->unsignedSmallInteger('rack_size_u')->nullable()->after('rack_id');
            }
        });

        Schema::table('aplikasi', function (Blueprint $table) {
            if (! Schema::hasColumn('aplikasi', 'pengembang')) {
                $table->string('pengembang', 50)->nullable()->after('jenis_aplikasi');
            }
        });
    }

    public function down(): void
    {
        Schema::table('aplikasi', function (Blueprint $table) {
            if (Schema::hasColumn('aplikasi', 'pengembang')) {
                $table->dropColumn('pengembang');
            }
        });

        Schema::table('server', function (Blueprint $table) {
            if (Schema::hasColumn('server', 'rack_size_u')) {
                $table->dropColumn('rack_size_u');
            }
        });
    }
};
