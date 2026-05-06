<?php

namespace App\Models;

use App\Models\Concerns\HasAssetCode;
use App\Models\Concerns\UsesUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Isp extends Model
{
    use HasAssetCode, UsesUuid;

    protected $table = 'isp';

    protected $fillable = ['asset_code', 'nama', 'tipe', 'bandwidth', 'kontak'];

    public $timestamps = false;

    public function ipAddresses(): HasMany
    {
        return $this->hasMany(IpAddress::class, 'isp_id');
    }
}
