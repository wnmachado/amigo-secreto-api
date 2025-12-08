<?php

namespace App\Modules\Participants\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Participants\Models\Participant;
use App\Modules\Participants\Repositories\ParticipantRepository;
use App\Http\Resources\ParticipantResource;
use App\Modules\Participants\Requests\UpdateGiftSugestionRequest;
use App\Plugins\WhatsApp;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ParticipantConfirmedController extends Controller
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
        $participants = $this->participantRepository->getByEvent($event, $request);
        return ParticipantResource::collection($participants);
    }

    /**
     * Send a WhatsApp code to a participant.
     */
    public function sendWhatsappCode(string $uuid, int $id, Request $request): JsonResponse
    {
        $event = \App\Modules\Events\Models\Event::where('uuid', $uuid)->firstOrFail();

        $participant = $this->participantRepository->findById($id);

        if (!$participant || $participant->event_id !== $event->id) {
            abort(404, 'Participante não encontrado');
        }

        // Generate or get verification code
        $code = str_pad((string) rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $request->merge(['code' => $code]);
        $participant->update($request->all());

        $message = "Você foi convidado para participar do evento *" . $event->title . "*.";
        if ($event->description) {
            $message .= "\n\nInformações do evento:" . $event->description;
        }
        $message .= "\n\nData do evento: " . $event->event_date->format('d/m/Y');
        $message .= "\nValor mínimo: " . $event->min_value;
        $message .= "\nValor máximo: " . $event->max_value;
        $message .= "\n\nUse o código para confirmar sua presença no evento:";
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

        $request->validate(['code' => ['required', 'size:6'], 'whatsapp_number' => ['required', 'string', 'max:255']]);

        $participant = $this->participantRepository->findById($id);

        if (!$participant || $participant->event_id !== $event->id || $participant->code != $request->code || $participant->whatsapp_number != $request->whatsapp_number) {
            abort(404, 'Participante não encontrado ou código de verificação inválido');
        }

        $participant->update(['is_confirmed' => true, 'code' => null]);

        return response()->json(['message' => 'Presença confirmada com sucesso']);
    }

    public function update(UpdateGiftSugestionRequest $request, string $uuid, int $id): ParticipantResource
    {
        $event = \App\Modules\Events\Models\Event::where('uuid', $uuid)->firstOrFail();

        $participant = Participant::with('drawResultsAsReceiver.giver')
            ->where('event_id', $event->id)
            ->find($id);

        if (!$participant || $participant->event_id !== $event->id) {
            abort(404, 'Participante não encontrado');
        }

        $participant->update($request->validated());

        // enviar mensagem para o participante que tirou o participante que está sendo atualizado que o presente foi alterado
        $giver = $participant->drawResultsAsReceiver->first()->giver;
        $message = "Seu amigo secreto *" . $participant->name . "* alterou a sugestão de presente para: " . $participant->gift_suggestion;
        (new WhatsApp())->sendMessageText($message, (string)$giver->whatsapp_number, 'Olá, *' . $giver->name . '*!', 2);

        return new ParticipantResource($participant);
    }
}
