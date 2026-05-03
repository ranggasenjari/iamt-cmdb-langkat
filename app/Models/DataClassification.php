<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DataClassification extends Model
{
    protected $fillable = [
        'code', 'name', 'risk_level', 'description',
        'requires_encryption', 'requires_mfa', 'requires_audit_log',
    ];

    protected $casts = [
        'requires_encryption' => 'boolean',
        'requires_mfa' => 'boolean',
        'requires_audit_log' => 'boolean',
    ];

    public function dataAssets(): HasMany
    {
        return $this->hasMany(DataAsset::class, 'classification_id');
    }
}
