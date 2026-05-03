<?php

namespace App\Models;

use App\Models\Concerns\UsesUuid;
use Illuminate\Database\Eloquent\Model;

class SecurityTool extends Model
{
    use UsesUuid;

    protected $table = 'security_tools';

    protected $fillable = ['nama', 'jenis'];

    public $timestamps = false;
}
