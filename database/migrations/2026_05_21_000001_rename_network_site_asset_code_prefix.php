<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('consumer_network_sites') || ! Schema::hasColumn('consumer_network_sites', 'asset_code')) {
            return;
        }

        $rows = DB::table('consumer_network_sites')
            ->where('asset_code', 'like', 'LKT-NETS-%')
            ->orderBy('asset_code')
            ->get(['id', 'asset_code']);

        if ($rows->isEmpty()) {
            return;
        }

        foreach ($rows as $row) {
            DB::table('consumer_network_sites')
                ->where('id', $row->id)
                ->update(['asset_code' => 'TMP-SITE-'.$row->id]);
        }

        $usedNumbers = DB::table('consumer_network_sites')
            ->where('asset_code', 'like', 'LKT-SITE-%')
            ->pluck('asset_code')
            ->map(fn ($code) => (int) substr($code, -6))
            ->filter()
            ->values()
            ->all();

        $used = array_fill_keys($usedNumbers, true);

        foreach ($rows as $row) {
            $number = (int) substr($row->asset_code, -6);

            while (isset($used[$number])) {
                $number++;
            }

            $used[$number] = true;

            DB::table('consumer_network_sites')
                ->where('id', $row->id)
                ->update(['asset_code' => 'LKT-SITE-'.str_pad((string) $number, 6, '0', STR_PAD_LEFT)]);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('consumer_network_sites') || ! Schema::hasColumn('consumer_network_sites', 'asset_code')) {
            return;
        }

        DB::table('consumer_network_sites')
            ->where('asset_code', 'like', 'LKT-SITE-%')
            ->orderBy('asset_code')
            ->get(['id', 'asset_code'])
            ->each(function ($row): void {
                DB::table('consumer_network_sites')
                    ->where('id', $row->id)
                    ->update(['asset_code' => str_replace('LKT-SITE-', 'LKT-NETS-', $row->asset_code)]);
            });
    }
};
