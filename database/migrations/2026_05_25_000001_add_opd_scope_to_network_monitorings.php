<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('consumer_network_monitorings', function (Blueprint $table) {
            $table->dropForeign(['site_id']);
        });

        Schema::table('consumer_network_monitorings', function (Blueprint $table) {
            $table->uuid('site_id')->nullable()->change();
            $table->uuid('opd_id')->nullable()->after('site_id')->index();

            $table->foreign('site_id')
                ->references('id')
                ->on('consumer_network_sites')
                ->nullOnDelete();
            $table->foreign('opd_id')
                ->references('id')
                ->on('opd')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('consumer_network_monitorings', function (Blueprint $table) {
            $table->dropForeign(['site_id']);
            $table->dropForeign(['opd_id']);
        });

        Schema::table('consumer_network_monitorings', function (Blueprint $table) {
            $table->dropColumn('opd_id');
            $table->uuid('site_id')->nullable(false)->change();

            $table->foreign('site_id')
                ->references('id')
                ->on('consumer_network_sites');
        });
    }
};
