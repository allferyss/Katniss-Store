<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_simulate_successful_payment(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)->post(route('orders.simulate', $order), ['result' => 'success'])
            ->assertRedirect(route('orders.show', $order));

        $order->refresh();
        $this->assertSame(PaymentStatus::Paid, $order->payment_status);
        $this->assertSame(OrderStatus::Processing, $order->status);
        $this->assertNotNull($order->paid_at);
    }

    public function test_simulated_payment_is_idempotent(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)->post(route('orders.simulate', $order), ['result' => 'success']);
        $firstPaidAt = $order->fresh()->paid_at;
        $this->actingAs($user)->post(route('orders.simulate', $order), ['result' => 'success']);

        $order->refresh();
        $this->assertSame(PaymentStatus::Paid, $order->payment_status);
        $this->assertEquals($firstPaidAt, $order->paid_at);
        $this->assertSame(1, Payment::where('order_id', $order->id)->where('status', 'paid')->count());
    }

    public function test_failed_simulation_cancels_order(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)->post(route('orders.simulate', $order), ['result' => 'failed']);

        $order->refresh();
        $this->assertSame(OrderStatus::Cancelled, $order->status);
        $this->assertSame(PaymentStatus::Failed, $order->payment_status);
    }

    public function test_other_customer_cannot_simulate_payment(): void
    {
        $order = Order::factory()->create();
        $intruder = User::factory()->create();

        $this->actingAs($intruder)->post(route('orders.simulate', $order), ['result' => 'success'])->assertForbidden();
        $this->assertSame(PaymentStatus::Pending, $order->fresh()->payment_status);
    }

    public function test_simulation_route_is_disabled_when_driver_is_not_fake(): void
    {
        config(['katniss.payment.driver' => 'live']);
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)->post(route('orders.simulate', $order), ['result' => 'success'])->assertNotFound();
        $this->assertSame(PaymentStatus::Pending, $order->fresh()->payment_status);
    }

    public function test_customer_cannot_update_status_via_admin_route(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)->patch(route('admin.orders.update', $order), [
            'order_status' => 'completed', 'payment_status' => 'paid',
        ])->assertForbidden();

        $order->refresh();
        $this->assertSame(PaymentStatus::Pending, $order->payment_status);
        $this->assertSame(OrderStatus::Pending, $order->status);
    }

    /* ---------------- Webhook ---------------- */

    private function webhook(array $data, ?string $secret = 'test-webhook-secret')
    {
        config(['katniss.payment.webhook_secret' => 'test-webhook-secret']);
        $body = json_encode($data);
        $server = ['CONTENT_TYPE' => 'application/json'];
        if ($secret !== null) {
            $server['HTTP_X_SIGNATURE'] = hash_hmac('sha256', $body, $secret);
        }

        return $this->call('POST', '/webhooks/payment', [], [], [], $server, $body);
    }

    public function test_webhook_with_invalid_signature_is_rejected(): void
    {
        $order = Order::factory()->create();
        $ref = $order->payments->first()->reference;

        $this->webhook(['event_id' => 'e1', 'reference' => $ref, 'status' => 'paid'], 'secret-salah')->assertStatus(401);
        $this->webhook(['event_id' => 'e1', 'reference' => $ref, 'status' => 'paid'], null)->assertStatus(401);

        $this->assertSame(PaymentStatus::Pending, $order->fresh()->payment_status);
    }

    public function test_webhook_with_valid_signature_marks_payment_paid(): void
    {
        $order = Order::factory()->create();
        $ref = $order->payments->first()->reference;

        $this->webhook(['event_id' => 'evt-1', 'reference' => $ref, 'status' => 'paid', 'amount' => 10000])->assertOk();

        $order->refresh();
        $this->assertSame(PaymentStatus::Paid, $order->payment_status);
        $this->assertSame(OrderStatus::Processing, $order->status);
    }

    public function test_duplicate_webhook_event_is_ignored(): void
    {
        $order = Order::factory()->create();
        $ref = $order->payments->first()->reference;
        $data = ['event_id' => 'evt-dup', 'reference' => $ref, 'status' => 'paid'];

        $this->webhook($data)->assertOk();
        $this->webhook($data)->assertOk()->assertJson(['duplicate' => true]);

        $this->assertSame(1, \App\Models\WebhookEvent::where('event_id', 'evt-dup')->count());
    }

    public function test_webhook_rejects_amount_mismatch_and_unknown_reference(): void
    {
        $order = Order::factory()->create();
        $ref = $order->payments->first()->reference;

        $this->webhook(['event_id' => 'e2', 'reference' => $ref, 'status' => 'paid', 'amount' => 1])->assertStatus(422);
        $this->webhook(['event_id' => 'e3', 'reference' => 'TIDAK-ADA', 'status' => 'paid'])->assertStatus(404);
        $this->assertSame(PaymentStatus::Pending, $order->fresh()->payment_status);
    }

    public function test_webhook_is_rejected_when_no_secret_is_configured(): void
    {
        $order = Order::factory()->create();
        config(['katniss.payment.webhook_secret' => null]);
        $body = json_encode(['event_id' => 'e9', 'reference' => $order->payments->first()->reference, 'status' => 'paid']);

        $this->call('POST', '/webhooks/payment', [], [], [], ['CONTENT_TYPE' => 'application/json', 'HTTP_X_SIGNATURE' => hash_hmac('sha256', $body, 'apa-saja')], $body)
            ->assertStatus(401);
    }
}
