<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('data_assets', function (Blueprint $table) {
            $table->text('attributes')->nullable()->after('type');
            $table->string('owner_agency', 150)->nullable()->after('attributes');
            $table->unsignedTinyInteger('confidentiality_score')->nullable()->after('owner_agency');
            $table->unsignedTinyInteger('integrity_score')->nullable()->after('confidentiality_score');
            $table->unsignedTinyInteger('availability_score')->nullable()->after('integrity_score');
            $table->unsignedTinyInteger('risk_total')->nullable()->after('availability_score');
        });
    }

    public function down(): void
    {
        Schema::table('data_assets', function (Blueprint $table) {
            $table->dropColumn([
                'attributes',
                'owner_agency',
                'confidentiality_score',
                'integrity_score',
                'availability_score',
                'risk_total',
            ]);
        });
    }
};
