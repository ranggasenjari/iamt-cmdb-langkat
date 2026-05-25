<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\ConsumerNetworkDevice;
use App\Models\ConsumerNetworkInstallation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ConsumerNetworkDeviceController extends Controller
{
    public function index()
    {
        return ConsumerNetworkDevice::with($this->relations())
            ->withCount(['downstreamDevices', 'installations', 'ipConfigs', 'credentials'])
            ->orderBy('jenis')
            ->orderBy('nama')
            ->get();
    }

    public function show(ConsumerNetworkDevice $networkDevice)
    {
        return $networkDevice->load($this->relations())
            ->loadCount(['downstreamDevices', 'installations', 'ipConfigs', 'credentials']);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $siteId = $data['site_id'] ?? null;
        unset($data['site_id']);

        $device = DB::transaction(function () use ($data, $siteId, $request) {
            $device = ConsumerNetworkDevice::create($this->modelData($data));
            $this->audit('create', $device, null, $device->toArray(), $request);

            if ($siteId) {
                $installation = ConsumerNetworkInstallation::create([
                    'site_id' => $siteId,
                    'device_id' => $device->id,
                    'status' => 'aktif',
                    'installed_at' => now()->toDateString(),
                    'installed_by' => $request->attributes->get('auth_user')?->nama,
                    'notes' => 'Riwayat instalasi otomatis dari form tambah perangkat.',
                ]);

                $this->auditInstallation('create', $installation, null, $installation->toArray(), $request);
            }

            return $device;
        });

        return response()->json($device->fresh($this->relations()), 201);
    }

    public function update(Request $request, ConsumerNetworkDevice $networkDevice)
    {
        $before = $networkDevice->toArray();
        $networkDevice->update($this->modelData($this->validated($request), $networkDevice));
        $after = $networkDevice->fresh()->toArray();
        $this->audit('update', $networkDevice, $before, $after, $request);

        return $networkDevice->fresh($this->relations());
    }

    public function destroy(Request $request, ConsumerNetworkDevice $networkDevice)
    {
        $before = $networkDevice->toArray();
        $networkDevice->delete();
        $this->audit('delete', $networkDevice, $before, null, $request);

        return response()->noContent();
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'jenis' => ['required', 'in:router_utama,router,switch,access_point,wireless_controller,modem,cpe,repeater,bridge,firewall,lainnya'],
            'status' => ['nullable', 'in:aktif,nonaktif,maintenance'],
            'kondisi' => ['nullable', 'in:baik,kurang_baik,rusak'],
            'merk' => ['nullable', 'string', 'max:100'],
            'model' => ['nullable', 'string', 'max:100'],
            'serial_number' => ['nullable', 'string', 'max:120'],
            'os_firmware' => ['nullable', 'string', 'max:120'],
            'mac_address' => ['nullable', 'string', 'max:32'],
            'kapasitas_port' => ['nullable', 'integer', 'min:1'],
            'poe_support' => ['boolean'],
            'wireless_standard' => ['nullable', 'string', 'max:80'],
            'frekuensi' => ['nullable', 'string', 'max:80'],
            'bandwidth' => ['nullable', 'string', 'max:80'],
            'deskripsi' => ['nullable', 'string'],
            'management_ip' => ['nullable', 'ip'],
            'subnet_mask' => ['nullable', 'string', 'max:45'],
            'gateway' => ['nullable', 'ip'],
            'dns' => ['nullable', 'string', 'max:120'],
            'vlan' => ['nullable', 'string', 'max:80'],
            'ssid' => ['nullable', 'string', 'max:120'],
            'ip_public' => ['nullable', 'ip'],
            'dhcp_enabled' => ['boolean'],
            'ip_address_id' => ['nullable', 'uuid', 'exists:ip_address,id'],
            'upstream_device_id' => ['nullable', 'uuid', 'exists:consumer_network_devices,id'],
            'dc_id' => ['nullable', 'uuid', 'exists:data_center,id'],
            'rack_id' => ['nullable', 'uuid', 'exists:rack,id'],
            'opd_id' => ['nullable', 'uuid', 'exists:opd,id'],
            'lokasi_instalasi' => ['nullable', 'string', 'max:255'],
            'titik_koordinat' => ['nullable', 'string', 'max:120'],
            'tanggal_pasang' => ['nullable', 'date'],
            'penanggung_jawab' => ['nullable', 'string', 'max:255'],
            'management_url' => ['nullable', 'string', 'max:255'],
            'credential_username' => ['nullable', 'string', 'max:255'],
            'credential_password' => ['nullable', 'string', 'max:255'],
            'credential_notes' => ['nullable', 'string'],
            'site_id' => ['nullable', 'uuid', 'exists:consumer_network_sites,id'],
        ]);
    }

    private function modelData(array $data, ?ConsumerNetworkDevice $device = null): array
    {
        unset($data['site_id']);

        foreach (['poe_support', 'dhcp_enabled'] as $field) {
            $data[$field] = (bool) ($data[$field] ?? false);
        }

        if (($data['upstream_device_id'] ?? null) === $device?->id) {
            $data['upstream_device_id'] = null;
        }

        if (! array_key_exists('credential_password', $data) || blank($data['credential_password'])) {
            unset($data['credential_password']);
        } else {
            $data['credential_updated_at'] = now();
        }

        return $data;
    }

    private function relations(): array
    {
        return [
            'dataCenter:id,nama,lokasi',
            'rack:id,nama',
            'opd:id,nama',
            'ipAddress:id,ip,jenis',
            'upstreamDevice:id,nama,jenis,asset_code',
            'activeInstallation.site:id,nama,kode,jenis,asset_code,opd_id',
            'activeInstallation.site.opd:id,nama',
        ];
    }

    private function audit(string $action, ConsumerNetworkDevice $device, ?array $before, ?array $after, Request $request): void
    {
        AuditLog::create([
            'user_id' => $request->attributes->get('auth_user')?->id,
            'aksi' => $action,
            'tabel' => 'consumer_network_devices',
            'record_id' => $device->id,
            'before_data' => $before,
            'after_data' => $after,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }

    private function auditInstallation(string $action, ConsumerNetworkInstallation $installation, ?array $before, ?array $after, Request $request): void
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
