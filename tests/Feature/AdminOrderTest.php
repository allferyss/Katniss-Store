<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Promotion;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminOrderTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    public function test_admin_can_list_filter_and_view_orders(): void
    {
        $admin = $this->admin();
        $a = Order::factory()->create(['player_id' => 'PLAYERAAA']);
        $b = Order::factory()->paid()->create(['player_id' => 'PLAYERBBB']);

        $this->actingAs($admin)->get('/admin/orders')->assertOk()->assertSee($a->order_number)->assertSee($b->order_number);
        $this->actingAs($admin)->get('/admin/orders?q=PLAYERAAA')->assertSee($a->order_number)->assertDontSee($b->order_number);
        $this->actingAs($admin)->get('/admin/orders?status=processing')->assertSee($b->order_number)->assertDontSee($a->order_number);
        $this->actingAs($admin)->get(route('admin.orders.show', $a))->assertOk()->assertSee($a->order_number);
    }

    public function test_admin_can_apply_valid_status_transition(): void
    {
        $order = Order::factory()->create();

        $this->actingAs($this->admin())->patch(route('admin.orders.update', $order), [
            'order_status' => 'processing', 'payment_status' => 'paid',
        ])->assertRedirect(route('admin.orders.show', $order));

        $order->refresh();
        $this->assertSame(OrderStatus::Processing, $order->status);
        $this->assertSame(PaymentStatus::Paid, $order->payment_status);
        $this->assertDatabaseHas('activity_logs', ['action' => 'order.status_updated', 'subject_id' => $order->id]);
    }

    public function test_invalid_transition_is_rejected(): void
    {
        $order = Order::factory()->create();

        $this->actingAs($this->admin())->patch(route('admin.orders.update', $order), [
            'order_status' => 'completed', 'payment_status' => 'pending',
        ])->assertSessionHasErrors('order_status');

        $this->assertSame(OrderStatus::Pending, $order->fresh()->status);
    }

    public function test_order_cannot_be_processed_without_payment(): void
    {
        $order = Order::factory()->create();

        $this->actingAs($this->admin())->patch(route('admin.orders.update', $order), [
            'order_status' => 'processing', 'payment_status' => 'pending',
        ])->assertSessionHasErrors('order_status');

        $this->assertSame(OrderStatus::Pending, $order->fresh()->status);
    }

    public function test_final_status_cannot_be_changed(): void
    {
        $order = Order::factory()->paid()->create(['status' => OrderStatus::Completed]);

        $this->actingAs($this->admin())->patch(route('admin.orders.update', $order), [
            'order_status' => 'processing', 'payment_status' => 'paid',
        ])->assertSessionHasErrors('order_status');
    }

    public function test_paid_order_cannot_be_cancelled_without_refund(): void
    {
        $order = Order::factory()->paid()->create();

        $this->actingAs($this->admin())->patch(route('admin.orders.update', $order), [
            'order_status' => 'cancelled', 'payment_status' => 'paid',
        ])->assertSessionHasErrors('order_status');

        $this->assertSame(OrderStatus::Processing, $order->fresh()->status);
    }

    public function test_admin_can_run_simulated_topup_and_it_is_not_processed_twice(): void
    {
        $order = Order::factory()->paid()->create(['player_id' => '555444']);
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('admin.orders.topup', $order))->assertSessionHas('success');

        $order->refresh();
        $this->assertSame(OrderStatus::Completed, $order->status);
        $this->assertNotNull($order->topup_reference);
        $reference = $order->topup_reference;

        // Pemrosesan ulang tidak boleh mengubah apa pun.
        app(OrderService::class)->processTopUp($order->id);
        $this->actingAs($admin)->post(route('admin.orders.topup', $order))->assertSessionHas('error');
        $this->assertSame($reference, $order->fresh()->topup_reference);
    }

    public function test_simulated_topup_failure_marks_order_failed(): void
    {
        $order = Order::factory()->paid()->create(['player_id' => '000000']);

        $this->actingAs($this->admin())->post(route('admin.orders.topup', $order));

        $order->refresh();
        $this->assertSame(OrderStatus::Failed, $order->status);
        $this->assertNotNull($order->failure_reason);
    }

    public function test_cancelling_order_releases_promo_usage(): void
    {
        $promo = Promotion::factory()->create(['used_count' => 1]);
        $order = Order::factory()->create(['promotion_id' => $promo->id, 'promo_code' => $promo->code]);

        app(OrderService::class)->cancelOrder($order, 'Uji coba');

        $this->assertSame(OrderStatus::Cancelled, $order->fresh()->status);
        $this->assertSame(0, $promo->fresh()->used_count);
    }

    public function test_expired_unpaid_orders_are_cancelled_by_command(): void
    {
        $old = Order::factory()->create(['created_at' => now()->subDays(3)]);
        $fresh = Order::factory()->create();
        $paidOld = Order::factory()->paid()->create(['created_at' => now()->subDays(3)]);

        $this->artisan('katniss:cancel-expired-orders')->assertSuccessful();

        $this->assertSame(OrderStatus::Cancelled, $old->fresh()->status);
        $this->assertSame(OrderStatus::Pending, $fresh->fresh()->status);
        $this->assertSame(OrderStatus::Processing, $paidOld->fresh()->status);
    }
}
