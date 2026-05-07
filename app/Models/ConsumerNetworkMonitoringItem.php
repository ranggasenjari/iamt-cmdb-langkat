<?php

namespace App\Models;

use App\Models\Concerns\UsesUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsumerNetworkMonitoringItem extends Model
{
    use UsesUuid;

    protected $table = 'consumer_network_monitoring_items';

    protected $fillable = [
        'monitoring_id',
        'device_id',
        'installation_id',
        'condition',
        'note',
    ];

    public function monitoring(): BelongsTo
    {
        return $this->belongsTo(ConsumerNetworkMonitoring::class, 'monitoring_id');
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(ConsumerNetworkDevice::class, 'device_id');
    }

    public function installation(): BelongsTo
    {
        return $this->belongsTo(ConsumerNetworkInstallation::class, 'installation_id');
    }
}
