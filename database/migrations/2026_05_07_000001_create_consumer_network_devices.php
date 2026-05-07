<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consumer_network_devices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('asset_code', 50)->nullable()->unique();
            $table->string('nama');
            $table->enum('jenis', [
                'router_utama',
                'router',
                'switch',
                'access_point',
                'wireless_controller',
                'modem',
                'cpe',
                'repeater',
                'bridge',
                'firewall',
                'lainnya',
            ])->index();
            $table->enum('status', ['aktif', 'nonaktif', 'maintenance'])->nullable()->index();
            $table->enum('kondisi', ['baik', 'kurang_baik', 'rusak'])->nullable();

            $table->string('merk', 100)->nullable();
            $table->string('model', 100)->nullable();
            $table->string('serial_number', 120)->nullable();
            $table->string('os_firmware', 120)->nullable();
            $table->string('mac_address', 32)->nullable();
            $table->integer('kapasitas_port')->nullable();
            $table->boolean('poe_support')->default(false);
            $table->string('wireless_standard', 80)->nullable();
            $table->string('frekuensi', 80)->nullable();
            $table->string('bandwidth', 80)->nullable();
            $table->text('deskripsi')->nullable();

            $table->string('management_ip', 45)->nullable();
            $table->string('subnet_mask', 45)->nullable();
            $table->string('gateway', 45)->nullable();
            $table->string('dns', 120)->nullable();
            $table->string('vlan', 80)->nullable();
            $table->string('ssid', 120)->nullable();
            $table->string('ip_public', 45)->nullable();
            $table->boolean('dhcp_enabled')->default(false);
            $table->uuid('ip_address_id')->nullable()->index();
            $table->uuid('upstream_device_id')->nullable()->index();

            $table->uuid('dc_id')->nullable()->index();
            $table->uuid('rack_id')->nullable()->index();
            $table->uuid('opd_id')->nullable()->index();
            $table->string('lokasi_instalasi')->nullable();
            $table->string('titik_koordinat', 120)->nullable();
            $table->date('tanggal_pasang')->nullable();
            $table->string('penanggung_jawab')->nullable();

            $table->string('management_url')->nullable();
            $table->string('credential_username')->nullable();
            $table->text('credential_password')->nullable();
            $table->text('credential_notes')->nullable();
            $table->timestamp('credential_updated_at')->nullable();

            $table->timestamps();

            $table->foreign('ip_address_id')->references('id')->on('ip_address')->nullOnDelete();
            $table->foreign('upstream_device_id')->references('id')->on('consumer_network_devices')->nullOnDelete();
            $table->foreign('dc_id')->references('id')->on('data_center')->nullOnDelete();
            $table->foreign('rack_id')->references('id')->on('rack')->nullOnDelete();
            $table->foreign('opd_id')->references('id')->on('opd')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consumer_network_devices');
    }
};
