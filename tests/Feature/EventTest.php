<?php

namespace Tests\Feature;

use App\Modules\Events\Models\Event;
use App\Modules\Users\Models\Users;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class EventTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_event(): void
    {
        $user = Users::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/events', [
            'title' => 'Natal 2024',
            'description' => 'Amigo secreto de Natal',
            'event_date' => '2024-12-25 20:00:00',
            'min_value' => 50.00,
            'max_value' => 100.00,
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'title',
                'description',
                'event_date',
                'min_value',
                'max_value',
                'status',
            ]);

        $this->assertDatabaseHas('events', [
            'user_id' => $user->id,
            'title' => 'Natal 2024',
        ]);
    }

    public function test_authenticated_user_can_list_own_events(): void
    {
        $user = Users::factory()->create();
        Sanctum::actingAs($user);

        Event::factory()->count(3)->create(['user_id' => $user->id]);
        Event::factory()->count(2)->create(); // Other user's events

        $response = $this->getJson('/api/events');

        $response->assertStatus(200)
            ->assertJsonCount(3);
    }

    public function test_user_cannot_access_events_of_others(): void
    {
        $user = Users::factory()->create();
        $otherUser = Users::factory()->create();
        Sanctum::actingAs($user);

        $event = Event::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->getJson("/api/events/{$event->id}");

        $response->assertStatus(403);
    }
}
