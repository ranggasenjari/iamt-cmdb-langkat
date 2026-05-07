<?php

namespace App\Models;

use App\Models\Concerns\UsesUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsumerNetworkIpConfig extends Model
{
    use UsesUuid;

    protected $table = 'consumer_network_ip_configs';

    protected $fillable = [
        'device_id',
        'site_id',
        'ip_address_id',
        'interface_name',
        'ip_type',
        'ip_address',
        'subnet_mask',
        'gateway',
        'dns',
        'vlan',
        'ssid',
        'dhcp_enabled',
        'status',
        'notes',
    ];

    protected $casts = [
        'dhcp_enabled' => 'boolean',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(ConsumerNetworkDevice::class, 'device_id');
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(ConsumerNetworkSite::class, 'site_id');
    }

    public function ipAddressRecord(): BelongsTo
    {
        return $this->belongsTo(IpAddress::class, 'ip_address_id');
    }
}
