<?php

use Illuminate\Support\Facades\Route;
use Laravel\McpBuilder\Http\Controllers\DashboardController;
use Laravel\McpBuilder\Http\Controllers\ServerController;
use Laravel\McpBuilder\Http\Controllers\ToolController;

$prefix = config('mcp-builder.dashboard.prefix', 'mcp-builder');
$middleware = array_merge(
    config('mcp-builder.dashboard.middleware', ['web']),
    [\Laravel\McpBuilder\Middleware\AuthMcpBuilder::class]
);

Route::prefix($prefix)->middleware($middleware)->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('mcp-builder.dashboard');
    
    // Server routes
    Route::prefix('servers')->name('servers.')->group(function () {
        Route::get('/', [ServerController::class, 'index'])->name('index');
        Route::get('/create', [ServerController::class, 'create'])->name('create');
        Route::post('/', [ServerController::class, 'store'])->name('store');
        Route::get('/{id}', [ServerController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [ServerController::class, 'edit'])->name('edit');
        Route::put('/{id}', [ServerController::class, 'update'])->name('update');
        Route::delete('/{id}', [ServerController::class, 'destroy'])->name('destroy');
    });
    
    // Tool routes
    Route::prefix('tools')->name('tools.')->group(function () {
        Route::get('/', [ToolController::class, 'index'])->name('index');
        Route::get('/{id}', [ToolController::class, 'show'])->name('show');
    });
});

