<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DataCenter;
use App\Models\DataClassification;
use App\Models\BackupMedia;
use App\Models\ConsumerNetworkDevice;
use App\Models\ConsumerNetworkSite;
use App\Models\IpAddress;
use App\Models\Isp;
use App\Models\Opd;
use App\Models\Rack;
use App\Models\Server;
use App\Models\VirtualMachine;

class ReferenceController extends Controller
{
    public function __invoke(): array
    {
        return [
            'opd' => Opd::orderBy('nama')->get(['id', 'nama']),
            'classifications' => DataClassification::orderBy('id')->get(['id', 'code', 'name', 'risk_level', 'requires_encryption', 'requires_mfa', 'requires_audit_log']),
            'data_centers' => DataCenter::orderBy('nama')->get(['id', 'nama', 'lokasi', 'tipe']),
            'racks' => Rack::orderBy('nama')->get(['id', 'nama', 'dc_id']),
            'isps' => Isp::orderBy('nama')->get(['id', 'nama', 'tipe', 'bandwidth']),
            'servers' => Server::orderBy('nama')->get(['id', 'nama', 'status']),
            'vms' => VirtualMachine::orderBy('nama')->get(['id', 'nama', 'server_id', 'status']),
            'ips' => IpAddress::orderBy('ip')->get(['id', 'ip', 'jenis']),
            'backup_media' => BackupMedia::orderBy('nama')->get(['id', 'nama', 'location', 'jenis_media']),
            'network_devices' => ConsumerNetworkDevice::orderBy('nama')->get(['id', 'nama', 'jenis', 'asset_code', 'management_ip']),
            'network_sites' => ConsumerNetworkSite::orderBy('nama')->get(['id', 'nama', 'kode', 'jenis', 'asset_code']),
        ];
    }
}
