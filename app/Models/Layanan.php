<?php

namespace App\Models;

use App\Models\Concerns\UsesUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Layanan extends Model
{
    use UsesUuid;

    protected $table = 'layanan';

    protected $fillable = ['nama', 'deskripsi', 'opd_id', 'status', 'kategori_data', 'pic_nama', 'pic_kontak', 'tanggal_go_live', 'risiko'];

    public $timestamps = false;

    public function aplikasi(): BelongsToMany
    {
        return $this->belongsToMany(Aplikasi::class, 'layanan_aplikasi', 'layanan_id', 'aplikasi_id');
    }
}
