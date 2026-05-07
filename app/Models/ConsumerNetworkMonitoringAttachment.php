<?php

namespace App\Models;

use App\Models\Concerns\UsesUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsumerNetworkMonitoringAttachment extends Model
{
    use UsesUuid;

    protected $table = 'consumer_network_monitoring_attachments';

    protected $fillable = [
        'monitoring_id',
        'path',
        'original_name',
        'mime_type',
        'size_bytes',
    ];

    protected $appends = [
        'url',
        'is_image',
    ];

    public function monitoring(): BelongsTo
    {
        return $this->belongsTo(ConsumerNetworkMonitoring::class, 'monitoring_id');
    }

    public function getUrlAttribute(): string
    {
        return asset($this->path);
    }

    public function getIsImageAttribute(): bool
    {
        return str_starts_with((string) $this->mime_type, 'image/');
    }
}
