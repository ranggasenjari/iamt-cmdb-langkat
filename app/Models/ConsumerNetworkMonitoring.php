<?php

namespace App\Models;

use App\Models\Concerns\HasAssetCode;
use App\Models\Concerns\UsesUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ConsumerNetworkMonitoring extends Model
{
    use HasAssetCode, UsesUuid;

    protected $table = 'consumer_network_monitorings';

    protected $fillable = [
        'asset_code',
        'site_id',
        'monitoring_at',
        'period_month',
        'officers',
        'speedtest_download_mbps',
        'speedtest_upload_mbps',
        'speedtest_ping_ms',
        'tower_available',
        'tower_besi_condition',
        'tower_kawat_condition',
        'tower_pondasi_condition',
        'tower_notes',
        'notes',
    ];

    protected $casts = [
        'monitoring_at' => 'datetime',
        'officers' => 'array',
        'speedtest_download_mbps' => 'decimal:2',
        'speedtest_upload_mbps' => 'decimal:2',
        'speedtest_ping_ms' => 'decimal:2',
        'tower_available' => 'boolean',
    ];

    public function site(): BelongsTo
    {
        return $this->belongsTo(ConsumerNetworkSite::class, 'site_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ConsumerNetworkMonitoringItem::class, 'monitoring_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(ConsumerNetworkMonitoringAttachment::class, 'monitoring_id');
    }
}
