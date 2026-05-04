<?php

namespace App\Services;

use App\Models\AssetChangeLog;
use Illuminate\Http\Request;

class AssetChangeLogger
{
    public function log(string $assetType, string $assetId, string $assetName, array $before, array $after, Request $request, array $trackedFields): ?AssetChangeLog
    {
        $changes = collect($trackedFields)
            ->mapWithKeys(function (string $field) use ($before, $after) {
                $old = $before[$field] ?? null;
                $new = $after[$field] ?? null;

                return $old !== $new ? [$field => ['before' => $old, 'after' => $new]] : [];
            })
            ->all();

        if ($changes === []) {
            return null;
        }

        return AssetChangeLog::create([
            'asset_type' => $assetType,
            'asset_id' => $assetId,
            'asset_name' => $assetName,
            'user_id' => $request->attributes->get('auth_user')?->id,
            'change_type' => $this->changeType(array_keys($changes)),
            'changed_fields' => $changes,
            'reason' => $request->input('change_reason'),
            'changed_by' => $request->input('changed_by') ?: $request->attributes->get('auth_user')?->nama,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }

    private function changeType(array $fields): string
    {
        if (array_intersect($fields, ['cpu_core', 'ram_gb', 'storage_gb', 'vcpu', 'os', 'merk', 'tipe', 'serial_number', 'merk_processor'])) {
            return 'spesifikasi';
        }

        if (array_intersect($fields, ['status', 'kondisi'])) {
            return 'status';
        }

        if (array_intersect($fields, ['dc_id', 'rack_id', 'rack_size_u', 'server_id'])) {
            return 'lokasi_relasi';
        }

        return 'lainnya';
    }
}
