<?php

namespace App\Modules\Participants\Repositories;

use App\Modules\Participants\Models\Participant;
use App\Modules\Events\Models\Event;
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
            return $event->participants()->where('is_confirmed', $request->confirmed)->get();
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
}
