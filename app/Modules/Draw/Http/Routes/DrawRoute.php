<?php

namespace App\Modules\Draw\Http\Routes;

use App\Modules\Draw\Http\Controllers\DrawController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::post('events/{uuid}/draw', [DrawController::class, 'draw'])->where('uuid', REGEX_UUID);
    Route::get('events/{uuid}/draw-results', [DrawController::class, 'results'])->where('uuid', REGEX_UUID);
});
