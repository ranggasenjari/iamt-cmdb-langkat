<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BackupJob;
use Illuminate\Http\Request;

class BackupJobController extends Controller
{
    public function index()
    {
        return BackupJob::with(['aplikasi:id,nama,jenis_aplikasi,status', 'media:id,nama,location,jenis_media'])
            ->orderByDesc('updated_at')
            ->get();
    }

    public function store(Request $request)
    {
        return response()->json(BackupJob::create($this->validated($request))->load(['aplikasi', 'media']), 201);
    }

    public function update(Request $request, BackupJob $backupJob)
    {
        $backupJob->update($this->validated($request));

        return $backupJob->fresh(['aplikasi', 'media']);
    }

    public function destroy(BackupJob $backupJob)
    {
        $backupJob->delete();

        return response()->noContent();
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'aplikasi_id' => ['required', 'uuid', 'exists:aplikasi,id'],
            'backup_media_id' => ['required', 'integer', 'exists:backup_media,id'],
            'retensi_n' => ['required', 'integer', 'min:1'],
            'retensi_unit' => ['required', 'in:realtime,menit,jam,hari,minggu,bulan'],
            'repetisi_n' => ['required', 'integer', 'min:1'],
            'repetisi_unit' => ['required', 'in:realtime,menit,jam,hari,minggu,bulan'],
        ]);
    }
}
