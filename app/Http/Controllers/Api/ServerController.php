<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Server;
use App\Services\AssetChangeLogger;
use Illuminate\Http\Request;

class ServerController extends Controller
{
    public function index()
    {
        return Server::with(['dataCenter:id,nama,lokasi', 'rack:id,nama', 'vms:id,nama,server_id,status'])
            ->orderBy('nama')
            ->get();
    }

    public function store(Request $request)
    {
        $data = $this->modelData($this->validated($request));
        $server = Server::create($data);
        $this->audit('create', $server, null, $server->toArray(), $request);

        return response()->json($server->load(['dataCenter', 'rack', 'vms']), 201);
    }

    public function update(Request $request, Server $server, AssetChangeLogger $changeLogger)
    {
        $before = $server->toArray();
        $server->update($this->modelData($this->validated($request)));
        $after = $server->fresh()->toArray();
        $this->audit('update', $server, $before, $after, $request);
        $changeLogger->log('server', $server->id, $server->nama, $before, $after, $request, [
            'nama', 'dc_id', 'rack_id', 'rack_size_u', 'merk', 'tipe', 'serial_number', 'merk_processor', 'cpu_core',
            'ram_gb', 'storage_gb', 'kondisi', 'status', 'tahun', 'penanggung_jawab',
        ]);

        return $server->fresh(['dataCenter', 'rack', 'vms']);
    }

    public function destroy(Request $request, Server $server)
    {
        $before = $server->toArray();
        $server->delete();
        $this->audit('delete', $server, $before, null, $request);

        return response()->noContent();
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'dc_id' => ['nullable', 'uuid', 'exists:data_center,id'],
            'rack_id' => ['nullable', 'uuid', 'exists:rack,id'],
            'rack_size_u' => ['nullable', 'integer', 'min:1', 'max:60'],
            'merk' => ['nullable', 'string', 'max:100'],
            'tipe' => ['nullable', 'string', 'max:100'],
            'serial_number' => ['nullable', 'string', 'max:100'],
            'merk_processor' => ['nullable', 'string', 'max:100'],
            'cpu_core' => ['nullable', 'integer', 'min:1'],
            'ram_gb' => ['nullable', 'integer', 'min:1'],
            'storage_gb' => ['nullable', 'integer', 'min:1'],
            'kondisi' => ['nullable', 'in:baik,rusak'],
            'status' => ['nullable', 'in:aktif,nonaktif,maintenance'],
            'tahun' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'penanggung_jawab' => ['nullable', 'string', 'max:255'],
            'change_reason' => ['nullable', 'string'],
            'changed_by' => ['nullable', 'string', 'max:255'],
        ]);
    }

    private function modelData(array $data): array
    {
        unset($data['change_reason'], $data['changed_by']);

        return $data;
    }

    private function audit(string $action, Server $server, ?array $before, ?array $after, Request $request): void
    {
        AuditLog::create([
            'user_id' => $request->attributes->get('auth_user')?->id,
            'aksi' => $action,
            'tabel' => 'server',
            'record_id' => $server->id,
            'before_data' => $before,
            'after_data' => $after,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }
}

