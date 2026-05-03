<?php

namespace App\Models;

use App\Models\Concerns\UsesUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Server extends Model
{
    use UsesUuid;

    protected $table = 'server';

    protected $fillable = [
        'nama', 'dc_id', 'rack_id', 'merk', 'tipe', 'serial_number', 'merk_processor', 'cpu_core',
        'ram_gb', 'storage_gb', 'kondisi', 'status', 'tahun', 'penanggung_jawab',
    ];

    const UPDATED_AT = null;

    protected $casts = [
        'cpu_core' => 'integer',
        'ram_gb' => 'integer',
        'storage_gb' => 'integer',
        'tahun' => 'integer',
    ];

    public function dataCenter(): BelongsTo
    {
        return $this->belongsTo(DataCenter::class, 'dc_id');
    }

    public function rack(): BelongsTo
    {
        return $this->belongsTo(Rack::class, 'rack_id');
    }

    public function vms(): HasMany
    {
        return $this->hasMany(VirtualMachine::class, 'server_id');
    }

    public function aplikasi(): BelongsToMany
    {
        return $this->belongsToMany(Aplikasi::class, 'aplikasi_server', 'server_id', 'aplikasi_id');
    }
}
