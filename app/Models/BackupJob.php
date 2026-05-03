<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BackupJob extends Model
{
    protected $table = 'backup_jobs';

    protected $fillable = ['aplikasi_id', 'backup_media_id', 'retensi_n', 'retensi_unit', 'repetisi_n', 'repetisi_unit'];

    public function aplikasi(): BelongsTo
    {
        return $this->belongsTo(Aplikasi::class, 'aplikasi_id');
    }

    public function media(): BelongsTo
    {
        return $this->belongsTo(BackupMedia::class, 'backup_media_id');
    }
}
