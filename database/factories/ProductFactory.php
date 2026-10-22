<?php

namespace Database\Factories;

use App\Models\Game;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Product> */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'game_id' => Game::factory(),
            'name' => fake()->numberBetween(10, 500).' Poin',
            'price' => 10000,
            'cost_price' => 9000,
            'is_active' => true,
            'sort_order' => 0,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
