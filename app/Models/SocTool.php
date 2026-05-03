<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SocTool extends Model
{
    protected $fillable = ['nama', 'deskripsi_fungsi', 'jenis'];

    public function dataCenters(): BelongsToMany
    {
        return $this->belongsToMany(DataCenter::class, 'soc_tool_data_center', 'soc_tool_id', 'dc_id');
    }

    public function servers(): BelongsToMany
    {
        return $this->belongsToMany(Server::class, 'soc_tool_server', 'soc_tool_id', 'server_id');
    }

    public function vms(): BelongsToMany
    {
        return $this->belongsToMany(VirtualMachine::class, 'soc_tool_vm', 'soc_tool_id', 'vm_id');
    }

    public function applications(): BelongsToMany
    {
        return $this->belongsToMany(Aplikasi::class, 'soc_tool_aplikasi', 'soc_tool_id', 'aplikasi_id');
    }
}
