<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ups_devices', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->unsignedInteger('kapasitas_va');
            $table->enum('kondisi', ['baik', 'kurang_baik', 'rusak']);
            $table->uuid('dc_id')->nullable()->index();
            $table->timestamps();
            $table->foreign('dc_id')->references('id')->on('data_center')->nullOnDelete();
        });

        Schema::create('soc_tools', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->text('deskripsi_fungsi')->nullable();
            $table->enum('jenis', ['Firewall', 'IDS', 'IPS', 'Antivirus', 'EDR', 'SIEM', 'WAF', 'NDR', 'Vulnerability Scanner', 'Log Management']);
            $table->timestamps();
        });

        Schema::create('soc_tool_data_center', function (Blueprint $table) {
            $table->foreignId('soc_tool_id')->constrained('soc_tools')->cascadeOnDelete();
            $table->uuid('dc_id');
            $table->primary(['soc_tool_id', 'dc_id']);
            $table->foreign('dc_id')->references('id')->on('data_center')->cascadeOnDelete();
        });

        Schema::create('soc_tool_server', function (Blueprint $table) {
            $table->foreignId('soc_tool_id')->constrained('soc_tools')->cascadeOnDelete();
            $table->uuid('server_id');
            $table->primary(['soc_tool_id', 'server_id']);
            $table->foreign('server_id')->references('id')->on('server')->cascadeOnDelete();
        });

        Schema::create('soc_tool_vm', function (Blueprint $table) {
            $table->foreignId('soc_tool_id')->constrained('soc_tools')->cascadeOnDelete();
            $table->uuid('vm_id');
            $table->primary(['soc_tool_id', 'vm_id']);
            $table->foreign('vm_id')->references('id')->on('vm')->cascadeOnDelete();
        });

        Schema::create('soc_tool_aplikasi', function (Blueprint $table) {
            $table->foreignId('soc_tool_id')->constrained('soc_tools')->cascadeOnDelete();
            $table->uuid('aplikasi_id');
            $table->primary(['soc_tool_id', 'aplikasi_id']);
            $table->foreign('aplikasi_id')->references('id')->on('aplikasi')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('soc_tool_aplikasi');
        Schema::dropIfExists('soc_tool_vm');
        Schema::dropIfExists('soc_tool_server');
        Schema::dropIfExists('soc_tool_data_center');
        Schema::dropIfExists('soc_tools');
        Schema::dropIfExists('ups_devices');
    }
};
