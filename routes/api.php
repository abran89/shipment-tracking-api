<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PacketController;
use App\Http\Controllers\CarrierWebhookController;


Route::prefix('packets')->group(function () {
    Route::get('/', [PacketController::class, 'index']);
    Route::post('/', [PacketController::class, 'store']);
    Route::get('/{packet}', [PacketController::class, 'show']);
    Route::put('/{packet}/status', [PacketController::class, 'updateStatus']);
});

Route::post('/webhooks/carrier', CarrierWebhookController::class);