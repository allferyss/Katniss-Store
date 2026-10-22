<?php

namespace Database\Factories;

use App\Models\Game;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Game> */
class GameFactory extends Factory
{
    protected $model = Game::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'name' => Str::title($name),
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1, 99999),
            'publisher' => fake()->company(),
            'description' => fake()->sentence(),
            'id_hint' => 'Buka profil di dalam game untuk melihat ID.',
            'requires_server_id' => false,
            'is_active' => true,
            'sort_order' => 0,
        ];
    }

    public function withServerId(): static
    {
        return $this->state(fn () => ['requires_server_id' => true]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
