<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Order> */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'order_number' => Order::generateNumber(),
            'user_id' => User::factory(),
            'player_id' => (string) fake()->numberBetween(100000, 999999),
            'server_id' => null,
            'subtotal' => 10000,
            'discount_amount' => 0,
            'total' => 10000,
            'status' => OrderStatus::Pending,
            'payment_status' => PaymentStatus::Pending,
            'payment_method' => 'qris',
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Order $order) {
            $order->items()->create([
                'game_name' => 'Game Uji', 'product_name' => '100 Poin',
                'unit_price' => $order->subtotal, 'quantity' => 1, 'subtotal' => $order->subtotal,
            ]);
            $order->payments()->create([
                'reference' => 'SIM-'.strtoupper(fake()->unique()->bothify('????????')),
                'driver' => 'fake', 'method' => $order->payment_method,
                'amount' => $order->total, 'status' => $order->payment_status,
            ]);
        });
    }

    public function paid(): static
    {
        return $this->state(fn () => ['status' => OrderStatus::Processing, 'payment_status' => PaymentStatus::Paid, 'paid_at' => now()]);
    }
}
