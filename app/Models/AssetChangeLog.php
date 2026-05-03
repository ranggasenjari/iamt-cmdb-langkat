<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetChangeLog extends Model
{
    protected $fillable = [
        'asset_type', 'asset_id', 'asset_name', 'user_id', 'change_type', 'changed_fields',
        'reason', 'changed_by', 'ip_address', 'user_agent',
    ];

    protected $casts = [
        'changed_fields' => 'array',
    ];
}
