<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\DataAsset;
use App\Models\DataClassification;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DataAssetController extends Controller
{
    public function index()
    {
        return DataAsset::with([
            'aplikasi:id,nama,jenis_aplikasi,status',
            'classification:id,code,name,risk_level,requires_encryption,requires_mfa,requires_audit_log',
        ])
            ->orderBy('name')
            ->get();
    }

    public function store(Request $request)
    {
        $asset = DataAsset::create($this->withCalculatedClassification($this->validated($request)));
        $this->audit('create', $asset, null, $asset->toArray(), $request);

        return response()->json($asset->load(['aplikasi', 'classification']), 201);
    }

    public function update(Request $request, DataAsset $dataAsset)
    {
        $before = $dataAsset->toArray();
        $dataAsset->update($this->withCalculatedClassification($this->validated($request)));
        $this->audit('update', $dataAsset, $before, $dataAsset->fresh()->toArray(), $request);

        return $dataAsset->fresh(['aplikasi', 'classification']);
    }

    public function destroy(Request $request, DataAsset $dataAsset)
    {
        $before = $dataAsset->toArray();
        $dataAsset->delete();
        $this->audit('delete', $dataAsset, $before, null, $request);

        return response()->noContent();
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'aplikasi_id' => ['required', 'uuid', 'exists:aplikasi,id'],
            'classification_id' => ['nullable', 'integer', 'exists:data_classifications,id'],
            'name' => ['required', 'string', 'max:150'],
            'type' => ['required', 'in:TABLE,COLUMN,API,FILE,FORM,DATASET'],
            'attributes' => ['nullable', 'string'],
            'owner_agency' => ['nullable', 'string', 'max:150'],
            'confidentiality_score' => ['required', 'integer', Rule::in([1, 3, 5])],
            'integrity_score' => ['required', 'integer', Rule::in([1, 3, 5])],
            'availability_score' => ['required', 'integer', Rule::in([1, 3, 5])],
            'table_name' => ['nullable', 'string', 'max:100'],
            'column_name' => ['nullable', 'string', 'max:100'],
            'contains_personal_data' => ['boolean'],
            'personal_data_type' => ['nullable', 'string', 'max:255'],
            'processing_purpose' => ['nullable', 'string', 'max:255'],
            'retention_period' => ['nullable', 'string', 'max:100'],
            'storage_location' => ['nullable', 'string', 'max:255'],
            'data_owner' => ['nullable', 'string', 'max:150'],
            'access_policy' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
        ]);
    }

    private function withCalculatedClassification(array $data): array
    {
        $total = (int) $data['confidentiality_score'] + (int) $data['integrity_score'] + (int) $data['availability_score'];
        $code = match (true) {
            $total <= 7 => 'OPEN',
            $total <= 11 => 'LIMITED',
            default => 'RESTRICTED',
        };

        $data['risk_total'] = $total;
        $data['classification_id'] = DataClassification::where('code', $code)->value('id') ?? $data['classification_id'] ?? null;

        return $data;
    }

    private function audit(string $action, DataAsset $asset, ?array $before, ?array $after, Request $request): void
    {
        AuditLog::create([
            'user_id' => $request->attributes->get('auth_user')?->id,
            'aksi' => $action,
            'tabel' => 'data_assets',
            'record_id' => (string) $asset->id,
            'before_data' => $before,
            'after_data' => $after,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }
}

