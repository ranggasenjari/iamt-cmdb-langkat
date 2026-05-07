<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consumer_network_sites', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('asset_code', 50)->nullable()->unique();
            $table->string('kode', 50)->nullable()->unique();
            $table->string('nama');
            $table->enum('jenis', ['kantor', 'dc', 'rack', 'tower', 'ruang', 'outdoor', 'lainnya'])->index();
            $table->enum('status', ['aktif', 'nonaktif', 'maintenance'])->nullable()->index();
            $table->uuid('opd_id')->nullable()->index();
            $table->uuid('dc_id')->nullable()->index();
            $table->uuid('rack_id')->nullable()->index();
            $table->string('alamat')->nullable();
            $table->string('lokasi_detail')->nullable();
            $table->string('titik_koordinat', 120)->nullable();
            $table->string('pic_nama')->nullable();
            $table->string('pic_kontak', 100)->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->foreign('opd_id')->references('id')->on('opd')->nullOnDelete();
            $table->foreign('dc_id')->references('id')->on('data_center')->nullOnDelete();
            $table->foreign('rack_id')->references('id')->on('rack')->nullOnDelete();
        });

        Schema::create('consumer_network_installations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('site_id')->index();
            $table->uuid('device_id')->index();
            $table->uuid('replaced_by_device_id')->nullable()->index();
            $table->enum('role', ['primary', 'backup', 'distribution', 'access', 'uplink', 'client', 'lainnya'])->nullable()->index();
            $table->enum('status', ['aktif', 'diganti', 'dilepas', 'rusak', 'maintenance'])->index();
            $table->date('installed_at')->nullable();
            $table->date('removed_at')->nullable();
            $table->string('installed_by')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('site_id')->references('id')->on('consumer_network_sites');
            $table->foreign('device_id')->references('id')->on('consumer_network_devices');
            $table->foreign('replaced_by_device_id')->references('id')->on('consumer_network_devices')->nullOnDelete();
        });

        Schema::create('consumer_network_ip_configs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('device_id')->index();
            $table->uuid('site_id')->nullable()->index();
            $table->uuid('ip_address_id')->nullable()->index();
            $table->string('interface_name', 80)->nullable();
            $table->enum('ip_type', ['management', 'wan', 'lan', 'wifi', 'loopback', 'lainnya'])->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->string('subnet_mask', 45)->nullable();
            $table->string('gateway', 45)->nullable();
            $table->string('dns', 120)->nullable();
            $table->string('vlan', 80)->nullable();
            $table->string('ssid', 120)->nullable();
            $table->boolean('dhcp_enabled')->default(false);
            $table->enum('status', ['aktif', 'nonaktif'])->nullable()->index();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('device_id')->references('id')->on('consumer_network_devices');
            $table->foreign('site_id')->references('id')->on('consumer_network_sites')->nullOnDelete();
            $table->foreign('ip_address_id')->references('id')->on('ip_address')->nullOnDelete();
        });

        Schema::create('consumer_network_credentials', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('device_id')->index();
            $table->uuid('site_id')->nullable()->index();
            $table->string('label');
            $table->enum('access_method', ['web', 'ssh', 'winbox', 'snmp', 'api', 'vpn', 'lainnya'])->index();
            $table->string('management_url')->nullable();
            $table->string('username')->nullable();
            $table->text('password')->nullable();
            $table->text('notes')->nullable();
            $table->date('last_rotated_at')->nullable();
            $table->timestamps();

            $table->foreign('device_id')->references('id')->on('consumer_network_devices');
            $table->foreign('site_id')->references('id')->on('consumer_network_sites')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consumer_network_credentials');
        Schema::dropIfExists('consumer_network_ip_configs');
        Schema::dropIfExists('consumer_network_installations');
        Schema::dropIfExists('consumer_network_sites');
    }
};
