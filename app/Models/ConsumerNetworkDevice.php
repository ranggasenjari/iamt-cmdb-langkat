<?php

namespace App\Models;

use App\Models\Concerns\HasAssetCode;
use App\Models\Concerns\UsesUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ConsumerNetworkDevice extends Model
{
    use HasAssetCode, UsesUuid;

    protected $table = 'consumer_network_devices';

    protected $fillable = [
        'asset_code', 'nama', 'jenis', 'status', 'kondisi', 'merk', 'model', 'serial_number',
        'os_firmware', 'mac_address', 'kapasitas_port', 'poe_support', 'wireless_standard',
        'frekuensi', 'bandwidth', 'deskripsi', 'management_ip', 'subnet_mask', 'gateway',
        'dns', 'vlan', 'ssid', 'ip_public', 'dhcp_enabled', 'ip_address_id',
        'upstream_device_id', 'dc_id', 'rack_id', 'opd_id', 'lokasi_instalasi',
        'titik_koordinat', 'tanggal_pasang', 'penanggung_jawab', 'management_url',
        'credential_username', 'credential_password', 'credential_notes', 'credential_updated_at',
    ];

    protected $hidden = [
        'credential_password',
    ];

    protected $appends = [
        'has_credential',
    ];

    protected $casts = [
        'poe_support' => 'boolean',
        'dhcp_enabled' => 'boolean',
        'tanggal_pasang' => 'date',
        'credential_password' => 'encrypted',
        'credential_updated_at' => 'datetime',
    ];

    public function dataCenter(): BelongsTo
    {
        return $this->belongsTo(DataCenter::class, 'dc_id');
    }

    public function rack(): BelongsTo
    {
        return $this->belongsTo(Rack::class, 'rack_id');
    }

    public function opd(): BelongsTo
    {
        return $this->belongsTo(Opd::class, 'opd_id');
    }

    public function ipAddress(): BelongsTo
    {
        return $this->belongsTo(IpAddress::class, 'ip_address_id');
    }

    public function upstreamDevice(): BelongsTo
    {
        return $this->belongsTo(self::class, 'upstream_device_id');
    }

    public function downstreamDevices(): HasMany
    {
        return $this->hasMany(self::class, 'upstream_device_id');
    }

    public function installations(): HasMany
    {
        return $this->hasMany(ConsumerNetworkInstallation::class, 'device_id');
    }

    public function activeInstallation(): HasOne
    {
        return $this->hasOne(ConsumerNetworkInstallation::class, 'device_id')
            ->where('status', 'aktif')
            ->orderByDesc('installed_at')
            ->orderByDesc('created_at');
    }

    public function ipConfigs(): HasMany
    {
        return $this->hasMany(ConsumerNetworkIpConfig::class, 'device_id');
    }

    public function credentials(): HasMany
    {
        return $this->hasMany(ConsumerNetworkCredential::class, 'device_id');
    }

    public function getHasCredentialAttribute(): bool
    {
        return filled($this->credential_username) || filled($this->credential_password);
    }
}
