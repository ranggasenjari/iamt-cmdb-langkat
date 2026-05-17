<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ip_address', function (Blueprint $table) {
            $table->string('assignment')->nullable()->after('jenis');
            $table->enum('ping_status', ['unknown', 'up', 'down'])->default('unknown')->after('isp_id');
            $table->decimal('ping_latency_ms', 10, 2)->nullable()->after('ping_status');
            $table->timestamp('ping_checked_at')->nullable()->after('ping_latency_ms');
        });
    }

    public function down(): void
    {
        Schema::table('ip_address', function (Blueprint $table) {
            $table->dropColumn([
                'assignment',
                'ping_status',
                'ping_latency_ms',
                'ping_checked_at',
            ]);
        });
    }
};
