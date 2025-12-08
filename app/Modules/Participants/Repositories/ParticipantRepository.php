<?php

namespace App\Modules\Participants\Repositories;

use App\Modules\Participants\Models\Participant;
use App\Modules\Events\Models\Event;
use App\Plugins\WhatsApp;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class ParticipantRepository
{
    public function __construct(
        private Participant $model
    ) {
    }

    /**
     * Get all participants for an event.
     */
    public function getByEvent(Event $event, Request $request): Collection
    {
        if ($request->has('confirmed')) {
            return $event->participants()->where('is_confirmed', $request->confirmed)
                ->orderBy('name', 'asc')
                ->get();
        }

        return $event->participants()->orderBy('name', 'asc')->get();
    }

    /**
     * Get confirmed participants for an event.
     */
    public function getConfirmedByEvent(Event $event): Collection
    {
        return $event->participants()
            ->where('is_confirmed', true)
            ->get();
    }

    /**
     * Create a new participant.
     */
    public function create(Event $event, array $data): Participant
    {
        return $event->participants()->create($data);
    }

    /**
     * Find participant by ID.
     */
    public function findById(int $id): ?Participant
    {
        return $this->model->find($id);
    }

    /**
     * Update a participant.
     */
    public function update(string $uuid, Request $data): Participant
    {
        $participant = $this->model->where('uuid', $uuid)->firstOrFail();
        $participant->update($data->all());
        return $participant;
    }

    /**
     * Delete a participant.
     */
    public function delete(Participant $participant): bool
    {
        return $participant->delete();
    }

    public function sendSugestionsReminder(Event $event): bool
    {
        $participants = $event->participants()->where('is_confirmed', true)->get();
        foreach ($participants as $participant) {
            $giver = $participant->drawResultsAsReceiver->first()->giver;
            $message = "Já escolheu o presente para o seu amigo secreto? Se não, reveja a sugestão do seu amigo secreto, " . $giver->name . ": " . $giver->gift_suggestion;
            (new WhatsApp())->sendMessageText($message, (string)$participant->whatsapp_number, 'Olá, *' . $participant->name . '*!', 2);
        }
        return true;
    }
}
