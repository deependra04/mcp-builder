<?php

use Illuminate\Support\Facades\Route;
use Laravel\McpBuilder\Http\Controllers\Api\ServerApiController;

$prefix = config('mcp-builder.dashboard.prefix', 'mcp-builder');
$middleware = config('mcp-builder.dashboard.middleware', ['web']);

Route::prefix("api/{$prefix}")->middleware($middleware)->group(function () {
    Route::get('/servers', [ServerApiController::class, 'index'])->name('api.mcp-builder.servers.index');
    Route::get('/servers/{id}', [ServerApiController::class, 'show'])->name('api.mcp-builder.servers.show');
    Route::post('/servers', [ServerApiController::class, 'store'])->name('api.mcp-builder.servers.store');
    Route::put('/servers/{id}', [ServerApiController::class, 'update'])->name('api.mcp-builder.servers.update');
    Route::delete('/servers/{id}', [ServerApiController::class, 'destroy'])->name('api.mcp-builder.servers.destroy');
});

