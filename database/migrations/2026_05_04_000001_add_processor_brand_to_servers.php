<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('server', function (Blueprint $table) {
            if (! Schema::hasColumn('server', 'merk_processor')) {
                $table->string('merk_processor', 100)->nullable()->after('serial_number');
            }
        });
    }

    public function down(): void
    {
        Schema::table('server', function (Blueprint $table) {
            if (Schema::hasColumn('server', 'merk_processor')) {
                $table->dropColumn('merk_processor');
            }
        });
    }
};
