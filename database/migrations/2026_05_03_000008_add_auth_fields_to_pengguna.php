<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengguna', function (Blueprint $table) {
            $table->string('password')->nullable()->after('email');
            $table->enum('role', ['full', 'read_only'])->default('read_only')->after('role_legacy');
            $table->string('api_token_hash', 64)->nullable()->unique()->after('status');
            $table->timestamp('last_login_at')->nullable()->after('api_token_hash');
            $table->timestamps();
            $table->unique('email');
        });
    }

    public function down(): void
    {
        Schema::table('pengguna', function (Blueprint $table) {
            $table->dropUnique(['email']);
            $table->dropUnique(['api_token_hash']);
            $table->dropColumn(['password', 'role', 'api_token_hash', 'last_login_at', 'created_at', 'updated_at']);
        });
    }
};
