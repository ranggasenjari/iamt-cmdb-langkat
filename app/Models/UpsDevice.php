<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UpsDevice extends Model
{
    protected $fillable = ['nama', 'kapasitas_va', 'kondisi', 'dc_id'];

    public function dataCenter(): BelongsTo
    {
        return $this->belongsTo(DataCenter::class, 'dc_id');
    }
}
