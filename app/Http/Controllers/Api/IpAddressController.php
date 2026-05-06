<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\IpAddress;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class IpAddressController extends Controller
{
    public function index()
    {
        return IpAddress::with(['isp:id,nama,tipe,bandwidth'])
            ->withCount(['vms'])
            ->orderBy('ip')
            ->get();
    }

    public function show(IpAddress $ipAddress)
    {
        return $ipAddress->load(['isp:id,nama,tipe,bandwidth'])->loadCount(['vms']);
    }

    public function store(Request $request)
    {
        $ipAddress = IpAddress::create($this->validated($request));
        $this->audit('create', $ipAddress, null, $ipAddress->toArray(), $request);

        return response()->json($ipAddress->load('isp'), 201);
    }

    public function update(Request $request, IpAddress $ipAddress)
    {
        $before = $ipAddress->toArray();
        $ipAddress->update($this->validated($request, $ipAddress));
        $this->audit('update', $ipAddress, $before, $ipAddress->fresh()->toArray(), $request);

        return $ipAddress->fresh('isp');
    }

    public function destroy(Request $request, IpAddress $ipAddress)
    {
        try {
            $before = $ipAddress->toArray();
            $ipAddress->delete();
            $this->audit('delete', $ipAddress, $before, null, $request);

            return response()->noContent();
        } catch (QueryException) {
            return response()->json(['message' => 'IP address masih dipakai oleh VM atau aplikasi.'], 409);
        }
    }

    private function validated(Request $request, ?IpAddress $ipAddress = null): array
    {
        return $request->validate([
            'ip' => ['required', 'ip', 'max:45', Rule::unique('ip_address', 'ip')->ignore($ipAddress?->id)],
            'jenis' => ['required', 'in:publik,private'],
            'isp_id' => ['nullable', 'uuid', 'exists:isp,id'],
        ]);
    }

    private function audit(string $action, IpAddress $ipAddress, ?array $before, ?array $after, Request $request): void
    {
        AuditLog::create([
            'user_id' => $request->attributes->get('auth_user')?->id,
            'aksi' => $action,
            'tabel' => 'ip_address',
            'record_id' => $ipAddress->id,
            'before_data' => $before,
            'after_data' => $after,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }
}

