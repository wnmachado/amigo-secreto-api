<?php

namespace App\Modules\Participants\Http\Routes;

use App\Modules\Participants\Http\Controllers\ParticipantController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('events/{uuid}/participants', [ParticipantController::class, 'index'])->where('uuid', REGEX_UUID);
    Route::post('events/{uuid}/participants', [ParticipantController::class, 'store'])->where('uuid', REGEX_UUID);
    Route::put('events/{uuid}/participants/{id}', [ParticipantController::class, 'update'])->where('uuid', REGEX_UUID)->where('id', REGEX_UUID);
    Route::post('events/{uuid}/participants/{id}/send-whatsapp-code', [ParticipantController::class, 'sendWhatsappCode'])->where('uuid', REGEX_UUID)->where('id', REGEX_UUID);
    Route::delete('events/{uuid}/participants/{id}', [ParticipantController::class, 'destroy'])->where('uuid', REGEX_UUID)->where('id', REGEX_UUID);
});
