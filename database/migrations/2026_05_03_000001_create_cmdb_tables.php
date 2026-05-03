<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('opd', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nama');
            $table->string('kontak')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('pengguna', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nama')->nullable();
            $table->string('email')->nullable();
            $table->uuid('opd_id')->nullable()->index();
            $table->string('role_legacy', 50)->nullable();
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
            $table->foreign('opd_id')->references('id')->on('opd')->nullOnDelete();
        });

        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 50);
        });

        Schema::create('user_roles', function (Blueprint $table) {
            $table->uuid('user_id');
            $table->foreignId('role_id');
            $table->primary(['user_id', 'role_id']);
            $table->foreign('user_id')->references('id')->on('pengguna')->cascadeOnDelete();
            $table->foreign('role_id')->references('id')->on('roles');
        });

        Schema::create('data_center', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nama')->nullable();
            $table->string('lokasi')->nullable();
            $table->enum('tipe', ['utama', 'dr', 'cloud'])->nullable();
        });

        Schema::create('rack', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('dc_id')->nullable()->index();
            $table->string('nama', 100)->nullable();
            $table->integer('kapasitas_u')->nullable();
            $table->foreign('dc_id')->references('id')->on('data_center');
        });

        Schema::create('server', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nama')->index();
            $table->uuid('dc_id')->nullable()->index();
            $table->uuid('rack_id')->nullable()->index();
            $table->string('merk', 100)->nullable();
            $table->string('tipe', 100)->nullable();
            $table->string('serial_number', 100)->nullable();
            $table->integer('cpu_core')->nullable();
            $table->integer('ram_gb')->nullable();
            $table->integer('storage_gb')->nullable();
            $table->enum('kondisi', ['baik', 'rusak'])->nullable();
            $table->enum('status', ['aktif', 'nonaktif', 'maintenance'])->nullable();
            $table->year('tahun')->nullable();
            $table->string('penanggung_jawab')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->foreign('dc_id')->references('id')->on('data_center');
            $table->foreign('rack_id')->references('id')->on('rack');
        });

        Schema::create('vm', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nama')->nullable();
            $table->uuid('server_id')->nullable()->index();
            $table->string('os', 100)->nullable();
            $table->integer('vcpu')->nullable();
            $table->integer('ram_gb')->nullable();
            $table->integer('storage_gb')->nullable();
            $table->enum('status', ['running', 'stopped', 'suspended', 'maintenance'])->nullable();
            $table->foreign('server_id')->references('id')->on('server')->nullOnDelete();
        });

        Schema::create('isp', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nama')->nullable();
            $table->string('tipe', 50)->nullable();
            $table->string('bandwidth', 50)->nullable();
            $table->string('kontak')->nullable();
        });

        Schema::create('ip_address', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('ip', 45)->unique();
            $table->enum('jenis', ['publik', 'private'])->nullable();
            $table->uuid('isp_id')->nullable()->index();
            $table->foreign('isp_id')->references('id')->on('isp');
        });

        Schema::create('vm_ip', function (Blueprint $table) {
            $table->uuid('vm_id');
            $table->uuid('ip_id');
            $table->primary(['vm_id', 'ip_id']);
            $table->foreign('vm_id')->references('id')->on('vm')->cascadeOnDelete();
            $table->foreign('ip_id')->references('id')->on('ip_address')->cascadeOnDelete();
        });

        Schema::create('network_device', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nama')->nullable();
            $table->string('tipe', 100)->nullable();
            $table->string('lokasi')->nullable();
            $table->enum('status', ['aktif', 'nonaktif', 'maintenance'])->nullable();
        });

        Schema::create('aplikasi', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nama')->index();
            $table->string('url')->nullable();
            $table->uuid('opd_id')->nullable()->index();
            $table->text('deskripsi')->nullable();
            $table->string('jenis_aplikasi', 50)->nullable();
            $table->enum('status', ['aktif', 'nonaktif', 'maintenance'])->nullable();
            $table->decimal('sla_persen', 5, 2)->nullable();
            $table->string('jam_operasional', 100)->nullable();
            $table->string('kategori_data', 50)->nullable();
            $table->boolean('mengandung_data_pribadi')->default(false);
            $table->text('jenis_data_pribadi')->nullable();
            $table->string('retensi_data', 100)->nullable();
            $table->string('lokasi_data')->nullable();
            $table->string('pic_nama')->nullable();
            $table->string('pic_kontak', 100)->nullable();
            $table->date('tanggal_go_live')->nullable();
            $table->text('risiko')->nullable();
            $table->timestamps();
            $table->foreign('opd_id')->references('id')->on('opd')->nullOnDelete();
        });

        Schema::create('aplikasi_dokumen', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('aplikasi_id');
            $table->enum('jenis', ['keamanan', 'tata_kelola', 'modul'])->nullable();
            $table->string('nama')->nullable();
            $table->string('url')->nullable();
            $table->string('versi', 50)->nullable();
            $table->date('tanggal')->nullable();
            $table->foreign('aplikasi_id')->references('id')->on('aplikasi')->cascadeOnDelete();
        });

        Schema::create('aplikasi_vm', function (Blueprint $table) {
            $table->uuid('aplikasi_id');
            $table->uuid('vm_id');
            $table->primary(['aplikasi_id', 'vm_id']);
            $table->foreign('aplikasi_id')->references('id')->on('aplikasi')->cascadeOnDelete();
            $table->foreign('vm_id')->references('id')->on('vm')->cascadeOnDelete();
        });

        Schema::create('aplikasi_server', function (Blueprint $table) {
            $table->uuid('aplikasi_id');
            $table->uuid('server_id');
            $table->primary(['aplikasi_id', 'server_id']);
            $table->foreign('aplikasi_id')->references('id')->on('aplikasi')->cascadeOnDelete();
            $table->foreign('server_id')->references('id')->on('server')->cascadeOnDelete();
        });

        Schema::create('aplikasi_ip', function (Blueprint $table) {
            $table->uuid('aplikasi_id');
            $table->uuid('ip_id');
            $table->primary(['aplikasi_id', 'ip_id']);
            $table->foreign('aplikasi_id')->references('id')->on('aplikasi')->cascadeOnDelete();
            $table->foreign('ip_id')->references('id')->on('ip_address')->cascadeOnDelete();
        });

        Schema::create('layanan', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nama')->nullable();
            $table->text('deskripsi')->nullable();
            $table->uuid('opd_id')->nullable()->index();
            $table->enum('status', ['aktif', 'nonaktif', 'maintenance'])->nullable();
            $table->string('kategori_data', 50)->nullable();
            $table->string('pic_nama')->nullable();
            $table->string('pic_kontak', 100)->nullable();
            $table->date('tanggal_go_live')->nullable();
            $table->text('risiko')->nullable();
            $table->foreign('opd_id')->references('id')->on('opd')->nullOnDelete();
        });

        Schema::create('layanan_aplikasi', function (Blueprint $table) {
            $table->uuid('layanan_id');
            $table->uuid('aplikasi_id');
            $table->primary(['layanan_id', 'aplikasi_id']);
            $table->foreign('layanan_id')->references('id')->on('layanan');
            $table->foreign('aplikasi_id')->references('id')->on('aplikasi');
        });

        Schema::create('security_tools', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nama')->nullable();
            $table->string('jenis', 100)->nullable();
        });

        foreach (['server', 'vm', 'aplikasi'] as $assetTable) {
            Schema::create("security_{$assetTable}", function (Blueprint $table) use ($assetTable) {
                $table->uuid('tool_id');
                $table->uuid("{$assetTable}_id");
                $table->primary(['tool_id', "{$assetTable}_id"]);
                $table->foreign('tool_id')->references('id')->on('security_tools');
                $table->foreign("{$assetTable}_id")->references('id')->on($assetTable);
            });
        }

        Schema::create('security_dc', function (Blueprint $table) {
            $table->uuid('tool_id');
            $table->uuid('dc_id');
            $table->primary(['tool_id', 'dc_id']);
            $table->foreign('tool_id')->references('id')->on('security_tools');
            $table->foreign('dc_id')->references('id')->on('data_center');
        });

        Schema::create('backup_policy', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nama')->nullable();
            $table->string('frekuensi', 50)->nullable();
            $table->string('retensi', 50)->nullable();
        });

        Schema::create('backup_target', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('jenis', 50)->nullable();
            $table->string('lokasi')->nullable();
        });

        Schema::create('backup_aplikasi', function (Blueprint $table) {
            $table->uuid('aplikasi_id');
            $table->uuid('policy_id');
            $table->uuid('target_id');
            $table->primary(['aplikasi_id', 'policy_id', 'target_id']);
            $table->foreign('aplikasi_id')->references('id')->on('aplikasi');
            $table->foreign('policy_id')->references('id')->on('backup_policy');
            $table->foreign('target_id')->references('id')->on('backup_target');
        });

        Schema::create('audit_log', function (Blueprint $table) {
            $table->id();
            $table->uuid('user_id')->nullable()->index();
            $table->string('aksi');
            $table->string('tabel', 100);
            $table->uuid('record_id')->nullable();
            $table->json('before_data')->nullable();
            $table->json('after_data')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->foreign('user_id')->references('id')->on('pengguna')->nullOnDelete();
        });
    }

    public function down(): void
    {
        foreach ([
            'audit_log', 'backup_aplikasi', 'backup_target', 'backup_policy', 'security_dc',
            'security_aplikasi', 'security_vm', 'security_server', 'security_tools',
            'layanan_aplikasi', 'layanan', 'aplikasi_ip', 'aplikasi_server', 'aplikasi_vm',
            'aplikasi_dokumen', 'aplikasi', 'network_device', 'vm_ip', 'ip_address',
            'isp', 'vm', 'server', 'rack', 'data_center', 'user_roles', 'roles', 'pengguna', 'opd',
        ] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
