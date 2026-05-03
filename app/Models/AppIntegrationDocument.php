<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppIntegrationDocument extends Model
{
    protected $fillable = ['integration_id', 'path', 'original_name', 'mime_type', 'size_bytes'];

    public function integration(): BelongsTo
    {
        return $this->belongsTo(AppIntegration::class, 'integration_id');
    }
}
