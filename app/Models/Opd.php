<?php

namespace App\Models;

use App\Models\Concerns\UsesUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Opd extends Model
{
    use UsesUuid;

    protected $table = 'opd';

    protected $fillable = ['nama', 'kontak'];

    public $timestamps = false;

    public function aplikasi(): HasMany
    {
        return $this->hasMany(Aplikasi::class, 'opd_id');
    }
}
