<?php

namespace App\Models;

use App\Models\Concerns\HasAssetCode;
use App\Models\Concerns\UsesUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ConsumerNetworkSite extends Model
{
    use HasAssetCode, UsesUuid;

    protected $table = 'consumer_network_sites';

    protected $fillable = [
        'asset_code',
        'kode',
        'nama',
        'jenis',
        'status',
        'opd_id',
        'dc_id',
        'rack_id',
        'alamat',
        'lokasi_detail',
        'titik_koordinat',
        'pic_nama',
        'pic_kontak',
        'catatan',
    ];

    public function opd(): BelongsTo
    {
        return $this->belongsTo(Opd::class, 'opd_id');
    }

    public function dataCenter(): BelongsTo
    {
        return $this->belongsTo(DataCenter::class, 'dc_id');
    }

    public function rack(): BelongsTo
    {
        return $this->belongsTo(Rack::class, 'rack_id');
    }

    public function installations(): HasMany
    {
        return $this->hasMany(ConsumerNetworkInstallation::class, 'site_id');
    }

    public function ipConfigs(): HasMany
    {
        return $this->hasMany(ConsumerNetworkIpConfig::class, 'site_id');
    }

    public function credentials(): HasMany
    {
        return $this->hasMany(ConsumerNetworkCredential::class, 'site_id');
    }
}
