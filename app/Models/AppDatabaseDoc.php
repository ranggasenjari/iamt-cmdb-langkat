<?php

namespace App\Models;

use App\Models\Concerns\HasAssetCode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppDatabaseDoc extends Model
{
    use HasAssetCode;

    protected $table = 'app_database_docs';

    protected $fillable = [
        'asset_code', 'aplikasi_id', 'nama_database', 'tipe_dbms', 'versi',
        'host', 'port', 'nama_db_asli', 'jumlah_tabel',
        'file_path', 'original_name', 'mime_type', 'size_bytes', 'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'port' => 'integer',
            'jumlah_tabel' => 'integer',
            'size_bytes' => 'integer',
        ];
    }

    public function aplikasi(): BelongsTo
    {
        return $this->belongsTo(Aplikasi::class, 'aplikasi_id');
    }
}
