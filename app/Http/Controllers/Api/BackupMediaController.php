<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BackupMedia;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class BackupMediaController extends Controller
{
    public function index()
    {
        return BackupMedia::withCount('backupJobs')->orderBy('nama')->get();
    }

    public function store(Request $request)
    {
        return response()->json(BackupMedia::create($this->validated($request)), 201);
    }

    public function update(Request $request, BackupMedia $backupMedia)
    {
        $backupMedia->update($this->validated($request));

        return $backupMedia->fresh();
    }

    public function destroy(BackupMedia $backupMedia)
    {
        try {
            $backupMedia->delete();

            return response()->noContent();
        } catch (QueryException) {
            return response()->json(['message' => 'Media masih dipakai oleh jadwal pencadangan.'], 409);
        }
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'location' => ['required', 'in:local,remote'],
            'jenis_media' => ['required', 'in:NAS,Disk,Cloud,Replication,Tape,Object Storage'],
            'kapasitas_gb' => ['nullable', 'integer', 'min:1'],
            'address_url' => ['nullable', 'string', 'max:255'],
        ]);
    }
}
