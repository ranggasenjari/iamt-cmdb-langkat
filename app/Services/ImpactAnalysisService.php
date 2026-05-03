<?php

namespace App\Services;

use App\Models\Server;
use Illuminate\Support\Collection;

class ImpactAnalysisService
{
    public function forServer(Server $server): array
    {
        $server->load([
            'dataCenter',
            'rack',
            'vms.aplikasi.opd',
            'vms.aplikasi.dataAssets:id,aplikasi_id,contains_personal_data',
            'aplikasi.opd',
            'aplikasi.dataAssets:id,aplikasi_id,contains_personal_data',
        ]);

        $viaVm = $server->vms
            ->flatMap(fn ($vm) => $vm->aplikasi->map(fn ($app) => [
                'id' => $app->id,
                'nama' => $app->nama,
                'status' => $app->status,
                'opd' => $app->opd?->nama,
                'jalur' => "VM {$vm->nama}",
                'data_pribadi' => $app->dataAssets->contains('contains_personal_data', true),
            ]));

        $direct = $server->aplikasi->map(fn ($app) => [
            'id' => $app->id,
            'nama' => $app->nama,
            'status' => $app->status,
            'opd' => $app->opd?->nama,
            'jalur' => 'Relasi langsung server',
            'data_pribadi' => $app->dataAssets->contains('contains_personal_data', true),
        ]);

        /** @var Collection $applications */
        $applications = $viaVm->merge($direct)->unique('id')->values();

        return [
            'server' => [
                'id' => $server->id,
                'nama' => $server->nama,
                'status' => $server->status,
                'lokasi' => trim(($server->dataCenter?->nama ?? '-').' / '.($server->rack?->nama ?? '-')),
                'kapasitas' => "{$server->cpu_core} core / {$server->ram_gb} GB RAM / {$server->storage_gb} GB",
            ],
            'summary' => [
                'total_aplikasi' => $applications->count(),
                'data_pribadi' => $applications->where('data_pribadi', true)->count(),
                'running_vm' => $server->vms->where('status', 'running')->count(),
                'risk_level' => $applications->where('data_pribadi', true)->isNotEmpty() ? 'tinggi' : 'sedang',
            ],
            'applications' => $applications,
        ];
    }
}
