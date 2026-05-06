<?php

use App\Http\Controllers\PublicAssetController;
use Illuminate\Support\Facades\Route;

Route::get('/asset/{module}/{id}', PublicAssetController::class)->where('module', '[A-Za-z0-9-]+');
Route::view('/{any?}', 'app')->where('any', '^(?!api).*$');
