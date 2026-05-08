<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\ConsumerNetworkMonitoring;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ConsumerNetworkMonitoringController extends Controller
{
    public function index()
    {
        return ConsumerNetworkMonitoring::with($this->relations())
            ->withCount('items')
            ->orderByDesc('monitoring_at')
            ->orderByDesc('created_at')
            ->get();
    }

    public function show(ConsumerNetworkMonitoring $networkMonitoring)
    {
        return $networkMonitoring->load($this->relations())
            ->loadCount('items');
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $items = $data['items'] ?? [];
        unset($data['items'], $data['attachments']);

        $monitoring = ConsumerNetworkMonitoring::create($this->modelData($data));
        $this->syncItems($monitoring, $items);
        $this->storeAttachments($monitoring, $request->file('attachments', []));
        $after = $monitoring->fresh($this->relations())->toArray();
        $this->audit('create', $monitoring, null, $after, $request);

        return response()->json($monitoring->fresh($this->relations())->loadCount('items'), 201);
    }

    public function update(Request $request, ConsumerNetworkMonitoring $networkMonitoring)
    {
        $before = $networkMonitoring->load($this->relations())->toArray();
        $data = $this->validated($request);
        $items = $data['items'] ?? [];
        $removeAttachmentIds = $data['remove_attachment_ids'] ?? [];
        unset($data['items'], $data['attachments'], $data['remove_attachment_ids']);

        $networkMonitoring->update($this->modelData($data));
        $this->syncItems($networkMonitoring, $items);
        $this->deleteSelectedAttachments($networkMonitoring, $removeAttachmentIds);
        $this->storeAttachments($networkMonitoring, $request->file('attachments', []));

        $after = $networkMonitoring->fresh($this->relations())->toArray();
        $this->audit('update', $networkMonitoring, $before, $after, $request);

        return $networkMonitoring->fresh($this->relations())->loadCount('items');
    }

    public function destroy(Request $request, ConsumerNetworkMonitoring $networkMonitoring)
    {
        $before = $networkMonitoring->load($this->relations())->toArray();
        $attachmentPaths = $networkMonitoring->attachments()->pluck('path');
        $networkMonitoring->delete();
        $attachmentPaths->each(fn ($path) => $this->deleteUpload($path));
        $this->audit('delete', $networkMonitoring, $before, null, $request);

        return response()->noContent();
    }

    private function validated(Request $request): array
    {
        $this->prepareArrayInputs($request);

        return $request->validate([
            'site_id' => ['required', 'uuid', 'exists:consumer_network_sites,id'],
            'monitoring_at' => ['required', 'date'],
            'period_month' => ['nullable', 'date_format:Y-m'],
            'officers' => ['nullable', 'array'],
            'officers.*' => ['nullable', 'string', 'max:255'],
            'speedtest_download_mbps' => ['nullable', 'numeric', 'min:0'],
            'speedtest_upload_mbps' => ['nullable', 'numeric', 'min:0'],
            'speedtest_ping_ms' => ['nullable', 'numeric', 'min:0'],
            'attachments' => ['nullable', 'array'],
            'attachments.*' => ['file', 'max:20480'],
            'remove_attachment_ids' => ['nullable', 'array'],
            'remove_attachment_ids.*' => ['uuid', 'exists:consumer_network_monitoring_attachments,id'],
            'tower_available' => ['boolean'],
            'tower_besi_condition' => ['nullable', 'in:baik,kurang_baik,rusak'],
            'tower_kawat_condition' => ['nullable', 'in:baik,kurang_baik,rusak'],
            'tower_pondasi_condition' => ['nullable', 'in:baik,kurang_baik,rusak'],
            'tower_notes' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'items' => ['nullable', 'array'],
            'items.*.device_id' => ['required', 'uuid', 'exists:consumer_network_devices,id'],
            'items.*.installation_id' => ['nullable', 'uuid', 'exists:consumer_network_installations,id'],
            'items.*.condition' => ['required', 'in:baik,kurang_baik,rusak'],
            'items.*.note' => ['nullable', 'string'],
        ]);
    }

    private function prepareArrayInputs(Request $request): void
    {
        foreach (['officers', 'items', 'remove_attachment_ids'] as $key) {
            if ($request->has($key)) {
                $request->merge([$key => $this->arrayInput($request->input($key))]);
            }
        }

        if ($request->has('tower_available')) {
            $request->merge(['tower_available' => $request->boolean('tower_available')]);
        }
    }

    private function arrayInput(mixed $value): array
    {
        if (is_array($value)) {
            return array_values(array_filter($value, fn ($item) => $item !== null && $item !== ''));
        }

        if (blank($value)) {
            return [];
        }

        $decoded = json_decode((string) $value, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return array_values(array_filter($decoded, fn ($item) => $item !== null && $item !== ''));
        }

        return collect(preg_split('/[\r\n,]+/', (string) $value))
            ->map(fn ($item) => trim($item))
            ->filter()
            ->values()
            ->all();
    }

    private function modelData(array $data): array
    {
        $data['tower_available'] = (bool) ($data['tower_available'] ?? false);
        $data['officers'] = collect($data['officers'] ?? [])
            ->map(fn ($officer) => trim((string) $officer))
            ->filter()
            ->values()
            ->all();

        if (blank($data['period_month'] ?? null) && filled($data['monitoring_at'] ?? null)) {
            $data['period_month'] = Carbon::parse($data['monitoring_at'])->format('Y-m');
        }

        if (! $data['tower_available']) {
            $data['tower_besi_condition'] = null;
            $data['tower_kawat_condition'] = null;
            $data['tower_pondasi_condition'] = null;
            $data['tower_notes'] = null;
        }

        return $data;
    }

    private function syncItems(ConsumerNetworkMonitoring $monitoring, array $items): void
    {
        $monitoring->items()->delete();

        foreach ($items as $item) {
            $monitoring->items()->create([
                'device_id' => $item['device_id'],
                'installation_id' => $item['installation_id'] ?? null,
                'condition' => $item['condition'],
                'note' => $item['note'] ?? null,
            ]);
        }
    }

    private function storeAttachments(ConsumerNetworkMonitoring $monitoring, array $files): void
    {
        foreach ($files as $file) {
            if (! $file) {
                continue;
            }

            $monitoring->attachments()->create($this->storeUpload($file));
        }
    }

    private function deleteSelectedAttachments(ConsumerNetworkMonitoring $monitoring, array $attachmentIds): void
    {
        if (! $attachmentIds) {
            return;
        }

        $attachments = $monitoring->attachments()
            ->whereIn('id', $attachmentIds)
            ->get();

        foreach ($attachments as $attachment) {
            $this->deleteUpload($attachment->path);
            $attachment->delete();
        }
    }

    private function storeUpload($file): array
    {
        $directory = public_path('uploads/network-monitoring-attachments');
        File::ensureDirectoryExists($directory);

        $originalName = $file->getClientOriginalName();
        $mimeType = $file->getMimeType();
        $size = $file->getSize();
        $filename = (string) Str::uuid().'.'.$file->getClientOriginalExtension();
        $file->move($directory, $filename);

        return [
            'path' => "uploads/network-monitoring-attachments/{$filename}",
            'original_name' => $originalName,
            'mime_type' => $mimeType,
            'size_bytes' => $size,
        ];
    }

    private function deleteUpload(?string $path): void
    {
        if (! $path) {
            return;
        }

        $fullPath = public_path($path);

        if (str_starts_with(realpath(dirname($fullPath)) ?: '', public_path('uploads'))
            && is_file($fullPath)) {
            File::delete($fullPath);
        }
    }

    private function relations(): array
    {
        return [
            'site:id,nama,kode,jenis,status,asset_code,alamat,lokasi_detail,pic_nama,pic_kontak',
            'items.device:id,nama,jenis,asset_code,merk,model,serial_number',
            'items.installation:id,site_id,device_id,role,status,installed_at',
            'attachments:id,monitoring_id,path,original_name,mime_type,size_bytes',
        ];
    }

    private function audit(string $action, ConsumerNetworkMonitoring $monitoring, ?array $before, ?array $after, Request $request): void
    {
        AuditLog::create([
            'user_id' => $request->attributes->get('auth_user')?->id,
            'aksi' => $action,
            'tabel' => 'consumer_network_monitorings',
            'record_id' => $monitoring->id,
            'before_data' => $before,
            'after_data' => $after,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }
}
