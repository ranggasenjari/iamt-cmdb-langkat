<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('aplikasi', function (Blueprint $table) {
            $table->json('klasifikasi_fungsi')->nullable()->after('jenis_aplikasi');
        });
    }

    public function down(): void
    {
        Schema::table('aplikasi', function (Blueprint $table) {
            $table->dropColumn('klasifikasi_fungsi');
        });
    }
};
