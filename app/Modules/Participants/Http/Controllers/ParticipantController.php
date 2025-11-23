<?php

namespace App\Modules\Participants\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Events\Models\Event;
use App\Modules\Participants\Models\Participant;
use App\Modules\Participants\Repositories\ParticipantRepository;
use App\Modules\Participants\Requests\StoreParticipantRequest;
use App\Modules\Participants\Requests\UpdateParticipantRequest;
use App\Http\Resources\ParticipantResource;
use App\Plugins\WhatsApp;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ParticipantController extends Controller
{
    public function __construct(
        private ParticipantRepository $participantRepository
    ) {
    }

    /**
     * List participants of an event.
     */
    public function index(string $uuid): AnonymousResourceCollection
    {
        $event = \App\Modules\Events\Models\Event::where('uuid', $uuid)->firstOrFail();

        $this->authorize('view', $event);

        $participants = $this->participantRepository->getByEvent($event);

        return ParticipantResource::collection($participants);
    }

    /**
     * Add a participant to an event.
     */
    public function store(StoreParticipantRequest $request, string $uuid): ParticipantResource
    {
        $event = \App\Modules\Events\Models\Event::where('uuid', $uuid)->firstOrFail();

        $this->authorize('update', $event);

        $participant = $this->participantRepository->create($event, $request->validated());

        return new ParticipantResource($participant);
    }

    /**
     * Update a participant.
     */
    public function update(UpdateParticipantRequest $request, string $uuid): ParticipantResource
    {
        $event = \App\Modules\Events\Models\Event::where('uuid', $uuid)->firstOrFail();

        $this->authorize('update', $event);

        $participant = $this->participantRepository->update($uuid, $request);

        return new ParticipantResource($participant);
    }

    /**
         * Send a WhatsApp code to a participant.
     */
    public function sendWhatsappCode(UpdateParticipantRequest $request, string $uuid): JsonResponse
    {
        $event = \App\Modules\Events\Models\Event::where('uuid', $uuid)->firstOrFail();

        $this->authorize('update', $event);

        $participant = $this->participantRepository->update($uuid, $request);

        (new WhatsApp())->sendMessageText(
            "Código de verificação: " . $participant->code . "\n\nUse o código para confirmar sua presença no evento.",
            $participant->whatsapp_number,
            $participant->name,
            2
        );

        return response()->json(['message' => 'Código de verificação enviado com sucesso']);
    }

    /**
     * Remove a participant.
     */
    public function destroy(Participant $participant): JsonResponse
    {
        $this->authorize('update', $participant->event);

        $this->participantRepository->delete($participant);

        return response()->json(['message' => 'Participante removido com sucesso']);
    }
}
