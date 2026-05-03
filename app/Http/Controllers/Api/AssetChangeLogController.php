<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AssetChangeLog;
use Illuminate\Http\Request;

class AssetChangeLogController extends Controller
{
    public function __invoke(Request $request)
    {
        return AssetChangeLog::query()
            ->when($request->filled('asset_type'), fn ($query) => $query->where('asset_type', $request->string('asset_type')))
            ->when($request->filled('asset_id'), fn ($query) => $query->where('asset_id', $request->string('asset_id')))
            ->orderByDesc('created_at')
            ->limit(100)
            ->get();
    }
}
