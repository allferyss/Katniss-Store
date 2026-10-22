<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Http\Controllers\Admin\ReportController;
use App\Models\Game;
use App\Models\Order;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminCrudTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    public function test_admin_can_create_update_and_delete_game(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post('/admin/games', [
            'name' => 'Game Baru', 'description' => 'Deskripsi', 'is_active' => '1',
        ])->assertRedirect(route('admin.games.index'));

        $game = Game::where('slug', 'game-baru')->firstOrFail();
        $this->assertTrue($game->is_active);
        $this->assertFalse($game->requires_server_id);

        $this->actingAs($admin)->put(route('admin.games.update', $game), [
            'name' => 'Game Baru v2', 'slug' => 'game-baru', 'requires_server_id' => '1',
        ])->assertRedirect(route('admin.games.index'));

        $game->refresh();
        $this->assertSame('Game Baru v2', $game->name);
        $this->assertTrue($game->requires_server_id);
        $this->assertFalse($game->is_active);

        $this->actingAs($admin)->delete(route('admin.games.destroy', $game))->assertRedirect(route('admin.games.index'));
        $this->assertDatabaseMissing('games', ['id' => $game->id]);
    }

    public function test_game_validation_rejects_duplicate_slug_and_bad_upload(): void
    {
        $admin = $this->admin();
        Game::factory()->create(['slug' => 'sudah-ada']);

        $this->actingAs($admin)->post('/admin/games', ['name' => 'Lain', 'slug' => 'sudah-ada'])->assertSessionHasErrors('slug');
        $this->actingAs($admin)->post('/admin/games', ['name' => 'Lain', 'image' => UploadedFile::fake()->create('shell.php', 10, 'application/x-php')])->assertSessionHasErrors('image');
        $this->actingAs($admin)->post('/admin/games', ['name' => ''])->assertSessionHasErrors('name');
    }

    public function test_admin_can_upload_game_image_with_random_name(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin())->post('/admin/games', [
            'name' => 'Dengan Gambar', 'image' => UploadedFile::fake()->image('cover.png', 400, 300),
        ])->assertSessionDoesntHaveErrors();

        $game = Game::where('slug', 'dengan-gambar')->firstOrFail();
        $this->assertNotNull($game->image_path);
        $this->assertStringNotContainsString('cover', $game->image_path);
        Storage::disk('public')->assertExists($game->image_path);
    }

    public function test_admin_can_manage_products(): void
    {
        $admin = $this->admin();
        $game = Game::factory()->create();

        $this->actingAs($admin)->post('/admin/products', [
            'game_id' => $game->id, 'name' => '500 Gem', 'price' => 45000, 'is_active' => '1',
        ])->assertRedirect();

        $product = Product::where('name', '500 Gem')->firstOrFail();
        $this->assertSame(45000, $product->price);

        $this->actingAs($admin)->put(route('admin.products.update', $product), [
            'game_id' => $game->id, 'name' => '500 Gem', 'price' => 50000,
        ])->assertRedirect();
        $this->assertSame(50000, $product->fresh()->price);
        $this->assertFalse($product->fresh()->is_active);

        $this->actingAs($admin)->delete(route('admin.products.destroy', $product))->assertRedirect();
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    public function test_product_validation(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post('/admin/products', ['game_id' => 999, 'name' => 'X', 'price' => 'abc'])
            ->assertSessionHasErrors(['game_id', 'price']);
        $this->actingAs($admin)->post('/admin/products', ['game_id' => Game::factory()->create()->id, 'name' => 'X', 'price' => 10])
            ->assertSessionHasErrors('price');
    }

    public function test_deleting_product_keeps_order_history(): void
    {
        $product = Product::factory()->create(['name' => '100 Gem']);
        $user = User::factory()->create();
        $this->actingAs($user)->post('/checkout', [
            'product_id' => $product->id, 'player_id' => '123456', 'payment_method' => 'qris', 'idempotency_key' => (string) \Illuminate\Support\Str::uuid(),
        ]);

        $this->actingAs($this->admin())->delete(route('admin.products.destroy', $product));

        $order = Order::with('items')->firstOrFail();
        $this->assertSame('100 Gem', $order->items[0]->product_name);
        $this->assertNull($order->items[0]->product_id);
    }

    public function test_admin_can_manage_promotions(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post('/admin/promotions', [
            'code' => 'baru20', 'name' => 'Promo Baru', 'type' => 'percent', 'value' => 20, 'is_active' => '1',
        ])->assertRedirect(route('admin.promotions.index'));

        $promo = Promotion::where('code', 'BARU20')->firstOrFail();

        $this->actingAs($admin)->put(route('admin.promotions.update', $promo), [
            'code' => 'BARU20', 'name' => 'Promo Update', 'type' => 'fixed', 'value' => 5000,
        ])->assertRedirect(route('admin.promotions.index'));
        $this->assertSame('fixed', $promo->fresh()->type);

        $this->actingAs($admin)->post('/admin/promotions', ['code' => 'BIG', 'name' => 'X', 'type' => 'percent', 'value' => 95])
            ->assertSessionHasErrors('value');

        $this->actingAs($admin)->delete(route('admin.promotions.destroy', $promo))->assertRedirect();
        $this->assertDatabaseMissing('promotions', ['id' => $promo->id]);
    }

    public function test_admin_can_change_other_users_role_but_not_own(): void
    {
        $admin = $this->admin();
        $customer = User::factory()->create();

        $this->actingAs($admin)->patch(route('admin.users.update', $customer), ['role' => 'admin'])->assertSessionHasNoErrors();
        $this->assertSame(Role::Admin, $customer->fresh()->role);

        $this->actingAs($admin)->patch(route('admin.users.update', $admin), ['role' => 'customer'])->assertSessionHasErrors('role');
        $this->assertSame(Role::Admin, $admin->fresh()->role);
    }

    public function test_admin_can_demote_another_admin(): void
    {
        $admin = $this->admin();
        $other = User::factory()->admin()->create();

        // Ada dua admin, jadi menurunkan admin lain diperbolehkan.
        $this->actingAs($admin)->patch(route('admin.users.update', $other), ['role' => 'customer'])->assertSessionHasNoErrors();
        $this->assertSame(Role::Customer, $other->fresh()->role);
        $this->assertSame(1, User::where('role', 'admin')->count());
    }

    public function test_customer_cannot_change_roles(): void
    {
        $customer = User::factory()->create();
        $target = User::factory()->create();

        $this->actingAs($customer)->patch(route('admin.users.update', $target), ['role' => 'admin'])->assertForbidden();
        $this->assertSame(Role::Customer, $target->fresh()->role);
    }

    public function test_admin_can_update_settings(): void
    {
        $this->actingAs($this->admin())->put('/admin/settings', [
            'store_name' => 'Toko Uji', 'tagline' => 'Tagline uji', 'contact_email' => 'cs@example.test',
        ])->assertSessionHasNoErrors();

        $this->get('/kontak')->assertOk()->assertSee('cs@example.test');
        $this->get('/')->assertSee('Toko Uji');
    }

    public function test_report_page_and_csv_export(): void
    {
        $admin = $this->admin();
        $order = Order::factory()->paid()->create();

        $this->actingAs($admin)->get('/admin/reports')->assertOk()->assertSee($order->order_number);

        $response = $this->actingAs($admin)->get('/admin/reports/export');
        $response->assertOk();
        $this->assertStringContainsString($order->order_number, $response->streamedContent());
    }

    public function test_csv_cells_are_protected_against_formula_injection(): void
    {
        $this->assertSame("'=HYPERLINK(\"x\")", ReportController::safeCell('=HYPERLINK("x")'));
        $this->assertSame("'+1", ReportController::safeCell('+1'));
        $this->assertSame('Biasa', ReportController::safeCell('Biasa'));
        $this->assertSame(1000, ReportController::safeCell(1000));
    }

    public function test_activity_log_and_dashboard_render(): void
    {
        $admin = $this->admin();
        Order::factory()->paid()->create(['total' => 50000, 'subtotal' => 50000]);

        $this->actingAs($admin)->get('/admin')->assertOk()->assertSee('Rp 50.000');
        $this->actingAs($admin)->get('/admin/activity-logs')->assertOk();
        $this->actingAs($admin)->get('/admin/users')->assertOk();
        $this->actingAs($admin)->get('/admin/settings')->assertOk();
    }
}
