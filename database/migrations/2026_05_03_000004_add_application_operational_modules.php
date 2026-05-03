<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('aplikasi', function (Blueprint $table) {
            $table->text('tech_stack')->nullable()->after('jenis_aplikasi');
        });

        Schema::table('aplikasi_dokumen', function (Blueprint $table) {
            $table->string('document_category', 50)->nullable()->after('jenis');
            $table->string('path')->nullable()->after('url');
            $table->string('original_name')->nullable()->after('path');
            $table->string('mime_type', 100)->nullable()->after('original_name');
            $table->unsignedBigInteger('size_bytes')->nullable()->after('mime_type');
        });

        Schema::create('app_integrations', function (Blueprint $table) {
            $table->id();
            $table->uuid('aplikasi_id')->index();
            $table->text('deskripsi')->nullable();
            $table->enum('jenis_integrasi', ['proses_bisnis', 'berbagi_data']);
            $table->enum('metode_integrasi', ['spl', 'host_to_host']);
            $table->text('external_endpoints')->nullable();
            $table->timestamps();
            $table->foreign('aplikasi_id')->references('id')->on('aplikasi')->cascadeOnDelete();
        });

        Schema::create('app_integration_targets', function (Blueprint $table) {
            $table->foreignId('integration_id')->constrained('app_integrations')->cascadeOnDelete();
            $table->uuid('target_aplikasi_id');
            $table->primary(['integration_id', 'target_aplikasi_id']);
            $table->foreign('target_aplikasi_id')->references('id')->on('aplikasi')->cascadeOnDelete();
        });

        Schema::create('app_integration_data_assets', function (Blueprint $table) {
            $table->foreignId('integration_id')->constrained('app_integrations')->cascadeOnDelete();
            $table->foreignId('data_asset_id')->constrained('data_assets')->cascadeOnDelete();
            $table->primary(['integration_id', 'data_asset_id']);
        });

        Schema::create('app_integration_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('integration_id')->constrained('app_integrations')->cascadeOnDelete();
            $table->string('path');
            $table->string('original_name');
            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('size_bytes')->nullable();
            $table->timestamps();
        });

        Schema::create('backup_media', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->enum('location', ['local', 'remote']);
            $table->enum('jenis_media', ['NAS', 'Disk', 'Cloud', 'Replication', 'Tape', 'Object Storage']);
            $table->unsignedBigInteger('kapasitas_gb')->nullable();
            $table->string('address_url')->nullable();
            $table->timestamps();
        });

        Schema::create('backup_jobs', function (Blueprint $table) {
            $table->id();
            $table->uuid('aplikasi_id')->index();
            $table->foreignId('backup_media_id')->constrained('backup_media');
            $table->unsignedInteger('retensi_n')->default(1);
            $table->enum('retensi_unit', ['realtime', 'menit', 'jam', 'hari', 'minggu', 'bulan']);
            $table->unsignedInteger('repetisi_n')->default(1);
            $table->enum('repetisi_unit', ['realtime', 'menit', 'jam', 'hari', 'minggu', 'bulan']);
            $table->timestamps();
            $table->foreign('aplikasi_id')->references('id')->on('aplikasi')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('backup_jobs');
        Schema::dropIfExists('backup_media');
        Schema::dropIfExists('app_integration_documents');
        Schema::dropIfExists('app_integration_data_assets');
        Schema::dropIfExists('app_integration_targets');
        Schema::dropIfExists('app_integrations');

        Schema::table('aplikasi_dokumen', function (Blueprint $table) {
            $table->dropColumn(['document_category', 'path', 'original_name', 'mime_type', 'size_bytes']);
        });

        Schema::table('aplikasi', function (Blueprint $table) {
            $table->dropColumn('tech_stack');
        });
    }
};
