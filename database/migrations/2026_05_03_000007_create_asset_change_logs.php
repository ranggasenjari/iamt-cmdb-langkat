<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_change_logs', function (Blueprint $table) {
            $table->id();
            $table->enum('asset_type', ['server', 'vm']);
            $table->uuid('asset_id')->index();
            $table->string('asset_name');
            $table->uuid('user_id')->nullable()->index();
            $table->string('change_type', 50)->default('spesifikasi');
            $table->json('changed_fields');
            $table->text('reason')->nullable();
            $table->string('changed_by')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
            $table->index(['asset_type', 'asset_id']);
            $table->foreign('user_id')->references('id')->on('pengguna')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_change_logs');
    }
};
