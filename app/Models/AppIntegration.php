<?php

namespace App\Models;

use App\Models\Concerns\HasAssetCode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AppIntegration extends Model
{
    use HasAssetCode;

    protected $fillable = ['asset_code', 'aplikasi_id', 'deskripsi', 'jenis_integrasi', 'metode_integrasi', 'external_endpoints'];

    public function aplikasi(): BelongsTo
    {
        return $this->belongsTo(Aplikasi::class, 'aplikasi_id');
    }

    public function targetApplications(): BelongsToMany
    {
        return $this->belongsToMany(Aplikasi::class, 'app_integration_targets', 'integration_id', 'target_aplikasi_id');
    }

    public function dataAssets(): BelongsToMany
    {
        return $this->belongsToMany(DataAsset::class, 'app_integration_data_assets', 'integration_id', 'data_asset_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(AppIntegrationDocument::class, 'integration_id');
    }
}
