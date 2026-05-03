<?php

namespace App\Models;

use App\Models\Concerns\UsesUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class VirtualMachine extends Model
{
    use UsesUuid;

    protected $table = 'vm';

    protected $fillable = ['nama', 'server_id', 'os', 'vcpu', 'ram_gb', 'storage_gb', 'status'];

    public $timestamps = false;

    protected $casts = [
        'vcpu' => 'integer',
        'ram_gb' => 'integer',
        'storage_gb' => 'integer',
    ];

    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class, 'server_id');
    }

    public function aplikasi(): BelongsToMany
    {
        return $this->belongsToMany(Aplikasi::class, 'aplikasi_vm', 'vm_id', 'aplikasi_id');
    }

    public function ipAddresses(): BelongsToMany
    {
        return $this->belongsToMany(IpAddress::class, 'vm_ip', 'vm_id', 'ip_id');
    }
}
