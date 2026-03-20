<?php

use App\Http\Controllers\PacketController;
use Illuminate\Support\Facades\Route;

Route::prefix('packets')->group(function () {
    Route::get('/', [PacketController::class, 'index']);
    Route::post('/', [PacketController::class, 'store']);
    Route::get('/{packet}', [PacketController::class, 'show']);
    Route::put('/{packet}/status', [PacketController::class, 'updateStatus']);
});