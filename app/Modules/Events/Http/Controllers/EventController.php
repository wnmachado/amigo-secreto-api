<?php

namespace App\Modules\Events\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Events\Repositories\EventRepository;
use App\Modules\Events\Requests\StoreEventRequest;
use App\Modules\Events\Requests\UpdateEventRequest;
use App\Http\Resources\EventResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class EventController extends Controller
{
    public function __construct(
        private EventRepository $eventRepository
    ) {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): AnonymousResourceCollection
    {
        $status = request()->has('status') ? request('status') : null;
        $events = $this->eventRepository->getByUser(auth()->user(), $status);

        return EventResource::collection($events);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreEventRequest $request): EventResource
    {
        $event = $this->eventRepository->create($request);

        return new EventResource($event);
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

        $this->authorize('view', $event);

        $event->loadCount('participants');
        $event->loadCount(['participants as confirmed_participants_count' => function ($query) {
            $query->where('is_confirmed', true);
        }]);

        return response()->json($event, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEventRequest $request, string $uuid): EventResource
    {
        $event = $this->eventRepository->findByUuid($uuid);

        if (!$event) {
            abort(404, 'Evento não encontrado');
        }

        $this->authorize('update', $event);

        $event = $this->eventRepository->update($event, $request->validated());

        return new EventResource($event);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $uuid): JsonResponse
    {
        $event = $this->eventRepository->findByUuid($uuid);

        if (!$event) {
            abort(404, 'Evento não encontrado');
        }

        $this->authorize('delete', $event);

        $this->eventRepository->delete($event);

        return response()->json(['message' => 'Evento removido com sucesso']);
    }
}
