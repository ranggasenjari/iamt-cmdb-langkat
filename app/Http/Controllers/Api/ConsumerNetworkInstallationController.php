<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\ConsumerNetworkInstallation;
use Illuminate\Http\Request;

class ConsumerNetworkInstallationController extends Controller
{
    public function index()
    {
        return ConsumerNetworkInstallation::with($this->relations())
            ->orderByDesc('installed_at')
            ->orderByDesc('updated_at')
            ->get();
    }

    public function show(ConsumerNetworkInstallation $networkInstallation)
    {
        return $networkInstallation->load($this->relations());
    }

    public function store(Request $request)
    {
        $installation = ConsumerNetworkInstallation::create($this->validated($request));
        $this->audit('create', $installation, null, $installation->toArray(), $request);

        return response()->json($installation->fresh($this->relations()), 201);
    }

    public function update(Request $request, ConsumerNetworkInstallation $networkInstallation)
    {
        $before = $networkInstallation->toArray();
        $networkInstallation->update($this->validated($request));
        $after = $networkInstallation->fresh()->toArray();
        $this->audit('update', $networkInstallation, $before, $after, $request);

        return $networkInstallation->fresh($this->relations());
    }

    public function destroy(Request $request, ConsumerNetworkInstallation $networkInstallation)
    {
        $before = $networkInstallation->toArray();
        $networkInstallation->delete();
        $this->audit('delete', $networkInstallation, $before, null, $request);

        return response()->noContent();
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'site_id' => ['required', 'uuid', 'exists:consumer_network_sites,id'],
            'device_id' => ['required', 'uuid', 'exists:consumer_network_devices,id'],
            'replaced_by_device_id' => ['nullable', 'uuid', 'exists:consumer_network_devices,id', 'different:device_id'],
            'role' => ['nullable', 'in:primary,backup,distribution,access,uplink,client,lainnya'],
            'status' => ['required', 'in:aktif,diganti,dilepas,rusak,maintenance'],
            'installed_at' => ['nullable', 'date'],
            'removed_at' => ['nullable', 'date', 'after_or_equal:installed_at'],
            'installed_by' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);
    }

    private function relations(): array
    {
        return [
            'site:id,nama,kode,jenis,asset_code,opd_id',
            'site.opd:id,nama',
            'device:id,nama,jenis,asset_code',
            'replacementDevice:id,nama,jenis,asset_code',
        ];
    }

    private function audit(string $action, ConsumerNetworkInstallation $installation, ?array $before, ?array $after, Request $request): void
    {
        AuditLog::create([
            'user_id' => $request->attributes->get('auth_user')?->id,
            'aksi' => $action,
            'tabel' => 'consumer_network_installations',
            'record_id' => $installation->id,
            'before_data' => $before,
            'after_data' => $after,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }
}
