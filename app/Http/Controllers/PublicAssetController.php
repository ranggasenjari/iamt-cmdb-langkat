<?php

namespace App\Http\Controllers;

use App\Models\Aplikasi;
use App\Models\AppIntegration;
use App\Models\ApplicationDocument;
use App\Models\BackupJob;
use App\Models\BackupMedia;
use App\Models\ConsumerNetworkDevice;
use App\Models\DataAsset;
use App\Models\DataCenter;
use App\Models\IpAddress;
use App\Models\Isp;
use App\Models\Rack;
use App\Models\Server;
use App\Models\SocTool;
use App\Models\UpsDevice;
use App\Models\VirtualMachine;
use Illuminate\Contracts\View\View;

class PublicAssetController extends Controller
{
    public function __invoke(string $module, string $id): View
    {
        abort_unless(array_key_exists($module, $this->modules()), 404);

        $asset = $this->publicAsset($module, $id);

        return view('public-asset', [
            'asset' => $asset,
            'logoDataUri' => $this->logoDataUri(),
        ]);
    }

    private function publicAsset(string $module, string $id): array
    {
        $query = match ($module) {
            'data-centers' => DataCenter::query()->withCount('racks'),
            'racks' => Rack::query()->with('dataCenter:id,nama,lokasi'),
            'servers' => Server::query()->with(['dataCenter:id,nama,lokasi', 'rack:id,nama']),
            'vms' => VirtualMachine::query()->with('server:id,nama,asset_code'),
            'isps' => Isp::query()->withCount('ipAddresses'),
            'ip-addresses' => IpAddress::query()->with('isp:id,nama,asset_code'),
            'applications' => Aplikasi::query()->with('opd:id,nama'),
            'data-assets' => DataAsset::query()->with(['aplikasi:id,nama,asset_code', 'classification:id,code,name,risk_level']),
            'application-documents' => ApplicationDocument::query()->with('aplikasi:id,nama,asset_code'),
            'app-integrations' => AppIntegration::query()->with(['aplikasi:id,nama,asset_code', 'targetApplications:id,nama', 'dataAssets:id,name']),
            'backup-media' => BackupMedia::query()->withCount('backupJobs'),
            'backup-jobs' => BackupJob::query()->with(['aplikasi:id,nama,asset_code', 'media:id,nama,asset_code']),
            'ups-devices' => UpsDevice::query()->with('dataCenter:id,nama,lokasi'),
            'soc-tools' => SocTool::query()->with(['dataCenters:id,nama', 'servers:id,nama', 'vms:id,nama', 'applications:id,nama']),
            'network-devices' => ConsumerNetworkDevice::query()->with(['dataCenter:id,nama,lokasi', 'rack:id,nama', 'opd:id,nama', 'upstreamDevice:id,nama']),
        };

        $row = $query->findOrFail($id);

        return [
            'module' => $module,
            'module_label' => $this->modules()[$module],
            'asset_code' => $row->asset_code,
            'name' => $this->nameFor($module, $row),
            'status' => $this->statusFor($module, $row),
            'location' => $this->locationFor($module, $row),
            'details' => $this->detailsFor($module, $row),
        ];
    }

    private function modules(): array
    {
        return [
            'data-centers' => 'Data Center',
            'racks' => 'Rack',
            'servers' => 'Server',
            'vms' => 'VM / CT',
            'isps' => 'ISP',
            'ip-addresses' => 'IP Address',
            'applications' => 'Aplikasi',
            'data-assets' => 'Data Aplikasi',
            'application-documents' => 'Dokumen',
            'app-integrations' => 'Interoperabilitas',
            'backup-media' => 'Media Pencadangan',
            'backup-jobs' => 'Pencadangan',
            'ups-devices' => 'UPS / Power Backup',
            'soc-tools' => 'SOC',
            'network-devices' => 'Consumer Networking',
        ];
    }

    private function nameFor(string $module, object $row): string
    {
        return match ($module) {
            'ip-addresses' => $row->ip ?? '-',
            'data-assets' => $row->name ?? '-',
            'application-documents' => $row->original_name ?? $row->nama ?? '-',
            'app-integrations' => 'Integrasi '.$row->aplikasi?->nama,
            'backup-jobs' => 'Backup '.$row->aplikasi?->nama,
            default => $row->nama ?? '-',
        };
    }

