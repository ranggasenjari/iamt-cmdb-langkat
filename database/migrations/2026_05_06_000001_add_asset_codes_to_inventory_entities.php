<?php

use App\Support\AssetCodeGenerator;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (array_keys(AssetCodeGenerator::PREFIXES) as $table) {
            if (! Schema::hasTable($table) || Schema::hasColumn($table, 'asset_code')) {
                continue;
            }

            Schema::table($table, function (Blueprint $table) {
                $table->string('asset_code', 50)->nullable()->unique()->after('id');
            });
        }

        AssetCodeGenerator::backfillAll();
    }

    public function down(): void
    {
        foreach (array_keys(AssetCodeGenerator::PREFIXES) as $tableName) {
            if (! Schema::hasTable($tableName) || ! Schema::hasColumn($tableName, 'asset_code')) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                $table->dropUnique("{$tableName}_asset_code_unique");
                $table->dropColumn('asset_code');
            });
        }
    }
};
