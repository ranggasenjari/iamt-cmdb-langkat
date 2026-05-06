<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\DataCenter;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class DataCenterController extends Controller
{
    public function index()
    {
        return DataCenter::withCount(['racks'])
            ->orderBy('nama')
            ->get();
    }

    public function show(DataCenter $dataCenter)
    {
        return $dataCenter->loadCount(['racks']);
    }

    public function store(Request $request)
    {
        $dataCenter = DataCenter::create($this->validated($request));
        $this->audit('create', $dataCenter, null, $dataCenter->toArray(), $request);

        return response()->json($dataCenter, 201);
    }

    public function update(Request $request, DataCenter $dataCenter)
    {
        $before = $dataCenter->toArray();
        $dataCenter->update($this->validated($request));
        $this->audit('update', $dataCenter, $before, $dataCenter->fresh()->toArray(), $request);

        return $dataCenter->fresh();
    }

    public function destroy(Request $request, DataCenter $dataCenter)
    {
        try {
            $before = $dataCenter->toArray();
            $dataCenter->delete();
            $this->audit('delete', $dataCenter, $before, null, $request);

            return response()->noContent();
        } catch (QueryException) {
            return response()->json(['message' => 'Data center masih dipakai oleh rack atau server.'], 409);
        }
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'lokasi' => ['nullable', 'string', 'max:255'],
            'tipe' => ['required', 'in:utama,dr,cloud'],
        ]);
    }

    private function audit(string $action, DataCenter $dataCenter, ?array $before, ?array $after, Request $request): void
    {
        AuditLog::create([
            'user_id' => $request->attributes->get('auth_user')?->id,
            'aksi' => $action,
            'tabel' => 'data_center',
            'record_id' => $dataCenter->id,
            'before_data' => $before,
            'after_data' => $after,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }
}

