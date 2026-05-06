<?php

namespace App\Models;

use App\Models\Concerns\UsesUuid;
use App\Models\Concerns\HasAssetCode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DataCenter extends Model
{
    use HasAssetCode, UsesUuid;

    protected $table = 'data_center';

    protected $fillable = ['asset_code', 'nama', 'lokasi', 'tipe'];

    public $timestamps = false;

    public function racks(): HasMany
    {
        return $this->hasMany(Rack::class, 'dc_id');
    }
}
