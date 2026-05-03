<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $table = 'audit_log';

    protected $fillable = ['user_id', 'aksi', 'tabel', 'record_id', 'before_data', 'after_data', 'ip_address', 'user_agent'];

    const UPDATED_AT = null;

    protected $casts = [
        'before_data' => 'array',
        'after_data' => 'array',
    ];
}
