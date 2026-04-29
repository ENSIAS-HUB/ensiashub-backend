<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\IotDeviceController;
use App\Http\Controllers\LaundryMachineController;
use App\Http\Controllers\DeviceEventController;
use App\Http\Controllers\MenuItemController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderLineController;
use App\Http\Controllers\AuthController;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::apiResource('iot-devices', IotDeviceController::class);
Route::apiResource('laundry-machine',LaundryMachineController::class);
Route::apiResource('device-events',DeviceEventController::class);
Route::apiResource('menu-items', MenuItemController::class);
Route::apiResource('orders', OrderController::class);
Route::apiResource('orders', OrderController::class);
Route::apiResource('order-lines', OrderLineController::class);
Route::get('/auth/redirect/{provider}', [AuthController::class, 'redirect']);
Route::get('/auth/callback/{provider}', [AuthController::class, 'callback']);