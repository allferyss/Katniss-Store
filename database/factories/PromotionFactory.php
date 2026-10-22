<?php

namespace Database\Factories;

use App\Models\Promotion;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Promotion> */
class PromotionFactory extends Factory
{
    protected $model = Promotion::class;

    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->unique()->bothify('PROMO####')),
            'name' => 'Promo uji',
            'type' => Promotion::TYPE_PERCENT,
            'value' => 10,
            'min_purchase' => 0,
            'is_active' => true,
            'used_count' => 0,
        ];
    }
}
