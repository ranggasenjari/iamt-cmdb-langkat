<?php

namespace App\Models;

use App\Models\Concerns\HasAssetCode;
use App\Models\Concerns\UsesUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class IpAddress extends Model
{
    use HasAssetCode, UsesUuid;

    protected $table = 'ip_address';

    protected $fillable = ['asset_code', 'ip', 'jenis', 'isp_id'];

    public $timestamps = false;

    public function isp(): BelongsTo
    {
        return $this->belongsTo(Isp::class, 'isp_id');
    }

    public function vms(): BelongsToMany
    {
        return $this->belongsToMany(VirtualMachine::class, 'vm_ip', 'ip_id', 'vm_id');
    }
}
