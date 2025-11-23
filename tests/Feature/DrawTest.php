<?php

namespace Tests\Feature;

use App\Modules\Events\Models\Event;
use App\Modules\Participants\Models\Participant;
use App\Modules\Users\Models\Users;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DrawTest extends TestCase
{
    use RefreshDatabase;

    public function test_draw_generates_valid_pairs(): void
    {
        $user = Users::factory()->create();
        $event = Event::factory()->create(['user_id' => $user->id]);

        // Create 5 confirmed participants
        Participant::factory()->count(5)->create([
            'event_id' => $event->id,
            'is_confirmed' => true,
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson("/api/events/{$event->id}/draw");

        $response->assertStatus(200)
            ->assertJsonStructure([
                '*' => [
                    'giver' => ['id', 'name'],
                    'receiver' => ['id', 'name'],
                ],
            ]);

        // Verify no one draws themselves
        $pairs = $response->json();
        foreach ($pairs as $pair) {
            $this->assertNotEquals(
                $pair['giver']['id'],
                $pair['receiver']['id'],
                'Um participante não pode tirar a si mesmo'
            );
        }

        // Verify event status was updated
        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'status' => 'draw_done',
        ]);
    }

    public function test_draw_disallow_when_few_participants(): void
    {
        $user = Users::factory()->create();
        $event = Event::factory()->create(['user_id' => $user->id]);

        // Create only 2 confirmed participants
        Participant::factory()->count(2)->create([
            'event_id' => $event->id,
            'is_confirmed' => true,
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson("/api/events/{$event->id}/draw");

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'É necessário pelo menos 3 participantes confirmados para realizar o sorteio.',
            ]);
    }
}
