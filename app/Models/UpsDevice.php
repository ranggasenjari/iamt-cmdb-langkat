<?php

namespace App\Models;

use App\Models\Concerns\HasAssetCode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UpsDevice extends Model
{
    use HasAssetCode;

    protected $fillable = ['asset_code', 'nama', 'kapasitas_va', 'kondisi', 'dc_id'];

    public function dataCenter(): BelongsTo
    {
        return $this->belongsTo(DataCenter::class, 'dc_id');
    }
}
