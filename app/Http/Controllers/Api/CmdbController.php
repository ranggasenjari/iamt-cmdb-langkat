<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Aplikasi;
use App\Models\AuditLog;
use App\Models\Server;
use App\Services\ImpactAnalysisService;
use Illuminate\Support\Facades\DB;

class CmdbController extends Controller
{
    public function dependencyMap()
    {
        return Aplikasi::with([
            'opd:id,nama',
            'vms:id,nama,server_id,status',
            'vms.server:id,nama,status',
            'ipAddresses:id,ip,jenis',
            'dataAssets:id,aplikasi_id,contains_personal_data',
        ])
            ->orderBy('nama')
            ->get()
            ->map(fn (Aplikasi $app) => [
                'id' => $app->id,
                'nama' => $app->nama,
                'status' => $app->status,
                'opd' => $app->opd?->nama,
                'data_pribadi' => $app->dataAssets->contains('contains_personal_data', true),
                'vms' => $app->vms->map(fn ($vm) => [
                    'id' => $vm->id,
                    'nama' => $vm->nama,
                    'status' => $vm->status,
                    'server' => $vm->server?->only(['id', 'nama', 'status']),
                ]),
                'ips' => $app->ipAddresses->map->only(['id', 'ip', 'jenis']),
            ]);
    }

    public function serverImpact(Server $server, ImpactAnalysisService $impact): array
    {
        return $impact->forServer($server);
    }

    public function compliance(): array
    {
        $apps = Aplikasi::with(['opd:id,nama', 'vms:id,nama', 'servers:id,nama', 'dataAssets:id,aplikasi_id,contains_personal_data,classification_id'])
            ->orderBy('nama')
            ->get();

        return [
            'summary' => [
                'total' => $apps->count(),
                'data_pribadi' => $apps->filter(fn ($app) => $app->dataAssets->contains('contains_personal_data', true))->count(),
                'tanpa_vm' => $apps->filter(fn ($app) => $app->vms->isEmpty() && $app->servers->isEmpty())->count(),
                'sla_kritis' => $apps->where('sla_persen', '>=', 99)->count(),
            ],
            'items' => $apps->map(fn ($app) => [
                'id' => $app->id,
                'nama' => $app->nama,
                'opd' => $app->opd?->nama,
                'status' => $app->status,
                'sla' => $app->sla_persen,
                'data_pribadi' => $app->dataAssets->contains('contains_personal_data', true),
                'kontrol' => [
                    'infrastruktur' => $app->vms->isNotEmpty() || $app->servers->isNotEmpty(),
                    'pic' => filled($app->pic_nama) && filled($app->pic_kontak),
                    'klasifikasi_data' => $app->dataAssets->isNotEmpty(),
                    'retensi' => filled($app->retensi_data),
                ],
            ]),
            'security_gap' => [
                'servers_without_tools' => Server::query()
                    ->whereNotIn('id', DB::table('security_server')->select('server_id'))
                    ->orderBy('nama')
                    ->get(['id', 'nama', 'status']),
            ],
        ];
    }

    public function auditLog()
    {
        return AuditLog::orderByDesc('created_at')
            ->limit(30)
            ->get(['id', 'aksi', 'tabel', 'record_id', 'before_data', 'after_data', 'ip_address', 'created_at']);
    }
}
