<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SocTool;
use Illuminate\Http\Request;

class SocToolController extends Controller
{
    public function index()
    {
        return SocTool::with([
            'dataCenters:id,nama,lokasi',
            'servers:id,nama,status',
            'vms:id,nama,status',
            'applications:id,nama,status',
        ])->orderBy('nama')->get();
    }

    public function show(SocTool $socTool)
    {
        return $socTool->load($this->relations());
    }

    public function store(Request $request)
    {
        $tool = SocTool::create($this->validated($request));
        $this->syncCoverage($tool, $request);

        return response()->json($tool->fresh($this->relations()), 201);
    }

    public function update(Request $request, SocTool $socTool)
    {
        $socTool->update($this->validated($request));
        $this->syncCoverage($socTool, $request);

        return $socTool->fresh($this->relations());
    }

    public function destroy(SocTool $socTool)
    {
        $socTool->delete();

        return response()->noContent();
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'deskripsi_fungsi' => ['nullable', 'string'],
            'jenis' => ['required', 'in:Firewall,IDS,IPS,Antivirus,EDR,SIEM,WAF,NDR,Vulnerability Scanner,Log Management'],
            'dc_ids' => ['array'],
            'dc_ids.*' => ['uuid', 'exists:data_center,id'],
            'server_ids' => ['array'],
            'server_ids.*' => ['uuid', 'exists:server,id'],
            'vm_ids' => ['array'],
            'vm_ids.*' => ['uuid', 'exists:vm,id'],
            'application_ids' => ['array'],
            'application_ids.*' => ['uuid', 'exists:aplikasi,id'],
        ]);
    }

    private function syncCoverage(SocTool $tool, Request $request): void
    {
        $tool->dataCenters()->sync($request->input('dc_ids', []));
        $tool->servers()->sync($request->input('server_ids', []));
        $tool->vms()->sync($request->input('vm_ids', []));
        $tool->applications()->sync($request->input('application_ids', []));
    }

    private function relations(): array
    {
        return ['dataCenters', 'servers', 'vms', 'applications'];
    }
}
