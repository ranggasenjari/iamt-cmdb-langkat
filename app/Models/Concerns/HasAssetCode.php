<?php

namespace App\Models\Concerns;

use App\Support\AssetCodeGenerator;

trait HasAssetCode
{
    protected static function bootHasAssetCode(): void
    {
        static::creating(function ($model) {
            if (empty($model->asset_code)) {
                $model->asset_code = AssetCodeGenerator::next($model->getTable());
            }
        });
    }
}
