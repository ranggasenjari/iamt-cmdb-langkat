<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consumer_network_monitorings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('asset_code', 50)->nullable()->unique();
            $table->uuid('site_id')->index();
            $table->dateTime('monitoring_at')->index();
            $table->string('period_month', 7)->nullable()->index();
            $table->json('officers')->nullable();
            $table->decimal('speedtest_download_mbps', 10, 2)->nullable();
            $table->decimal('speedtest_upload_mbps', 10, 2)->nullable();
            $table->decimal('speedtest_ping_ms', 10, 2)->nullable();
            $table->boolean('tower_available')->default(false)->index();
            $table->enum('tower_besi_condition', ['baik', 'kurang_baik', 'rusak'])->nullable();
            $table->enum('tower_kawat_condition', ['baik', 'kurang_baik', 'rusak'])->nullable();
            $table->enum('tower_pondasi_condition', ['baik', 'kurang_baik', 'rusak'])->nullable();
            $table->text('tower_notes')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('site_id')->references('id')->on('consumer_network_sites');
        });

        Schema::create('consumer_network_monitoring_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('monitoring_id')->index();
            $table->uuid('device_id')->index();
            $table->uuid('installation_id')->nullable()->index();
            $table->enum('condition', ['baik', 'kurang_baik', 'rusak']);
            $table->text('note')->nullable();
            $table->timestamps();

            $table->foreign('monitoring_id')
                ->references('id')
                ->on('consumer_network_monitorings')
                ->cascadeOnDelete();
            $table->foreign('device_id')->references('id')->on('consumer_network_devices');
            $table->foreign('installation_id')
                ->references('id')
                ->on('consumer_network_installations')
                ->nullOnDelete();
        });

        Schema::create('consumer_network_monitoring_attachments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('monitoring_id')->index();
            $table->string('path');
            $table->string('original_name');
            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('size_bytes')->nullable();
            $table->timestamps();

            $table->foreign('monitoring_id')
                ->references('id')
                ->on('consumer_network_monitorings')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consumer_network_monitoring_attachments');
        Schema::dropIfExists('consumer_network_monitoring_items');
        Schema::dropIfExists('consumer_network_monitorings');
    }
};
