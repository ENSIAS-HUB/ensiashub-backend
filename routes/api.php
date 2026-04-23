<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\IotDeviceController;
use App\Http\Controllers\LaundryMachineController;
use App\Http\Controllers\DeviceEventController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::apiResource('iot-devices', IotDeviceController::class);
Route::apiResource('laundry-machine',LaundryMachineController::class);
Route::apiResource('device-events',DeviceEventController::class);