<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccessControlTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_open_admin_pages(): void
    {
        $this->get('/admin')->assertRedirect(route('login'));
        $this->get('/admin/orders')->assertRedirect(route('login'));
    }

    public function test_customer_cannot_open_admin_pages(): void
    {
        $customer = User::factory()->create();

        $this->actingAs($customer)->get('/admin')->assertForbidden();
        $this->actingAs($customer)->get('/admin/games')->assertForbidden();
        $this->actingAs($customer)->post('/admin/games', ['name' => 'X'])->assertForbidden();
    }

    public function test_admin_can_open_admin_dashboard(): void
    {
        $this->actingAs(User::factory()->admin()->create())->get('/admin')->assertOk();
    }

    public function test_guest_is_redirected_from_customer_pages(): void
    {
        $this->get('/pesanan')->assertRedirect(route('login'));
        $this->get('/dashboard')->assertRedirect(route('login'));
        $this->get('/checkout')->assertRedirect(route('login'));
    }

    public function test_customer_cannot_view_another_customers_order(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $owner->id]);

        $this->actingAs($other)->get(route('orders.show', $order))->assertForbidden();
        $this->actingAs($owner)->get(route('orders.show', $order))->assertOk();
    }

    public function test_order_list_only_shows_own_orders(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $mine = Order::factory()->create(['user_id' => $owner->id]);
        $theirs = Order::factory()->create(['user_id' => $other->id]);

        $this->actingAs($owner)->get(route('orders.index'))
            ->assertOk()
            ->assertSee($mine->order_number)
            ->assertDontSee($theirs->order_number);
    }

    public function test_public_registration_always_creates_customer(): void
    {
        $this->post('/daftar', [
            'name' => 'Penyusup', 'email' => 'penyusup@example.test',
            'password' => 'Password123', 'password_confirmation' => 'Password123',
            'role' => 'admin',
        ])->assertRedirect(route('dashboard'));

        $user = User::where('email', 'penyusup@example.test')->firstOrFail();
        $this->assertSame(Role::Customer, $user->role);
        $this->assertFalse($user->isAdmin());
    }

    public function test_login_works_and_wrong_password_fails(): void
    {
        $user = User::factory()->create(['email' => 'a@example.test']);

        $this->post('/masuk', ['email' => 'a@example.test', 'password' => 'salah'])->assertSessionHasErrors('email');
        $this->assertGuest();

        $this->post('/masuk', ['email' => 'a@example.test', 'password' => 'Password123'])->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_admin_login_redirects_to_admin_dashboard(): void
    {
        User::factory()->admin()->create(['email' => 'adm@example.test']);

        $this->post('/masuk', ['email' => 'adm@example.test', 'password' => 'Password123'])->assertRedirect(route('admin.dashboard'));
    }
}