    private function statusFor(string $module, object $row): string
    {
        return match ($module) {
            'data-centers' => $row->tipe ?? '-',
            'racks' => trim(($row->kapasitas_u ?? '-').'U'),
            'servers', 'vms', 'applications' => $row->status ?? '-',
            'isps' => $row->tipe ?? '-',
            'ip-addresses' => $row->jenis ?? '-',
            'data-assets' => $row->classification?->name ?? '-',
            'application-documents' => $row->document_category ?? $row->jenis ?? '-',
            'app-integrations' => trim(($row->jenis_integrasi ?? '-').' / '.($row->metode_integrasi ?? '-')),
            'backup-media' => trim(($row->jenis_media ?? '-').' / '.($row->location ?? '-')),
            'backup-jobs' => trim(($row->repetisi_n ?? '-').' '.($row->repetisi_unit ?? '')),
            'ups-devices' => $row->kondisi ?? '-',
            'soc-tools' => $row->jenis ?? '-',
            'network-devices' => trim(($row->jenis ?? '-').' / '.($row->status ?? '-')),
            default => '-',
        };
    }

    private function locationFor(string $module, object $row): string
    {
        return match ($module) {
            'data-centers' => $row->lokasi ?? '-',
            'racks' => collect([$row->dataCenter?->nama, $row->dataCenter?->lokasi])->filter()->join(' / ') ?: '-',
            'servers' => collect([$row->dataCenter?->nama, $row->rack?->nama])->filter()->join(' / ') ?: '-',
            'vms' => $row->server?->nama ?? '-',
            'isps' => $row->bandwidth ?? '-',
            'ip-addresses' => $row->isp?->nama ?? '-',
            'applications' => $row->opd?->nama ?? '-',
            'data-assets' => $row->aplikasi?->nama ?? '-',
            'application-documents' => $row->aplikasi?->nama ?? '-',
            'app-integrations' => $row->targetApplications->pluck('nama')->join(', ') ?: '-',
            'backup-media' => $row->location ?? '-',
            'backup-jobs' => $row->media?->nama ?? '-',
            'ups-devices' => collect([$row->dataCenter?->nama, $row->dataCenter?->lokasi])->filter()->join(' / ') ?: '-',
            'soc-tools' => 'DC '.$row->dataCenters->count().' / Server '.$row->servers->count().' / VM '.$row->vms->count().' / Aplikasi '.$row->applications->count(),
            'network-devices' => collect([$row->dataCenter?->nama, $row->rack?->nama, $row->opd?->nama, $row->lokasi_instalasi])->filter()->join(' / ') ?: '-',
            default => '-',
        };
    }

    private function detailsFor(string $module, object $row): array
    {
        return match ($module) {
            'servers' => [
                'Merk / Tipe' => trim(($row->merk ?? '-').' '.($row->tipe ?? '')),
                'Processor' => $row->merk_processor ?? '-',
                'Kapasitas' => ($row->cpu_core ?? 0).' core / '.($row->ram_gb ?? 0).' GB RAM / '.($row->storage_gb ?? 0).' GB storage',
            ],
            'vms' => [
                'Host' => $row->server?->nama ?? '-',
                'OS' => $row->os ?? '-',
                'Kapasitas' => ($row->vcpu ?? 0).' vCPU / '.($row->ram_gb ?? 0).' GB RAM / '.($row->storage_gb ?? 0).' GB storage',
            ],
            'applications' => [
                'Jenis' => $row->jenis_aplikasi ?? '-',
                'Pengembang' => $row->pengembang ?? '-',
            ],
            'data-assets' => [
                'Tipe Data' => $row->type ?? '-',
                'Klasifikasi' => $row->classification?->code ?? '-',
                'Aplikasi' => $row->aplikasi?->nama ?? '-',
            ],
            'backup-jobs' => [
                'Retensi' => ($row->retensi_n ?? '-').' '.($row->retensi_unit ?? ''),
                'Repetisi' => ($row->repetisi_n ?? '-').' '.($row->repetisi_unit ?? ''),
                'Media' => $row->media?->nama ?? '-',
            ],
            'network-devices' => [
                'Merk / Model' => trim(($row->merk ?? '-').' '.($row->model ?? '')),
                'Firmware' => $row->os_firmware ?? '-',
                'Uplink' => $row->upstreamDevice?->nama ?? '-',
            ],
            default => [],
        };
    }

    private function logoDataUri(): ?string
    {
        $path = resource_path('img/logo_langkat.png');

        if (! is_file($path)) {
            return null;
        }

        return 'data:image/png;base64,'.base64_encode(file_get_contents($path));
    }
}
