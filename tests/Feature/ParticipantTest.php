<?php

namespace Tests\Feature;

use App\Modules\Events\Models\Event;
use App\Modules\Participants\Models\Participant;
use App\Modules\Users\Models\Users;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ParticipantTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_add_participant_to_event(): void
    {
        $user = Users::factory()->create();
        $event = Event::factory()->create(['user_id' => $user->id]);
        Sanctum::actingAs($user);

        $response = $this->postJson("/api/events/{$event->id}/participants", [
            'name' => 'João Silva',
            'whatsapp_number' => '+5511999999999',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'name',
                'whatsapp_number',
                'is_confirmed',
            ]);

        $this->assertDatabaseHas('participants', [
            'event_id' => $event->id,
            'name' => 'João Silva',
        ]);
    }

    public function test_can_update_participant_confirmation(): void
    {
        $user = Users::factory()->create();
        $event = Event::factory()->create(['user_id' => $user->id]);
        $participant = Participant::factory()->create([
            'event_id' => $event->id,
            'is_confirmed' => false,
        ]);
        Sanctum::actingAs($user);

        $response = $this->putJson("/api/participants/{$participant->id}", [
            'is_confirmed' => true,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'is_confirmed' => true,
            ]);

        $this->assertDatabaseHas('participants', [
            'id' => $participant->id,
            'is_confirmed' => true,
        ]);
    }
}
