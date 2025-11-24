<?php

namespace App\Modules\Participants\Http\Routes;

use App\Modules\Participants\Http\Controllers\ParticipantConfirmedController;
use App\Modules\Participants\Http\Controllers\ParticipantController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('events/{uuid}/participants', [ParticipantController::class, 'index'])->where('uuid', REGEX_UUID);
    Route::post('events/{uuid}/participants', [ParticipantController::class, 'store'])->where('uuid', REGEX_UUID);
    Route::put('events/{uuid}/participants/{id}', [ParticipantController::class, 'update'])->where('uuid', REGEX_UUID)->where('id', REGEX_ID);
    Route::delete('events/{uuid}/participants/{id}', [ParticipantController::class, 'destroy'])->where('uuid', REGEX_UUID)->where('id', REGEX_ID);
});

Route::get('events/{uuid}/confirmed/participants', [ParticipantConfirmedController::class, 'index'])->where('uuid', REGEX_UUID);
Route::post('events/{uuid}/confirmed/participants/{id}/send-whatsapp-code', [ParticipantConfirmedController::class, 'sendWhatsappCode'])->where('uuid', REGEX_UUID)->where('id', REGEX_ID);
Route::post('events/{uuid}/confirmed/participants/{id}/verify-whatsapp-code', [ParticipantConfirmedController::class, 'verifyWhatsappCode'])->where('uuid', REGEX_UUID)->where('id', REGEX_ID);
