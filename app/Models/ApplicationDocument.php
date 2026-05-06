<?php

namespace App\Models;

use App\Models\Concerns\HasAssetCode;
use App\Models\Concerns\UsesUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationDocument extends Model
{
    use HasAssetCode, UsesUuid;

    protected $table = 'aplikasi_dokumen';

    protected $fillable = [
        'asset_code', 'aplikasi_id', 'jenis', 'document_category', 'nama', 'url', 'path',
        'original_name', 'mime_type', 'size_bytes', 'versi', 'tanggal',
    ];

    public $timestamps = false;

    public function aplikasi(): BelongsTo
    {
        return $this->belongsTo(Aplikasi::class, 'aplikasi_id');
    }
}
