<?php

use App\Http\Controllers\Api\ApplicationController;
use App\Http\Controllers\Api\ApplicationDocumentController;
use App\Http\Controllers\Api\AppIntegrationController;
use App\Http\Controllers\Api\AssetChangeLogController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BackupJobController;
use App\Http\Controllers\Api\BackupMediaController;
use App\Http\Controllers\Api\CmdbController;
use App\Http\Controllers\Api\DataAssetController;
use App\Http\Controllers\Api\DataClassificationController;
use App\Http\Controllers\Api\DataCenterController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\IpAddressController;
use App\Http\Controllers\Api\IspController;
use App\Http\Controllers\Api\RackController;
use App\Http\Controllers\Api\ReferenceController;
use App\Http\Controllers\Api\ServerController;
use App\Http\Controllers\Api\SocToolController;
use App\Http\Controllers\Api\UpsDeviceController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\VirtualMachineController;
use Illuminate\Support\Facades\Route;

Route::post('/auth/login', [AuthController::class, 'login']);

Route::middleware('auth.api_token')->group(function () {
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    Route::get('/dashboard', DashboardController::class);
    Route::get('/references', ReferenceController::class);
    Route::get('/dependency-map', [CmdbController::class, 'dependencyMap']);
    Route::get('/impact/server/{server}', [CmdbController::class, 'serverImpact']);
    Route::get('/compliance', [CmdbController::class, 'compliance']);
    Route::get('/audit-log', [CmdbController::class, 'auditLog']);
    Route::get('/asset-change-logs', AssetChangeLogController::class);

    Route::apiResource('/servers', ServerController::class)->only(['index', 'show']);
    Route::apiResource('/vms', VirtualMachineController::class)->only(['index', 'show']);
    Route::apiResource('/applications', ApplicationController::class)->only(['index', 'show']);
    Route::apiResource('/application-documents', ApplicationDocumentController::class)->parameters(['application-documents' => 'applicationDocument'])->only(['index', 'show']);
    Route::apiResource('/app-integrations', AppIntegrationController::class)->parameters(['app-integrations' => 'appIntegration'])->only(['index', 'show']);
    Route::apiResource('/backup-media', BackupMediaController::class)->parameters(['backup-media' => 'backupMedia'])->only(['index', 'show']);
    Route::apiResource('/backup-jobs', BackupJobController::class)->parameters(['backup-jobs' => 'backupJob'])->only(['index', 'show']);
    Route::apiResource('/ups-devices', UpsDeviceController::class)->parameters(['ups-devices' => 'upsDevice'])->only(['index', 'show']);
    Route::apiResource('/soc-tools', SocToolController::class)->parameters(['soc-tools' => 'socTool'])->only(['index', 'show']);
    Route::apiResource('/data-assets', DataAssetController::class)->parameters(['data-assets' => 'dataAsset'])->only(['index', 'show']);
    Route::apiResource('/data-classifications', DataClassificationController::class)->parameters(['data-classifications' => 'dataClassification'])->only(['index', 'show']);
    Route::apiResource('/data-centers', DataCenterController::class)->parameters(['data-centers' => 'dataCenter'])->only(['index', 'show']);
    Route::apiResource('/racks', RackController::class)->only(['index', 'show']);
    Route::apiResource('/isps', IspController::class)->only(['index', 'show']);
    Route::apiResource('/ip-addresses', IpAddressController::class)->parameters(['ip-addresses' => 'ipAddress'])->only(['index', 'show']);
    Route::apiResource('/users', UserController::class)->parameters(['users' => 'user'])->only(['index', 'show']);

    Route::middleware('role.full')->group(function () {
        Route::apiResource('/servers', ServerController::class)->only(['store', 'update', 'destroy']);
        Route::apiResource('/vms', VirtualMachineController::class)->only(['store', 'update', 'destroy']);
        Route::apiResource('/applications', ApplicationController::class)->only(['store', 'update', 'destroy']);
        Route::apiResource('/application-documents', ApplicationDocumentController::class)->parameters(['application-documents' => 'applicationDocument'])->only(['store', 'update', 'destroy']);
        Route::apiResource('/app-integrations', AppIntegrationController::class)->parameters(['app-integrations' => 'appIntegration'])->only(['store', 'update', 'destroy']);
        Route::apiResource('/backup-media', BackupMediaController::class)->parameters(['backup-media' => 'backupMedia'])->only(['store', 'update', 'destroy']);
        Route::apiResource('/backup-jobs', BackupJobController::class)->parameters(['backup-jobs' => 'backupJob'])->only(['store', 'update', 'destroy']);
        Route::apiResource('/ups-devices', UpsDeviceController::class)->parameters(['ups-devices' => 'upsDevice'])->only(['store', 'update', 'destroy']);
        Route::apiResource('/soc-tools', SocToolController::class)->parameters(['soc-tools' => 'socTool'])->only(['store', 'update', 'destroy']);
        Route::apiResource('/data-assets', DataAssetController::class)->parameters(['data-assets' => 'dataAsset'])->only(['store', 'update', 'destroy']);
        Route::apiResource('/data-classifications', DataClassificationController::class)->parameters(['data-classifications' => 'dataClassification'])->only(['store', 'update', 'destroy']);
        Route::apiResource('/data-centers', DataCenterController::class)->parameters(['data-centers' => 'dataCenter'])->only(['store', 'update', 'destroy']);
        Route::apiResource('/racks', RackController::class)->only(['store', 'update', 'destroy']);
        Route::apiResource('/isps', IspController::class)->only(['store', 'update', 'destroy']);
        Route::apiResource('/ip-addresses', IpAddressController::class)->parameters(['ip-addresses' => 'ipAddress'])->only(['store', 'update', 'destroy']);
        Route::apiResource('/users', UserController::class)->parameters(['users' => 'user'])->only(['store', 'update', 'destroy']);
    });
});
