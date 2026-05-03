<?php

namespace App\Models;

use App\Models\Concerns\UsesUuid;
use Illuminate\Database\Eloquent\Model;

class Pengguna extends Model
{
    use UsesUuid;

    protected $table = 'pengguna';

    protected $fillable = [
        'nama', 'email', 'password', 'opd_id', 'role_legacy', 'role',
        'status', 'api_token_hash', 'last_login_at',
    ];

    protected $hidden = ['password', 'api_token_hash'];

    protected $casts = [
        'last_login_at' => 'datetime',
    ];

    public function canWrite(): bool
    {
        return $this->role === 'full' && $this->status === 'aktif';
    }
}
