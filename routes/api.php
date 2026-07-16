<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ExternalDistributionController;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/distributions', [ExternalDistributionController::class, 'store']);
});
