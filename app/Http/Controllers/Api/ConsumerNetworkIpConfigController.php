<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\ConsumerNetworkIpConfig;
use Illuminate\Http\Request;

class ConsumerNetworkIpConfigController extends Controller
{
    public function index()
    {
        return ConsumerNetworkIpConfig::with($this->relations())
            ->orderByDesc('updated_at')
            ->get();
    }

    public function show(ConsumerNetworkIpConfig $networkIpConfig)
    {
        return $networkIpConfig->load($this->relations());
    }

    public function store(Request $request)
    {
        $config = ConsumerNetworkIpConfig::create($this->modelData($this->validated($request)));
        $this->audit('create', $config, null, $config->toArray(), $request);

        return response()->json($config->fresh($this->relations()), 201);
    }

    public function update(Request $request, ConsumerNetworkIpConfig $networkIpConfig)
    {
        $before = $networkIpConfig->toArray();
        $networkIpConfig->update($this->modelData($this->validated($request)));
        $after = $networkIpConfig->fresh()->toArray();
        $this->audit('update', $networkIpConfig, $before, $after, $request);

        return $networkIpConfig->fresh($this->relations());
    }

    public function destroy(Request $request, ConsumerNetworkIpConfig $networkIpConfig)
    {
        $before = $networkIpConfig->toArray();
        $networkIpConfig->delete();
        $this->audit('delete', $networkIpConfig, $before, null, $request);

        return response()->noContent();
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'device_id' => ['required', 'uuid', 'exists:consumer_network_devices,id'],
            'site_id' => ['nullable', 'uuid', 'exists:consumer_network_sites,id'],
            'ip_address_id' => ['nullable', 'uuid', 'exists:ip_address,id'],
            'interface_name' => ['nullable', 'string', 'max:80'],
            'ip_type' => ['nullable', 'in:management,wan,lan,wifi,loopback,lainnya'],
            'ip_address' => ['nullable', 'ip'],
            'subnet_mask' => ['nullable', 'string', 'max:45'],
            'gateway' => ['nullable', 'ip'],
            'dns' => ['nullable', 'string', 'max:120'],
            'vlan' => ['nullable', 'string', 'max:80'],
            'ssid' => ['nullable', 'string', 'max:120'],
            'dhcp_enabled' => ['boolean'],
            'status' => ['nullable', 'in:aktif,nonaktif'],
            'notes' => ['nullable', 'string'],
        ]);
    }

    private function modelData(array $data): array
    {
        $data['dhcp_enabled'] = (bool) ($data['dhcp_enabled'] ?? false);

        return $data;
    }

    private function relations(): array
    {
        return [
            'device:id,nama,jenis,asset_code',
            'site:id,nama,kode,jenis,asset_code',
            'ipAddressRecord:id,ip,jenis',
        ];
    }

    private function audit(string $action, ConsumerNetworkIpConfig $config, ?array $before, ?array $after, Request $request): void
    {
        AuditLog::create([
            'user_id' => $request->attributes->get('auth_user')?->id,
            'aksi' => $action,
            'tabel' => 'consumer_network_ip_configs',
            'record_id' => $config->id,
            'before_data' => $before,
            'after_data' => $after,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }
}
