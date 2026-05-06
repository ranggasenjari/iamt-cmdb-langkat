<?php

namespace App\Models;

use App\Models\Concerns\HasAssetCode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BackupJob extends Model
{
    use HasAssetCode;

    protected $table = 'backup_jobs';

    protected $fillable = ['asset_code', 'aplikasi_id', 'backup_media_id', 'retensi_n', 'retensi_unit', 'repetisi_n', 'repetisi_unit'];

    public function aplikasi(): BelongsTo
    {
        return $this->belongsTo(Aplikasi::class, 'aplikasi_id');
    }

    public function media(): BelongsTo
    {
        return $this->belongsTo(BackupMedia::class, 'backup_media_id');
    }
}
