<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LayerElementController;
use App\Http\Controllers\MapImportController;
use App\Http\Controllers\MapLayerController;
use App\Http\Controllers\SiteMapController;
use App\Http\Controllers\ViewportController;
use App\Http\Controllers\ViewportStateController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/sites/{site}/maps/{map}', [SiteMapController::class, 'show'])->name('api.maps.show');
    Route::get('/maps/{map}/state', [ViewportStateController::class, 'show'])->name('api.maps.state');

    Route::post('/maps/{map}/layers', [MapLayerController::class, 'store']);
    Route::put('/maps/{map}/layers/{layer}', [MapLayerController::class, 'update']);
    Route::delete('/maps/{map}/layers/{layer}', [MapLayerController::class, 'destroy']);
    Route::post('/maps/{map}/layers/{layer}/toggle', [MapLayerController::class, 'toggleVisibility']);
    Route::post('/sites/{site}/maps/import-map', [MapImportController::class, 'store'])->name('api.maps.import-map');
    Route::delete('/sites/{site}/maps/{map}', [MapImportController::class, 'destroy'])->name('api.maps.delete');

    Route::get('/layers/{layer}/elements', [LayerElementController::class, 'index']);
    Route::post('/layers/{layer}/elements', [LayerElementController::class, 'bulkUpsert']);

    Route::post('/maps/{map}/viewports', [ViewportController::class, 'store']);
    Route::put('/maps/{map}/viewports/{viewport}', [ViewportController::class, 'update']);
});
