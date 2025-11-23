<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Modules\Events\Models\Event>
 */
class EventFactory extends Factory
{
    protected $model = \App\Modules\Events\Models\Event::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'uuid' => \Illuminate\Support\Str::uuid(),
            'user_id' => \App\Modules\Users\Models\Users::factory(),
            'title' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'event_date' => fake()->dateTimeBetween('now', '+1 year'),
            'min_value' => fake()->randomFloat(2, 10, 50),
            'max_value' => fake()->randomFloat(2, 100, 500),
            'status' => 'draft',
        ];
    }
}
