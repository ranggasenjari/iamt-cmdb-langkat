<?php

namespace App\Models;

use App\Models\Concerns\HasAssetCode;
use App\Models\Concerns\UsesUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rack extends Model
{
    use HasAssetCode, UsesUuid;

    protected $table = 'rack';

    protected $fillable = ['asset_code', 'dc_id', 'nama', 'kapasitas_u'];

    public $timestamps = false;

    public function dataCenter(): BelongsTo
    {
        return $this->belongsTo(DataCenter::class, 'dc_id');
    }

    public function servers(): HasMany
    {
        return $this->hasMany(Server::class, 'rack_id');
    }
}
