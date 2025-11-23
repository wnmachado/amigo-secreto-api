<?php

namespace App\Modules\Events\Repositories;

use App\Modules\Events\Models\Event;
use App\Modules\Users\Models\Users;
use App\Modules\Users\Repositories\UserRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class EventRepository
{
    public function __construct(
        private Event $model
    ) {
    }

    /**
     * Get all events for a user.
     */
    public function getByUser(Users $user, ?string $status = null): Collection
    {
        $query = $this->model->where('user_id', $user->id)
            ->withCount('participants')
            ->withCount(['participants as confirmed_participants_count' => function ($query) {
                $query->where('is_confirmed', true);
            }]);

        if ($status) {
            $query->where('status', $status);
        }

        return $query->get();
    }

    /**
     * Create a new event.
     */
    public function create(Request $request): Event
    {
        if (!Auth::check()) {
            $user = (new UserRepository())->create($request->all());
            Auth::login($user);
        }
        $request->merge(['uuid' => Str::uuid(), 'user_id' => Auth::user()->id]);
        return $this->model->create($request->all());
    }

    /**
     * Find event by ID.
     */
    public function findById(int $id): ?Event
    {
        return $this->model->find($id);
    }

    /**
     * Find event by UUID.
     */
    public function findByUuid(string $uuid): ?Event
    {
        return $this->model->where('uuid', $uuid)->first();
    }

    /**
     * Update an event.
     */
    public function update(Event $event, array $data): Event
    {
        $event->update($data);

        $event->loadCount('participants');
        $event->loadCount(['participants as confirmed_participants_count' => function ($query) {
            $query->where('is_confirmed', true);
        }]);

        return $event;
    }

    /**
     * Delete an event.
     */
    public function delete(Event $event): bool
    {
        return $event->delete();
    }
}
