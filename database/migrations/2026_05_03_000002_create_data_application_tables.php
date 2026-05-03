<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_classifications', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('name', 100);
            $table->enum('risk_level', ['LOW', 'MEDIUM', 'HIGH']);
            $table->text('description')->nullable();
            $table->boolean('requires_encryption')->default(false);
            $table->boolean('requires_mfa')->default(false);
            $table->boolean('requires_audit_log')->default(true);
            $table->timestamps();
        });

        Schema::create('data_assets', function (Blueprint $table) {
            $table->id();
            $table->uuid('aplikasi_id')->index();
            $table->foreignId('classification_id')->constrained('data_classifications');
            $table->string('name', 150);
            $table->enum('type', ['TABLE', 'COLUMN', 'API', 'FILE', 'FORM', 'DATASET']);
            $table->string('table_name', 100)->nullable();
            $table->string('column_name', 100)->nullable();
            $table->boolean('contains_personal_data')->default(false);
            $table->string('personal_data_type', 255)->nullable();
            $table->string('processing_purpose', 255)->nullable();
            $table->string('retention_period', 100)->nullable();
            $table->string('storage_location', 255)->nullable();
            $table->string('data_owner', 150)->nullable();
            $table->text('access_policy')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
            $table->foreign('aplikasi_id')->references('id')->on('aplikasi')->cascadeOnDelete();
        });

        DB::table('data_classifications')->insert([
            [
                'code' => 'OPEN',
                'name' => 'Data Elektronik Terbuka',
                'risk_level' => 'LOW',
                'description' => 'Data Elektronik dengan level risiko rendah dan dapat diakses publik sesuai ketentuan.',
                'requires_encryption' => false,
                'requires_mfa' => false,
                'requires_audit_log' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'LIMITED',
                'name' => 'Data Elektronik Terbatas',
                'risk_level' => 'MEDIUM',
                'description' => 'Data Elektronik dengan level risiko sedang, akses dibatasi untuk pihak berwenang.',
                'requires_encryption' => true,
                'requires_mfa' => false,
                'requires_audit_log' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'RESTRICTED',
                'name' => 'Data Elektronik Tertutup',
                'risk_level' => 'HIGH',
                'description' => 'Data Elektronik dengan level risiko tinggi, sangat sensitif, dan membutuhkan kontrol akses kuat.',
                'requires_encryption' => true,
                'requires_mfa' => true,
                'requires_audit_log' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('data_assets');
        Schema::dropIfExists('data_classifications');
    }
};
