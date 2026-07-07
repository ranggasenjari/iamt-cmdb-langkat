<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppDatabaseDoc;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class AppDatabaseDocController extends Controller
{
    public function index()
    {
        return AppDatabaseDoc::with('aplikasi:id,nama,jenis_aplikasi')
            ->orderByDesc('updated_at')
            ->get();
    }

    public function show(AppDatabaseDoc $appDatabaseDoc)
    {
        return $appDatabaseDoc->load('aplikasi');
    }

    public function store(Request $request)
    {
        $doc = AppDatabaseDoc::create($this->validated($request));
        $this->storeFile($doc, $request);

        return response()->json($doc->fresh('aplikasi'), 201);
    }

    public function update(Request $request, AppDatabaseDoc $appDatabaseDoc)
    {
        $appDatabaseDoc->update($this->validated($request));
        $this->storeFile($appDatabaseDoc, $request);

        return $appDatabaseDoc->fresh('aplikasi');
    }

    public function destroy(AppDatabaseDoc $appDatabaseDoc)
    {
        if ($appDatabaseDoc->file_path) {
            File::delete(storage_path("app/{$appDatabaseDoc->file_path}"));
        }
        $appDatabaseDoc->delete();

        return response()->noContent();
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'aplikasi_id' => ['required', 'uuid', 'exists:aplikasi,id'],
            'nama_database' => ['required', 'string', 'max:100'],
            'tipe_dbms' => ['required', 'string', 'max:50'],
            'versi' => ['nullable', 'string', 'max:50'],
            'host' => ['nullable', 'string', 'max:150'],
            'port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'nama_db_asli' => ['nullable', 'string', 'max:100'],
            'jumlah_tabel' => ['nullable', 'integer', 'min:0'],
            'file' => ['nullable', 'file', 'max:20480'],
            'keterangan' => ['nullable', 'string'],
        ]);
    }

    private function storeFile(AppDatabaseDoc $doc, Request $request): void
    {
        if (! $request->hasFile('file')) {
            return;
        }

        if ($doc->file_path) {
            File::delete(storage_path("app/{$doc->file_path}"));
        }

        $file = $request->file('file');
        $folder = 'app-database-docs';
        $directory = storage_path("app/uploads/{$folder}");
        File::ensureDirectoryExists($directory);
        $filename = (string) Str::uuid().'.'.$file->getClientOriginalExtension();
        $file->move($directory, $filename);

        $doc->update([
            'file_path' => "uploads/{$folder}/{$filename}",
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size_bytes' => $file->getSize(),
        ]);
    }
}
