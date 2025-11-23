<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Modules\Participants\Models\Participant>
 */
class ParticipantFactory extends Factory
{
    protected $model = \App\Modules\Participants\Models\Participant::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_id' => \App\Modules\Events\Models\Event::factory(),
            'name' => fake()->name(),
            'whatsapp_number' => fake()->phoneNumber(),
            'is_confirmed' => fake()->boolean(),
        ];
    }
}
