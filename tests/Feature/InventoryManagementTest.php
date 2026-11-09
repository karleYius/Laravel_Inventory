<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Product;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryManagementTest extends TestCase
{
    use RefreshDatabase;

    private function userWithRole(string $role): User
    {
        return User::factory()->create(['role' => $role]);
    }

    public function test_admin_dashboard_includes_inventory_value_and_stock_alert_groups(): void
    {
        $admin = $this->userWithRole(User::ROLE_ADMIN);
        Product::factory()->create(['qty' => 3, 'price' => 12.50]);
        Product::factory()->outOfStock()->create();

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertOk()
            ->assertSee('Inventory Value')
            ->assertSee('Products Needing Attention')
            ->assertSee('Low-stock products')
            ->assertSee('Out-of-stock products');
        $this->assertArrayHasKey('value', $response->viewData('stats'));
    }

    public function test_regular_dashboard_does_not_calculate_or_return_aggregate_value_but_shows_product_prices(): void
    {
        $user = $this->userWithRole(User::ROLE_USER);
        Product::factory()->create(['name' => 'Affordable Item', 'qty' => 7, 'price' => 12.50]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk()
            ->assertDontSee('Inventory Value')
            ->assertSee('$12.50')
            ->assertSee('Low-stock products')
            ->assertSee('Out-of-stock products');
        $this->assertArrayNotHasKey('value', $response->viewData('stats'));
    }

    public function test_regular_product_listing_hides_aggregate_value_but_keeps_individual_price(): void
    {
        $user = $this->userWithRole(User::ROLE_USER);
        Product::factory()->create(['name' => 'Visible Price Item', 'price' => 14.25]);

        $response = $this->actingAs($user)->get(route('product.index'));

        $response->assertOk()
            ->assertDontSee('Inventory Value')
            ->assertSee('$14.25');
        $this->assertArrayNotHasKey('value', $response->viewData('stats'));
    }

    public function test_shared_ten_unit_threshold_drives_dashboard_and_product_status(): void
    {
        $admin = $this->userWithRole(User::ROLE_ADMIN);
        $zero = Product::factory()->outOfStock()->create(['name' => 'Empty']);
        $low = Product::factory()->create(['name' => 'At threshold', 'qty' => 10]);
        $alsoLow = Product::factory()->create(['name' => 'Below threshold', 'qty' => 1]);
        $inStock = Product::factory()->create(['name' => 'Above threshold', 'qty' => 11]);

        $this->assertTrue($low->isLowStock());
        $this->assertTrue($alsoLow->isLowStock());
        $this->assertFalse($zero->isLowStock());
        $this->assertTrue($zero->isOutOfStock());
        $this->assertFalse($inStock->isLowStock());

        $this->actingAs($admin)->get(route('product.show', $low))->assertOk()->assertSee('Low stock');
        $this->get(route('product.show', $zero))->assertOk()->assertSee('Out of stock');
        $this->get(route('product.show', $inStock))->assertOk()->assertSee('In stock');

        $response = $this->get(route('dashboard'))->assertOk();
        $this->assertSame(2, $response->viewData('stats')['low_stock']);
        $this->assertSame(1, $response->viewData('stats')['out_of_stock']);
        $this->assertSame(3, $response->viewData('stats')['attention']);
        $this->assertEqualsCanonicalizing(
            ['At threshold', 'Below threshold'],
            $response->viewData('lowStockProducts')->pluck('name')->all()
        );
        $this->assertSame(['Empty'], $response->viewData('outOfStockProducts')->pluck('name')->all());
    }

    public function test_product_creation_updates_and_deletion_are_logged_with_snapshots(): void
    {
        $admin = $this->userWithRole(User::ROLE_ADMIN);

        $this->actingAs($admin)->post(route('product.store'), [
            'name' => 'Logged product',
            'qty' => 8,
            'price' => 15.25,
            'description' => 'Initial description',
        ])->assertRedirect(route('product.index'));

        $product = Product::query()->where('name', 'Logged product')->firstOrFail();
        $created = ActivityLog::query()->where('action', 'product.created')->firstOrFail();
        $this->assertSame($admin->id, $created->user_id);
        $this->assertSame($product->id, $created->subject_id);
        $this->assertSame('Logged product', $created->subject_name);
        $this->assertSame(8, $created->properties['after']['qty']);

        $this->put(route('product.update', $product), [
            'name' => 'Updated logged product',
            'qty' => 4,
            'price' => 16.50,
            'description' => 'Updated description',
        ])->assertRedirect(route('product.index'));

        $updated = ActivityLog::query()->where('action', 'product.updated')->firstOrFail();
        $this->assertSame(8, $updated->properties['before']['qty']);
        $this->assertSame(4, $updated->properties['after']['qty']);

        $this->delete(route('product.destroy', $product))->assertRedirect(route('product.index'));

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'product.deleted',
            'user_id' => $admin->id,
            'subject_name' => 'Updated logged product',
        ]);
    }

    public function test_historical_activity_for_removed_features_remains_renderable(): void
    {
        $admin = $this->userWithRole(User::ROLE_ADMIN);

        ActivityLog::query()->create([
            'user_id' => $admin->id,
            'user_name' => $admin->name,
            'action' => 'category.created',
            'subject_type' => 'App\\Models\\Category',
            'subject_id' => 5,
            'subject_name' => 'Historical category',
            'properties' => ['after' => ['name' => 'Historical category']],
        ]);
        ActivityLog::query()->create([
            'user_id' => $admin->id,
            'user_name' => $admin->name,
            'action' => 'stock.stock_in',
            'subject_type' => 'App\\Models\\Product',
            'subject_id' => 8,
            'subject_name' => 'Historical product',
            'properties' => ['quantity' => 3],
        ]);

        $this->actingAs($admin)->get(route('activity-logs.index'))
            ->assertOk()
            ->assertSee('Historical category')
            ->assertSee('Historical product')
            ->assertSee('category · created')
            ->assertSee('stock · stock_in');
    }

    public function test_activity_logger_redacts_credentials_and_keeps_actor_snapshot(): void
    {
        $actor = $this->userWithRole(User::ROLE_ADMIN);
        $subject = Product::factory()->create(['name' => 'Logged product']);

        app(ActivityLogger::class)->log($actor, 'test.redaction', $subject, [
            'before' => ['name' => $subject->name, 'password' => 'secret', 'remember_token' => 'token'],
        ]);
        $log = ActivityLog::query()->where('action', 'test.redaction')->firstOrFail();
        $this->assertSame(['name' => $subject->name], $log->properties['before']);

        $actorName = $actor->name;
        $actor->delete();
        $log->refresh();
        $this->assertNull($log->user_id);
        $this->assertSame($actorName, $log->user_name);
    }

    public function test_regular_users_cannot_access_activity_logs_or_user_management(): void
    {
        $user = $this->userWithRole(User::ROLE_USER);
        $this->actingAs($user)->get(route('activity-logs.index'))->assertForbidden();
        $this->get(route('users.index'))->assertForbidden();
    }

    public function test_user_management_never_exposes_credentials_and_records_role_changes(): void
    {
        $admin = $this->userWithRole(User::ROLE_ADMIN);
        $target = $this->userWithRole(User::ROLE_USER);

        $this->actingAs($admin)->get(route('users.index'))
            ->assertOk()
            ->assertSee($target->email)
            ->assertDontSee($target->password)
            ->assertDontSee('remember_token');
        $this->get(route('users.show', $target))
            ->assertOk()
            ->assertDontSee('remember_token')
            ->assertDontSee($target->password);

        $passwordHash = $target->password;
        $rememberToken = $target->remember_token;
        $this->put(route('users.update-role', $target), [
            'role' => 'root',
            'password' => 'attacker-chosen-password',
            'remember_token' => 'attacker-chosen-token',
        ])->assertSessionHasErrors('role');
        $this->put(route('users.update-role', $target), [
            'role' => User::ROLE_ADMIN,
            'password' => 'attacker-chosen-password',
            'remember_token' => 'attacker-chosen-token',
        ])->assertRedirect(route('users.show', $target));

        $this->assertSame(User::ROLE_ADMIN, $target->fresh()->role);
        $this->assertSame($passwordHash, $target->fresh()->password);
        $this->assertSame($rememberToken, $target->fresh()->remember_token);
        $this->assertDatabaseHas('activity_logs', [
            'action' => 'user.role_changed',
            'subject_id' => $target->id,
        ]);
    }

    public function test_last_administrator_cannot_be_demoted_or_deleted_and_profile_deletion_is_guarded(): void
    {
        $admin = $this->userWithRole(User::ROLE_ADMIN);

        $this->actingAs($admin)->put(route('users.update-role', $admin), ['role' => User::ROLE_USER])
            ->assertSessionHasErrors('role');
        $this->delete(route('users.destroy', $admin))->assertSessionHasErrors('user');
        $this->delete(route('profile.destroy'), ['password' => 'password'])
            ->assertSessionHasErrors('role');

        $this->assertDatabaseHas('users', ['id' => $admin->id, 'role' => User::ROLE_ADMIN]);
    }

    public function test_admin_can_delete_another_user_and_records_the_action(): void
    {
        $admin = $this->userWithRole(User::ROLE_ADMIN);
        $target = $this->userWithRole(User::ROLE_USER);

        $this->actingAs($admin)->delete(route('users.destroy', $target))
            ->assertRedirect(route('users.index'));

        $this->assertDatabaseMissing('users', ['id' => $target->id]);
        $this->assertDatabaseHas('activity_logs', [
            'action' => 'user.deleted',
            'subject_name' => $target->name,
        ]);
    }
}
