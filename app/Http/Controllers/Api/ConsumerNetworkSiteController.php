<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\ConsumerNetworkSite;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ConsumerNetworkSiteController extends Controller
{
    public function index()
    {
        return ConsumerNetworkSite::with($this->relations())
            ->withCount(['installations', 'ipConfigs', 'credentials'])
            ->orderBy('jenis')
            ->orderBy('nama')
            ->get();
    }

    public function show(ConsumerNetworkSite $networkSite)
    {
        return $networkSite->load($this->relations())
            ->loadCount(['installations', 'ipConfigs', 'credentials']);
    }

    public function store(Request $request)
    {
        $site = ConsumerNetworkSite::create($this->validated($request));
        $this->audit('create', $site, null, $site->toArray(), $request);

        return response()->json($site->fresh($this->relations()), 201);
    }

    public function update(Request $request, ConsumerNetworkSite $networkSite)
    {
        $before = $networkSite->toArray();
        $networkSite->update($this->validated($request, $networkSite));
        $after = $networkSite->fresh()->toArray();
        $this->audit('update', $networkSite, $before, $after, $request);

        return $networkSite->fresh($this->relations());
    }

    public function destroy(Request $request, ConsumerNetworkSite $networkSite)
    {
        $before = $networkSite->toArray();
        $networkSite->delete();
        $this->audit('delete', $networkSite, $before, null, $request);

        return response()->noContent();
    }

    private function validated(Request $request, ?ConsumerNetworkSite $site = null): array
    {
        return $request->validate([
            'kode' => ['nullable', 'string', 'max:50', Rule::unique('consumer_network_sites', 'kode')->ignore($site?->id)],
            'nama' => ['required', 'string', 'max:255'],
            'jenis' => ['required', 'in:kantor,dc,rack,tower,ruang,outdoor,lainnya'],
            'status' => ['nullable', 'in:aktif,nonaktif,maintenance'],
            'opd_id' => ['nullable', 'uuid', 'exists:opd,id'],
            'dc_id' => ['nullable', 'uuid', 'exists:data_center,id'],
            'rack_id' => ['nullable', 'uuid', 'exists:rack,id'],
            'alamat' => ['nullable', 'string', 'max:255'],
            'lokasi_detail' => ['nullable', 'string', 'max:255'],
            'titik_koordinat' => ['nullable', 'string', 'max:120'],
            'pic_nama' => ['nullable', 'string', 'max:255'],
            'pic_kontak' => ['nullable', 'string', 'max:100'],
            'catatan' => ['nullable', 'string'],
        ]);
    }

    private function relations(): array
    {
        return [
            'opd:id,nama',
            'dataCenter:id,nama,lokasi',
            'rack:id,nama,dc_id',
        ];
    }

    private function audit(string $action, ConsumerNetworkSite $site, ?array $before, ?array $after, Request $request): void
    {
        AuditLog::create([
            'user_id' => $request->attributes->get('auth_user')?->id,
            'aksi' => $action,
            'tabel' => 'consumer_network_sites',
            'record_id' => $site->id,
            'before_data' => $before,
            'after_data' => $after,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }
}
