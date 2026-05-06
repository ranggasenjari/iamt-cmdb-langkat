<?php

namespace App\Models;

use App\Models\Concerns\HasAssetCode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BackupMedia extends Model
{
    use HasAssetCode;

    protected $table = 'backup_media';

    protected $fillable = ['asset_code', 'nama', 'location', 'jenis_media', 'kapasitas_gb', 'address_url'];

    public function backupJobs(): HasMany
    {
        return $this->hasMany(BackupJob::class, 'backup_media_id');
    }
}
