<?php

namespace App\Models;

use App\Models\Concerns\HasAssetCode;
use App\Models\Concerns\UsesUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Aplikasi extends Model
{
    use HasAssetCode, UsesUuid;

    protected $table = 'aplikasi';

    protected $fillable = [
        'asset_code', 'nama', 'url', 'opd_id', 'deskripsi', 'jenis_aplikasi', 'pengembang', 'klasifikasi_fungsi', 'tech_stack', 'status', 'sla_persen',
        'jam_operasional', 'kategori_data', 'mengandung_data_pribadi', 'jenis_data_pribadi',
        'retensi_data', 'pic_nama', 'pic_kontak', 'tanggal_go_live',
    ];

    protected $casts = [
        'mengandung_data_pribadi' => 'boolean',
        'klasifikasi_fungsi' => 'array',
        'sla_persen' => 'decimal:2',
        'tanggal_go_live' => 'date',
    ];

    public function opd(): BelongsTo
    {
        return $this->belongsTo(Opd::class, 'opd_id');
    }

    public function vms(): BelongsToMany
    {
        return $this->belongsToMany(VirtualMachine::class, 'aplikasi_vm', 'aplikasi_id', 'vm_id');
    }

    public function servers(): BelongsToMany
    {
        return $this->belongsToMany(Server::class, 'aplikasi_server', 'aplikasi_id', 'server_id');
    }

    public function ipAddresses(): BelongsToMany
    {
        return $this->belongsToMany(IpAddress::class, 'aplikasi_ip', 'aplikasi_id', 'ip_id');
    }

    public function layanan(): BelongsToMany
    {
        return $this->belongsToMany(Layanan::class, 'layanan_aplikasi', 'aplikasi_id', 'layanan_id');
    }

    public function dataAssets(): HasMany
    {
        return $this->hasMany(DataAsset::class, 'aplikasi_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(ApplicationDocument::class, 'aplikasi_id');
    }

    public function integrations(): HasMany
    {
        return $this->hasMany(AppIntegration::class, 'aplikasi_id');
    }

    public function backupJobs(): HasMany
    {
        return $this->hasMany(BackupJob::class, 'aplikasi_id');
    }

    public function databaseDocs(): HasMany
    {
        return $this->hasMany(AppDatabaseDoc::class, 'aplikasi_id');
    }
}
