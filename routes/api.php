<?php

use App\Http\Controllers\Api\V1\BodyMeasurementController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\NutritionController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::post('measurements/body', [BodyMeasurementController::class, 'store'])
        ->middleware('auth.homeassistant');

    Route::middleware('auth.health')->group(function (): void {
        Route::get('measurements/body/latest', [BodyMeasurementController::class, 'latest']);
        Route::get('nutrition/latest', [NutritionController::class, 'latest']);
        Route::get('dashboard', [DashboardController::class, 'show']);
    });
});
