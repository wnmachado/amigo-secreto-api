<?php

namespace App\Modules\Events\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Events\Repositories\EventRepository;

class EventConfirmedController extends Controller
{
    public function __construct(
        private EventRepository $eventRepository
    ) {
    }

    /**
     * Display the specified resource.
     */
    public function show(string $uuid)
    {
        $event = $this->eventRepository->findByUuid($uuid);

        if (!$event) {
            abort(404, 'Evento não encontrado');
        }

        $event->loadCount('participants');
        $event->loadCount(['participants as confirmed_participants_count' => function ($query) {
            $query->where('is_confirmed', true);
        }]);
        $event->load('pairs');

        return response()->json($event, 200);
    }
}
