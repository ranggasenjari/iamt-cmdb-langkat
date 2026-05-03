<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Isp;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class IspController extends Controller
{
    public function index()
    {
        return Isp::withCount(['ipAddresses'])
            ->orderBy('nama')
            ->get();
    }

    public function store(Request $request)
    {
        $isp = Isp::create($this->validated($request));
        $this->audit('create', $isp, null, $isp->toArray(), $request);

        return response()->json($isp, 201);
    }

    public function update(Request $request, Isp $isp)
    {
        $before = $isp->toArray();
        $isp->update($this->validated($request));
        $this->audit('update', $isp, $before, $isp->fresh()->toArray(), $request);

        return $isp->fresh();
    }

    public function destroy(Request $request, Isp $isp)
    {
        try {
            $before = $isp->toArray();
            $isp->delete();
            $this->audit('delete', $isp, $before, null, $request);

            return response()->noContent();
        } catch (QueryException) {
            return response()->json(['message' => 'ISP masih dipakai oleh IP address.'], 409);
        }
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'tipe' => ['nullable', 'string', 'max:50'],
            'bandwidth' => ['nullable', 'string', 'max:50'],
            'kontak' => ['nullable', 'string', 'max:255'],
        ]);
    }

    private function audit(string $action, Isp $isp, ?array $before, ?array $after, Request $request): void
    {
        AuditLog::create([
            'user_id' => $request->attributes->get('auth_user')?->id,
            'aksi' => $action,
            'tabel' => 'isp',
            'record_id' => $isp->id,
            'before_data' => $before,
            'after_data' => $after,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }
}

