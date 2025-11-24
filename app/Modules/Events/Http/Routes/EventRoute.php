<?php

namespace App\Modules\Events\Http\Routes;

use App\Modules\Events\Http\Controllers\EventConfirmedController;
use App\Modules\Events\Http\Controllers\EventController;
use Illuminate\Support\Facades\Route;

Route::post('events', [EventController::class, 'store']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('events', [EventController::class, 'index']);
    Route::get('events/{uuid}', [EventController::class, 'show'])->where('uuid', REGEX_UUID);
    Route::put('events/{uuid}', [EventController::class, 'update'])->where('uuid', REGEX_UUID);
    Route::delete('events/{uuid}', [EventController::class, 'destroy'])->where('uuid', REGEX_UUID);
});

Route::get('events/{uuid}/confirmed', [EventConfirmedController::class, 'show'])->where('uuid', REGEX_UUID);
