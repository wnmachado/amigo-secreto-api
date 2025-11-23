<?php

namespace App\Modules\Draw\Repositories;

use App\Modules\Draw\Models\DrawResult;
use App\Modules\Events\Models\Event;
use App\Modules\Participants\Models\Participant;
use Illuminate\Database\Eloquent\Collection;

class DrawResultRepository
{
    public function __construct(
        private DrawResult $model
    ) {
    }

    /**
     * Create a draw result.
     */
    public function create(Event $event, Participant $giver, Participant $receiver): DrawResult
    {
        return $this->model->create([
            'event_id' => $event->id,
            'giver_participant_id' => $giver->id,
            'receiver_participant_id' => $receiver->id,
        ]);
    }

    /**
     * Get all draw results for an event.
     */
    public function getByEvent(Event $event): Collection
    {
        return $event->drawResults()->with(['giver', 'receiver'])->get();
    }

    /**
     * Check if draw results exist for an event.
     */
    public function existsForEvent(Event $event): bool
    {
        return $event->drawResults()->exists();
    }
}
