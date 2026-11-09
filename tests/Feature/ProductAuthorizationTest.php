<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, mixed>
     */
    private function productData(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Authorized Product',
            'qty' => 12,
            'price' => 25.50,
            'description' => 'Product used to test access rules.',
        ], $overrides);
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->forceFill(['role' => $role])->save();

        return $user;
    }

    public function test_admin_can_access_create_page_and_create_product(): void
    {
        $admin = $this->userWithRole(User::ROLE_ADMIN);

        $this->actingAs($admin)
            ->get(route('product.create'))
            ->assertOk()
            ->assertSee('Create product');

        $this->actingAs($admin)
            ->post(route('product.store'), $this->productData())
            ->assertRedirect(route('product.index'));

        $this->assertDatabaseHas('products', ['name' => 'Authorized Product']);
    }

    public function test_admin_can_access_edit_page_and_update_product(): void
    {
        $admin = $this->userWithRole(User::ROLE_ADMIN);
        $product = Product::factory()->create(['name' => 'Before Update']);

        $this->actingAs($admin)
            ->get(route('product.edit', $product))
            ->assertOk()
            ->assertSee('Edit Product');

        $this->actingAs($admin)
            ->put(route('product.update', $product), $this->productData(['name' => 'After Update']))
            ->assertRedirect(route('product.index'));

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'After Update',
        ]);
    }

    public function test_admin_can_delete_product(): void
    {
        $admin = $this->userWithRole(User::ROLE_ADMIN);
        $product = Product::factory()->create();

        $this->actingAs($admin)
            ->delete(route('product.destroy', $product))
            ->assertRedirect(route('product.index'));

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    public function test_regular_user_can_view_and_search_products_without_management_controls(): void
    {
        $user = $this->userWithRole(User::ROLE_USER);
        $user->name = 'Carl Hummington';
        $user->save();
        Product::factory()->create([
            'name' => 'Searchable Monitor',
            'description' => 'A monitor for office work.',
        ]);
        Product::factory()->create([
            'name' => 'Unmatched Keyboard',
            'description' => 'Mechanical keyboard.',
        ]);

        $this->actingAs($user)
            ->get(route('product.index'))
            ->assertOk()
            ->assertSee('Stockwise')
            ->assertSee('Searchable Monitor')
            ->assertSee('Carl Hummington')
            ->assertSee('USER')
            ->assertDontSee('Create product', false)
            ->assertDontSee('Add Product')
            ->assertDontSee(route('product.create'), false)
            ->assertDontSee('Edit')
            ->assertDontSee('Delete');

        $this->actingAs($user)
            ->get(route('product.index', ['search' => 'office work']))
            ->assertOk()
            ->assertSee('Searchable Monitor')
            ->assertDontSee('Unmatched Keyboard');
    }

    public function test_admin_sees_add_product_button_and_admin_role_badge(): void
    {
        $admin = $this->userWithRole(User::ROLE_ADMIN);
        $admin->name = 'Karle Taer';
        $admin->save();

        $this->actingAs($admin)
            ->get(route('product.index'))
            ->assertOk()
            ->assertSee('Stockwise')
            ->assertSee('Karle Taer')
            ->assertSee('ADMIN')
            ->assertSee('Add Product')
            ->assertDontSee('Create product', false);
    }

    public function test_regular_user_cannot_access_create_page_or_submit_creation(): void
    {
        $user = $this->userWithRole(User::ROLE_USER);

        $this->actingAs($user)->get(route('product.create'))->assertForbidden();
        $this->actingAs($user)->post(route('product.store'), $this->productData())->assertForbidden();

        $this->assertDatabaseCount('products', 0);
    }

    public function test_regular_user_cannot_access_edit_page_or_submit_update(): void
    {
        $user = $this->userWithRole(User::ROLE_USER);
        $product = Product::factory()->create(['name' => 'Protected Product']);

        $this->actingAs($user)->get(route('product.edit', $product))->assertForbidden();
        $this->actingAs($user)
            ->put(route('product.update', $product), $this->productData(['name' => 'Tampered Product']))
            ->assertForbidden();

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Protected Product',
        ]);
    }

    public function test_regular_user_cannot_delete_product(): void
    {
        $user = $this->userWithRole(User::ROLE_USER);
        $product = Product::factory()->create();

        $this->actingAs($user)->delete(route('product.destroy', $product))->assertForbidden();

        $this->assertDatabaseHas('products', ['id' => $product->id]);
    }

    public function test_guests_are_redirected_to_login_for_authenticated_product_routes(): void
    {
        $product = Product::factory()->create();

        $this->get(route('product.index'))->assertRedirect(route('login'));
        $this->get(route('product.show', $product))->assertRedirect(route('login'));
        $this->get(route('product.create'))->assertRedirect(route('login'));
        $this->post(route('product.store'), $this->productData())->assertRedirect(route('login'));
        $this->get(route('product.edit', $product))->assertRedirect(route('login'));
        $this->put(route('product.update', $product), $this->productData())->assertRedirect(route('login'));
        $this->delete(route('product.destroy', $product))->assertRedirect(route('login'));
    }
}
