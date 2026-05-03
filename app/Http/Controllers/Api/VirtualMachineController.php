<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\VirtualMachine;
use App\Services\AssetChangeLogger;
use Illuminate\Http\Request;

class VirtualMachineController extends Controller
{
    public function index()
    {
        return VirtualMachine::with(['server:id,nama,status', 'ipAddresses:id,ip,jenis'])
            ->orderBy('nama')
            ->get();
    }

    public function store(Request $request)
    {
        $data = $this->modelData($this->validated($request));
        $vm = VirtualMachine::create($data);
        $this->syncIps($vm, $request);
        $this->audit('create', $vm, null, $vm->fresh(['server', 'ipAddresses'])->toArray(), $request);

        return response()->json($vm->fresh(['server', 'ipAddresses']), 201);
    }

    public function update(Request $request, VirtualMachine $vm, AssetChangeLogger $changeLogger)
    {
        $before = $vm->load('ipAddresses')->toArray();
        $vm->update($this->modelData($this->validated($request)));
        $this->syncIps($vm, $request);
        $after = $vm->fresh(['server', 'ipAddresses'])->toArray();
        $this->audit('update', $vm, $before, $after, $request);
        $changeLogger->log('vm', $vm->id, $vm->nama, $before, $after, $request, [
            'nama', 'server_id', 'os', 'vcpu', 'ram_gb', 'storage_gb', 'status',
        ]);

        return $vm->fresh(['server', 'ipAddresses']);
    }

    public function destroy(Request $request, VirtualMachine $vm)
    {
        $before = $vm->toArray();
        $vm->delete();
        $this->audit('delete', $vm, $before, null, $request);

        return response()->noContent();
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'server_id' => ['nullable', 'uuid', 'exists:server,id'],
            'os' => ['nullable', 'string', 'max:100'],
            'vcpu' => ['nullable', 'integer', 'min:1'],
            'ram_gb' => ['nullable', 'integer', 'min:1'],
            'storage_gb' => ['nullable', 'integer', 'min:1'],
            'status' => ['nullable', 'in:running,stopped,suspended,maintenance'],
            'ip_ids' => ['array'],
            'ip_ids.*' => ['uuid', 'exists:ip_address,id'],
            'change_reason' => ['nullable', 'string'],
            'changed_by' => ['nullable', 'string', 'max:255'],
        ]);
    }

    private function syncIps(VirtualMachine $vm, Request $request): void
    {
        if ($request->has('ip_ids')) {
            $vm->ipAddresses()->sync($request->array('ip_ids'));
        }
    }

    private function modelData(array $data): array
    {
        unset($data['ip_ids'], $data['change_reason'], $data['changed_by']);

        return $data;
    }

    private function audit(string $action, VirtualMachine $vm, ?array $before, ?array $after, Request $request): void
    {
        AuditLog::create([
            'user_id' => $request->attributes->get('auth_user')?->id,
            'aksi' => $action,
            'tabel' => 'vm',
            'record_id' => $vm->id,
            'before_data' => $before,
            'after_data' => $after,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }
}

