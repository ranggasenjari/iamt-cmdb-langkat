<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UpsDevice;
use Illuminate\Http\Request;

class UpsDeviceController extends Controller
{
    public function index()
    {
        return UpsDevice::with('dataCenter:id,nama,lokasi,tipe')->orderBy('nama')->get();
    }

    public function show(UpsDevice $upsDevice)
    {
        return $upsDevice->load('dataCenter:id,nama,lokasi,tipe');
    }

    public function store(Request $request)
    {
        return response()->json(UpsDevice::create($this->validated($request))->load('dataCenter'), 201);
    }

    public function update(Request $request, UpsDevice $upsDevice)
    {
        $upsDevice->update($this->validated($request));

        return $upsDevice->fresh('dataCenter');
    }

    public function destroy(UpsDevice $upsDevice)
    {
        $upsDevice->delete();

        return response()->noContent();
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'kapasitas_va' => ['required', 'integer', 'min:1'],
            'kondisi' => ['required', 'in:baik,kurang_baik,rusak'],
            'dc_id' => ['nullable', 'uuid', 'exists:data_center,id'],
        ]);
    }
}
