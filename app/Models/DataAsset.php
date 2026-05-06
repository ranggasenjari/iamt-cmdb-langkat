<?php

namespace App\Models;

use App\Models\Concerns\HasAssetCode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DataAsset extends Model
{
    use HasAssetCode;

    protected $fillable = [
        'asset_code', 'aplikasi_id', 'classification_id', 'name', 'type', 'table_name', 'column_name',
        'attributes', 'owner_agency', 'confidentiality_score', 'integrity_score',
        'availability_score', 'risk_total',
        'contains_personal_data', 'personal_data_type', 'processing_purpose',
        'retention_period', 'storage_location', 'data_owner', 'access_policy', 'description',
    ];

    protected $casts = [
        'contains_personal_data' => 'boolean',
    ];

    public function aplikasi(): BelongsTo
    {
        return $this->belongsTo(Aplikasi::class, 'aplikasi_id');
    }

    public function classification(): BelongsTo
    {
        return $this->belongsTo(DataClassification::class, 'classification_id');
    }
}
