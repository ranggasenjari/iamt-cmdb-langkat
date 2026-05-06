<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\DataClassification;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DataClassificationController extends Controller
{
    public function index()
    {
        return DataClassification::orderBy('id')->get();
    }

    public function show(DataClassification $dataClassification)
    {
        return $dataClassification->loadCount('dataAssets');
    }

    public function store(Request $request)
    {
        $classification = DataClassification::create($this->validated($request));
        $this->audit('create', $classification, null, $classification->toArray(), $request);

        return response()->json($classification, 201);
    }

    public function update(Request $request, DataClassification $dataClassification)
    {
        $before = $dataClassification->toArray();
        $dataClassification->update($this->validated($request, $dataClassification));
        $this->audit('update', $dataClassification, $before, $dataClassification->fresh()->toArray(), $request);

        return $dataClassification->fresh();
    }

    public function destroy(Request $request, DataClassification $dataClassification)
    {
        $before = $dataClassification->toArray();
        $dataClassification->delete();
        $this->audit('delete', $dataClassification, $before, null, $request);

        return response()->noContent();
    }

    private function validated(Request $request, ?DataClassification $dataClassification = null): array
    {
        return $request->validate([
            'code' => ['required', 'string', 'max:20', Rule::unique('data_classifications', 'code')->ignore($dataClassification?->id)],
            'name' => ['required', 'string', 'max:100'],
            'risk_level' => ['required', 'in:LOW,MEDIUM,HIGH'],
            'description' => ['nullable', 'string'],
            'requires_encryption' => ['boolean'],
            'requires_mfa' => ['boolean'],
            'requires_audit_log' => ['boolean'],
        ]);
    }

    private function audit(string $action, DataClassification $classification, ?array $before, ?array $after, Request $request): void
    {
        AuditLog::create([
            'user_id' => $request->attributes->get('auth_user')?->id,
            'aksi' => $action,
            'tabel' => 'data_classifications',
            'record_id' => (string) $classification->id,
            'before_data' => $before,
            'after_data' => $after,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }
}
