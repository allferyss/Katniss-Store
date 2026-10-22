<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Game;
use App\Models\Order;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    private function payload(Product $product, array $override = []): array
    {
        return array_merge([
            'product_id' => $product->id,
            'player_id' => '123456789',
            'payment_method' => 'qris',
            'idempotency_key' => (string) Str::uuid(),
        ], $override);
    }

    public function test_guest_cannot_create_order(): void
    {
        $product = Product::factory()->create();

        $this->post('/checkout', $this->payload($product))->assertRedirect(route('login'));
        $this->assertSame(0, Order::count());
    }

    public function test_checkout_summary_page_loads(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 25000]);

        $this->actingAs($user)->get('/checkout?product_id='.$product->id.'&player_id=123456')
            ->assertOk()->assertSee('Rp 25.000');
    }

    public function test_order_is_created_with_server_side_price_and_snapshot(): void
    {
        $user = User::factory()->create();
        $game = Game::factory()->create(['name' => 'Alpha Quest']);
        $product = Product::factory()->for($game)->create(['name' => '100 Gem', 'price' => 10000]);

        // Client mencoba memanipulasi harga & status: semuanya harus diabaikan.
        $response = $this->actingAs($user)->post('/checkout', $this->payload($product, [
            'price' => 1, 'total' => 1, 'subtotal' => 1,
            'status' => 'completed', 'payment_status' => 'paid',
        ]));

        $order = Order::with('items', 'payments')->firstOrFail();
        $response->assertRedirect(route('orders.show', $order));

        $this->assertSame(10000, $order->total);
        $this->assertSame(OrderStatus::Pending, $order->status);
        $this->assertSame(PaymentStatus::Pending, $order->payment_status);
        $this->assertSame($user->id, $order->user_id);
        $this->assertMatchesRegularExpression('/^KS-\d{8}-[A-Z0-9]{10}$/', $order->order_number);
        $this->assertCount(1, $order->items);
        $this->assertSame('Alpha Quest', $order->items[0]->game_name);
        $this->assertSame('100 Gem', $order->items[0]->product_name);
        $this->assertSame(10000, $order->items[0]->unit_price);
        $this->assertCount(1, $order->payments);
        $this->assertSame(10000, $order->payments[0]->amount);
    }

    public function test_price_change_does_not_affect_existing_order(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['name' => '100 Gem', 'price' => 10000]);

        $this->actingAs($user)->post('/checkout', $this->payload($product));

        $product->update(['price' => 99000, 'name' => 'Nama Baru']);

        $order = Order::with('items')->firstOrFail();
        $this->assertSame(10000, $order->total);
        $this->assertSame(10000, $order->items[0]->unit_price);
        $this->assertSame('100 Gem', $order->items[0]->product_name);
    }

    public function test_player_id_is_required_and_validated(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $this->actingAs($user)->post('/checkout', $this->payload($product, ['player_id' => '']))->assertSessionHasErrors('player_id');
        $this->actingAs($user)->post('/checkout', $this->payload($product, ['player_id' => '12 <script>']))->assertSessionHasErrors('player_id');
        $this->assertSame(0, Order::count());
    }

    public function test_server_id_required_only_for_games_that_need_it(): void
    {
        $user = User::factory()->create();
        $withServer = Product::factory()->for(Game::factory()->withServerId())->create();
        $without = Product::factory()->create();

        $this->actingAs($user)->post('/checkout', $this->payload($withServer))->assertSessionHasErrors('server_id');
        $this->assertSame(0, Order::count());

        $this->actingAs($user)->post('/checkout', $this->payload($withServer, ['server_id' => '2001']))->assertSessionDoesntHaveErrors();
        $this->assertSame('2001', Order::firstOrFail()->server_id);

        // Server ID dari client diabaikan bila game tidak membutuhkannya.
        $this->actingAs($user)->post('/checkout', $this->payload($without, ['server_id' => '999']));
        $this->assertNull(Order::latest('id')->firstOrFail()->server_id);
    }

    public function test_invalid_product_and_payment_method_are_rejected(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $this->actingAs($user)->post('/checkout', $this->payload($product, ['product_id' => 9999]))->assertSessionHasErrors('product_id');
        $this->actingAs($user)->post('/checkout', $this->payload($product, ['payment_method' => 'gratis']))->assertSessionHasErrors('payment_method');
        $this->assertSame(0, Order::count());
    }

    public function test_inactive_product_or_game_cannot_be_ordered(): void
    {
        $user = User::factory()->create();
        $inactiveProduct = Product::factory()->inactive()->create();
        $inactiveGame = Product::factory()->for(Game::factory()->inactive())->create();

        $this->actingAs($user)->post('/checkout', $this->payload($inactiveProduct))->assertSessionHasErrors('product_id');
        $this->actingAs($user)->post('/checkout', $this->payload($inactiveGame))->assertSessionHasErrors('product_id');
        $this->assertSame(0, Order::count());
    }

    public function test_duplicate_submission_with_same_idempotency_key_creates_one_order(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $payload = $this->payload($product);

        $this->actingAs($user)->post('/checkout', $payload);
        $this->actingAs($user)->post('/checkout', $payload);

        $this->assertSame(1, Order::count());
        $this->assertSame(1, \App\Models\Payment::count());
    }

    public function test_promo_is_applied_server_side_and_counts_usage(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 20000]);
        $promo = Promotion::factory()->create(['code' => 'HEMAT10', 'value' => 10]);

        $this->actingAs($user)->post('/checkout', $this->payload($product, ['promo_code' => 'hemat10']));

        $order = Order::firstOrFail();
        $this->assertSame(2000, $order->discount_amount);
        $this->assertSame(18000, $order->total);
        $this->assertSame('HEMAT10', $order->promo_code);
        $this->assertSame(1, $promo->fresh()->used_count);
    }

    public function test_expired_or_exhausted_promo_is_rejected(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 20000]);
        Promotion::factory()->create(['code' => 'LAMA', 'ends_at' => now()->subDay()]);
        Promotion::factory()->create(['code' => 'HABIS', 'usage_limit' => 1, 'used_count' => 1]);

        $this->actingAs($user)->post('/checkout', $this->payload($product, ['promo_code' => 'LAMA']))->assertSessionHasErrors('promo_code');
        $this->actingAs($user)->post('/checkout', $this->payload($product, ['promo_code' => 'HABIS']))->assertSessionHasErrors('promo_code');
        $this->assertSame(0, Order::count());
    }

    public function test_discount_never_makes_total_below_minimum(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 5000]);
        Promotion::factory()->create(['code' => 'BESAR', 'type' => Promotion::TYPE_FIXED, 'value' => 999999]);

        $this->actingAs($user)->post('/checkout', $this->payload($product, ['promo_code' => 'BESAR']));

        $this->assertSame(1000, Order::firstOrFail()->total);
    }
}
