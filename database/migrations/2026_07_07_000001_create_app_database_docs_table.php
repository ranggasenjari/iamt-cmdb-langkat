<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_database_docs', function (Blueprint $table) {
            $table->id();
            $table->string('asset_code', 30)->nullable()->unique();
            $table->uuid('aplikasi_id');
            $table->string('nama_database', 100);
            $table->string('tipe_dbms', 50);
            $table->string('versi', 50)->nullable();
            $table->string('host', 150)->nullable();
            $table->integer('port')->nullable();
            $table->string('nama_db_asli', 100)->nullable();
            $table->integer('jumlah_tabel')->nullable();
            $table->string('file_path')->nullable();
            $table->string('original_name')->nullable();
            $table->string('mime_type', 100)->nullable();
            $table->integer('size_bytes')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->foreign('aplikasi_id')->references('id')->on('aplikasi')->cascadeOnDelete();
            $table->index('aplikasi_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_database_docs');
    }
};
