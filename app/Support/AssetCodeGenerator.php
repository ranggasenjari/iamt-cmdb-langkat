<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AssetCodeGenerator
{
    public const PREFIXES = [
        'data_center' => 'DC',
        'rack' => 'RCK',
        'server' => 'SRV',
        'vm' => 'VM',
        'isp' => 'ISP',
        'ip_address' => 'IP',
        'aplikasi' => 'APP',
        'aplikasi_dokumen' => 'DOC',
        'data_assets' => 'DATA',
        'app_integrations' => 'INT',
        'backup_media' => 'BKM',
        'backup_jobs' => 'BKJ',
        'ups_devices' => 'UPS',
        'soc_tools' => 'SOC',
        'consumer_network_devices' => 'NET',
        'consumer_network_sites' => 'NETS',
        'consumer_network_monitorings' => 'MON',
    ];

    public static function next(string $table): string
    {
        $prefix = self::PREFIXES[$table] ?? strtoupper(substr($table, 0, 3));
        $base = "LKT-{$prefix}-";
        $lastCode = DB::table($table)
            ->where('asset_code', 'like', $base.'%')
            ->orderByDesc('asset_code')
            ->value('asset_code');

        $nextNumber = $lastCode ? ((int) substr($lastCode, -6)) + 1 : 1;

        return $base.str_pad((string) $nextNumber, 6, '0', STR_PAD_LEFT);
    }

    public static function backfillAll(): void
    {
        foreach (array_keys(self::PREFIXES) as $table) {
            self::backfillTable($table);
        }
    }

    public static function backfillTable(string $table): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'asset_code')) {
            return;
        }

        $ids = DB::table($table)
            ->whereNull('asset_code')
            ->orderBy('id')
            ->pluck('id');

        foreach ($ids as $id) {
            DB::table($table)
                ->where('id', $id)
                ->update(['asset_code' => self::next($table)]);
        }
    }
}
