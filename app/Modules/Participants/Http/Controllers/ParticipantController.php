<?php

namespace App\Modules\Participants\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Participants\Repositories\ParticipantRepository;
use App\Modules\Participants\Requests\StoreParticipantRequest;
use App\Modules\Participants\Requests\UpdateParticipantRequest;
use App\Http\Resources\ParticipantResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
    public function index(string $uuid, Request $request): AnonymousResourceCollection
    {
        $event = \App\Modules\Events\Models\Event::where('uuid', $uuid)->firstOrFail();

        $this->authorize('view', $event);

        $participants = $this->participantRepository->getByEvent($event, $request);

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
    public function update(UpdateParticipantRequest $request, string $uuid, int $id): ParticipantResource
    {
        $event = \App\Modules\Events\Models\Event::where('uuid', $uuid)->firstOrFail();

        $this->authorize('update', $event);

        $participant = $this->participantRepository->findById($id);

        if (!$participant || $participant->event_id !== $event->id) {
            abort(404, 'Participante não encontrado');
        }

        $participant->update($request->validated());

        return new ParticipantResource($participant);
    }

    /**
     * Remove a participant.
     */
    public function destroy(string $uuid, int $id): JsonResponse
    {
        $event = \App\Modules\Events\Models\Event::where('uuid', $uuid)->firstOrFail();

        $this->authorize('update', $event);

        $participant = $this->participantRepository->findById($id);

        if (!$participant || $participant->event_id !== $event->id) {
            abort(404, 'Participante não encontrado');
        }

        $this->participantRepository->delete($participant);

        return response()->json(['message' => 'Participante removido com sucesso']);
    }

    /**
     * Send suggestions reminder to all confirmed participants.
     */
    public function sendSugestionsReminder(string $uuid): JsonResponse
    {
        $event = \App\Modules\Events\Models\Event::where('uuid', $uuid)->firstOrFail();

        $this->authorize('update', $event);

        $this->participantRepository->sendSugestionsReminder($event);

        return response()->json(['message' => 'Mensagens enviadas com sucesso']);
    }
}
