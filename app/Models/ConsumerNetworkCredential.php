<?php

namespace App\Models;

use App\Models\Concerns\UsesUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsumerNetworkCredential extends Model
{
    use UsesUuid;

    protected $table = 'consumer_network_credentials';

    protected $fillable = [
        'device_id',
        'site_id',
        'label',
        'access_method',
        'management_url',
        'username',
        'password',
        'notes',
        'last_rotated_at',
    ];

    protected $hidden = [
        'password',
    ];

    protected $appends = [
        'has_password',
    ];

    protected $casts = [
        'password' => 'encrypted',
        'last_rotated_at' => 'date',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(ConsumerNetworkDevice::class, 'device_id');
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(ConsumerNetworkSite::class, 'site_id');
    }

    public function getHasPasswordAttribute(): bool
    {
        return filled($this->password);
    }
}
