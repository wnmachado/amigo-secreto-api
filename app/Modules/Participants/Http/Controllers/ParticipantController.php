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
     * Send a WhatsApp code to a participant.
     */
    public function sendWhatsappCode(string $uuid, int $id, Request $request): JsonResponse
    {
        $event = \App\Modules\Events\Models\Event::where('uuid', $uuid)->firstOrFail();

        $this->authorize('update', $event);

        $participant = $this->participantRepository->findById($id);

        if (!$participant || $participant->event_id !== $event->id) {
            abort(404, 'Participante não encontrado');
        }

        // Generate or get verification code
        $code = str_pad((string) rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $request->merge(['code' => $code]);
        $participant->update($request->all());

        $message = "Você foi convidado para participar do evento *" . $event->title . "*.\n\nUse o código para confirmar sua presença no evento.";
        $message .= "\n\nCódigo de verificação: " . $participant->code;

        (new WhatsApp())->sendMessageText(
            $message,
            (string)$participant->whatsapp_number,
            'Olá, *' . $participant->name . '*!',
            2
        );

        return response()->json(['message' => 'Código de verificação enviado com sucesso']);
    }

    /**
     * Verify a WhatsApp code for a participant.
     */
    public function verifyWhatsappCode(string $uuid, int $id, Request $request): JsonResponse
    {
        $event = \App\Modules\Events\Models\Event::where('uuid', $uuid)->firstOrFail();

        $this->authorize('update', $event);

        $request->validate([
            'code' => ['required', 'size:6'],
        ]);

        $participant = $this->participantRepository->findById($id);

        if (!$participant || $participant->event_id !== $event->id || $participant->code != $request->code || $participant->whatsapp_number != $request->whatsapp_number) {
            abort(404, 'Participante não encontrado ou código de verificação inválido');
        }

        $participant->update(['is_confirmed' => true, 'code' => null]);

        return response()->json(['message' => 'Código de verificação verificado com sucesso']);
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
}
