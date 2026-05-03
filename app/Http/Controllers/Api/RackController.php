<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Rack;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class RackController extends Controller
{
    public function index()
    {
        return Rack::with(['dataCenter:id,nama,lokasi,tipe'])
            ->withCount(['servers'])
            ->orderBy('nama')
            ->get();
    }

    public function store(Request $request)
    {
        $rack = Rack::create($this->validated($request));
        $this->audit('create', $rack, null, $rack->toArray(), $request);

        return response()->json($rack->load('dataCenter'), 201);
    }

    public function update(Request $request, Rack $rack)
    {
        $before = $rack->toArray();
        $rack->update($this->validated($request));
        $this->audit('update', $rack, $before, $rack->fresh()->toArray(), $request);

        return $rack->fresh('dataCenter');
    }

    public function destroy(Request $request, Rack $rack)
    {
        try {
            $before = $rack->toArray();
            $rack->delete();
            $this->audit('delete', $rack, $before, null, $request);

            return response()->noContent();
        } catch (QueryException) {
            return response()->json(['message' => 'Rack masih dipakai oleh server.'], 409);
        }
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'dc_id' => ['required', 'uuid', 'exists:data_center,id'],
            'nama' => ['required', 'string', 'max:100'],
            'kapasitas_u' => ['required', 'integer', 'min:1', 'max:60'],
        ]);
    }

    private function audit(string $action, Rack $rack, ?array $before, ?array $after, Request $request): void
    {
        AuditLog::create([
            'user_id' => $request->attributes->get('auth_user')?->id,
            'aksi' => $action,
            'tabel' => 'rack',
            'record_id' => $rack->id,
            'before_data' => $before,
            'after_data' => $after,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }
}

