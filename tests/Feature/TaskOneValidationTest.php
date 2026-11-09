<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskOneValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_store_allows_empty_description(): void
    {
        $admin = $this->admin();

        $response = $this->actingAs($admin)->post(route('product.store'), [
            'name' => 'Desk Organizer',
            'qty' => 8,
            'price' => 19.99,
            'description' => '',
        ]);

        $response->assertRedirect(route('product.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('products', [
            'name' => 'Desk Organizer',
            'qty' => 8,
            'price' => 19.99,
            'description' => null,
        ]);
    }

    public function test_product_store_requires_name_qty_and_price(): void
    {
        $admin = $this->admin();

        $response = $this->actingAs($admin)->from(route('product.create'))->post(route('product.store'), [
            'name' => '',
            'qty' => '',
            'price' => '',
            'description' => '',
        ]);

        $response->assertSessionHasErrors(['name', 'qty', 'price'])
            ->assertSessionDoesntHaveErrors(['description'])
            ->assertSessionHasInput('description', '');

        $this->assertDatabaseCount('products', 0);
    }

    public function test_product_update_requires_name_quantity_and_price_but_allows_empty_description(): void
    {
        $admin = $this->admin();
        $product = Product::factory()->create([
            'name' => 'Existing Product',
            'qty' => 10,
            'price' => 25.00,
            'description' => 'Old description',
        ]);

        $response = $this->actingAs($admin)
            ->from(route('product.edit', $product))
            ->put(route('product.update', $product), [
                'name' => '',
                'qty' => '',
                'price' => '',
                'description' => '',
            ]);

        $response->assertSessionHasErrors(['name', 'qty', 'price'])
            ->assertSessionDoesntHaveErrors(['description'])
            ->assertSessionHasInput('name', '');

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Existing Product',
            'qty' => 10,
            'description' => 'Old description',
        ]);
    }

    private function admin(): User
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => User::ROLE_ADMIN])->save();

        return $admin;
    }
}
