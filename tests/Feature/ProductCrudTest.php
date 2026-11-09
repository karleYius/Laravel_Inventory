<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ProductCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $admin = User::factory()->create();
        $admin->forceFill(['role' => User::ROLE_ADMIN])->save();
        $this->actingAs($admin);
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Wireless Keyboard',
            'qty' => 25,
            'price' => 49.99,
            'description' => 'Compact wireless keyboard with backlit keys.',
        ], $overrides);
    }

    private function createProductsForPagination(int $count, string $namePrefix): void
    {
        for ($number = 1; $number <= $count; $number++) {
            $timestamp = now()->addSeconds($number);

            Product::factory()->create([
                'name' => sprintf('%s %02d', $namePrefix, $number),
                'description' => 'Pagination fixture',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]);
        }
    }

    public function test_home_page_displays_dashboard(): void
    {
        Product::factory()->create(['qty' => 3]);

        $response = $this->get(route('dashboard'));

        $response->assertOk()
            ->assertSee('Dashboard')
            ->assertSee('Total Products')
            ->assertSee('Products Needing Attention');
    }

    public function test_products_page_displays_listing_and_statistics(): void
    {
        Product::factory()->create([
            'name' => 'Standing Desk',
            'qty' => 10,
            'price' => 399.00,
            'description' => 'Electric standing desk.',
        ]);

        $response = $this->get(route('product.index'));

        $response->assertOk()
            ->assertSee('Manage your product catalog, stock levels and pricing.')
            ->assertSee('Standing Desk')
            ->assertSee('Electric standing desk.')
            ->assertSee('Total Products')
            ->assertSee('Units in Stock')
            ->assertSee('Inventory Value')
            ->assertSee('Needs Attention')
            ->assertSee('Search products...')
            ->assertSee('Edit')
            ->assertSee('Delete')
            ->assertDontSee('page=');
    }

    public function test_products_page_shows_empty_state_when_no_products(): void
    {
        $this->get(route('product.index'))
            ->assertOk()
            ->assertSee('No products yet');
    }

    public function test_search_filters_products(): void
    {
        Product::factory()->create([
            'name' => 'Gaming Mouse',
            'description' => 'RGB gaming mouse.',
        ]);
        Product::factory()->create([
            'name' => 'Office Chair',
            'description' => 'Mesh back chair.',
        ]);

        $response = $this->get(route('product.index', ['search' => 'Gaming']));

        $response->assertOk()
            ->assertSee('Gaming Mouse')
            ->assertDontSee('Office Chair');
    }

    public function test_search_matches_description_and_shows_no_match_state(): void
    {
        Product::factory()->create([
            'name' => 'Desk Lamp',
            'description' => 'Warm LED lamp.',
        ]);

        $this->get(route('product.index', ['search' => 'Warm LED']))
            ->assertOk()
            ->assertSee('Desk Lamp');

        $this->get(route('product.index', ['search' => 'zzz-nothing']))
            ->assertOk()
            ->assertSee('No products match')
            ->assertDontSee('page=');
    }

    public function test_products_are_limited_to_ten_per_page(): void
    {
        $this->createProductsForPagination(12, 'Pagination Product');

        $response = $this->get(route('product.index'))->assertOk();
        $products = $response->viewData('products');

        $this->assertCount(10, $products);
        $this->assertSame(10, $products->perPage());
    }

    public function test_pagination_links_are_rendered_when_products_span_multiple_pages(): void
    {
        $this->createProductsForPagination(11, 'Pagination Link Product');

        $this->get(route('product.index'))
            ->assertOk()
            ->assertSee('page=2', false);
    }

    public function test_second_page_displays_the_remaining_products(): void
    {
        $this->createProductsForPagination(12, 'Pagination Page Product');

        $response = $this->get(route('product.index', ['page' => 2]))->assertOk();
        $products = $response->viewData('products');

        $this->assertCount(2, $products);
        $response->assertSee('Pagination Page Product 01')
            ->assertSee('Pagination Page Product 02')
            ->assertDontSee('Pagination Page Product 03');
    }

    public function test_search_keyword_is_preserved_when_navigating_pages(): void
    {
        $this->createProductsForPagination(11, 'Searchable Pagination Product');
        Product::factory()->create([
            'name' => 'Unmatched Pagination Product',
            'description' => 'Pagination fixture',
        ]);

        $this->get(route('product.index', ['search' => 'Searchable']))
            ->assertOk()
            ->assertSee('search=Searchable', false)
            ->assertSee('page=2', false);

        $response = $this->get(route('product.index', [
            'search' => 'Searchable',
            'page' => 2,
        ]))->assertOk();
        $products = $response->viewData('products');

        $this->assertCount(1, $products);
        $response->assertSee('Searchable Pagination Product 01')
            ->assertDontSee('Unmatched Pagination Product');
    }

    public function test_create_product_form_is_displayed(): void
    {
        $this->get(route('product.create'))
            ->assertOk()
            ->assertSee('New Product')
            ->assertSee('Product name')
            ->assertSee('Quantity in stock')
            ->assertDontSee('Category')
            ->assertDontSee('Supplier')
            ->assertSee('Unit price')
            ->assertSee('Create product');
    }

    public function test_store_creates_product_and_redirects_with_success_message(): void
    {
        $response = $this->post(route('product.store'), $this->payload());

        $response->assertRedirect(route('product.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseCount('products', 1);

        $product = Product::firstOrFail();
        $this->assertSame('Wireless Keyboard', $product->name);
        $this->assertSame(25, $product->qty);
        $this->assertEqualsWithDelta(49.99, (float) $product->price, 0.01);
        $this->assertDatabaseHas('activity_logs', [
            'action' => 'product.created',
            'subject_id' => $product->id,
        ]);
    }

    public function test_store_requires_all_fields_except_description(): void
    {
        $response = $this->from(route('product.create'))
            ->post(route('product.store'), [
                'name' => '',
                'qty' => '',
                'price' => '',
                'description' => '',
            ]);

        $response->assertRedirect(route('product.create'))
            ->assertSessionHasErrors(['name', 'qty', 'price'])
            ->assertSessionDoesntHaveErrors(['description']);

        $this->get(route('product.create'))
            ->assertOk()
            ->assertSee('The name field is required.')
            ->assertSee('The qty field is required.')
            ->assertSee('The price field is required.');

        $this->assertDatabaseCount('products', 0);
    }

    public function test_store_accepts_empty_description(): void
    {
        $response = $this->post(route('product.store'), [
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

    public function test_store_rejects_invalid_values_and_repopulates_input(): void
    {
        $response = $this->post(route('product.store'), [
            'name' => 'Valid Name',
            'qty' => -5,
            'price' => -1,
            'description' => 'Valid description.',
        ]);

        $response->assertSessionHasErrors(['qty', 'price']);
        $response->assertSessionHasInput('name', 'Valid Name');
        $this->assertDatabaseCount('products', 0);
    }

    public function test_edit_form_displays_existing_product(): void
    {
        $product = Product::factory()->create([
            'name' => 'Monitor Light Bar',
            'qty' => 12,
            'price' => 49.90,
            'description' => 'Asymmetric monitor light bar.',
        ]);

        $this->get(route('product.edit', $product))
            ->assertOk()
            ->assertSee('Edit Product')
            ->assertSee('Monitor Light Bar')
            ->assertSee('Asymmetric monitor light bar.')
            ->assertSee('Save changes');
    }

    public function test_update_saves_changes_and_redirects_with_success_message(): void
    {
        $product = Product::factory()->create(['qty' => 15]);

        $response = $this->put(route('product.update', $product), $this->payload([
            'name' => 'Updated Product Name',
            'qty' => 7,
            'price' => 12.50,
        ]));

        $response->assertRedirect(route('product.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Updated Product Name',
            'qty' => 7,
        ]);
    }

    public function test_update_changes_stock_quantity_through_the_product_form(): void
    {
        $product = Product::factory()->create(['qty' => 15]);

        $this->put(route('product.update', $product), [
            'name' => 'Edited with stock change',
            'qty' => 9,
            'price' => 19.95,
            'description' => '',
        ])->assertRedirect(route('product.index'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Edited with stock change',
            'qty' => 9,
        ]);
    }

    public function test_destroy_deletes_the_product_and_flashes_success(): void
    {
        $product = Product::factory()->create();

        $response = $this->delete(route('product.destroy', $product));

        $response->assertRedirect(route('product.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    public function test_destroy_only_deletes_the_target_product(): void
    {
        $first = Product::factory()->create(['name' => 'Keep Me']);
        $second = Product::factory()->create(['name' => 'Delete Me']);

        $this->delete(route('product.destroy', $second))
            ->assertRedirect(route('product.index'));

        $this->assertDatabaseMissing('products', ['id' => $second->id]);
        $this->assertDatabaseHas('products', ['id' => $first->id, 'name' => 'Keep Me']);
    }

    public function test_get_product_route_displays_details_without_deleting_the_product(): void
    {
        $product = Product::factory()->create();

        $this->get(route('product.show', $product))
            ->assertOk()
            ->assertSee($product->name);

        $this->assertDatabaseHas('products', ['id' => $product->id]);
    }

    public function test_post_request_cannot_delete_a_product(): void
    {
        $product = Product::factory()->create();

        $this->post('/product/'.$product->id)->assertStatus(405);

        $this->assertDatabaseHas('products', ['id' => $product->id]);
    }

    public function test_product_routes_exist_with_expected_methods(): void
    {
        $routes = [
            'product.index' => 'GET',
            'product.create' => 'GET',
            'product.store' => 'POST',
            'product.edit' => 'GET',
            'product.update' => 'PUT',
            'product.destroy' => 'DELETE',
        ];

        foreach ($routes as $name => $method) {
            $route = Route::getRoutes()->getByName($name);

            $this->assertNotNull($route, "Route [{$name}] is missing.");
            $this->assertContains($method, $route->methods(), "Route [{$name}] should support {$method}.");
        }
    }

    public function test_product_routes_are_protected_by_the_web_middleware_group(): void
    {
        foreach (['product.index', 'product.store', 'product.update', 'product.destroy'] as $name) {
            $route = Route::getRoutes()->getByName($name);

            $this->assertNotNull($route, "Route [{$name}] is missing.");
            $this->assertContains('web', $route->gatherMiddleware(), "Route [{$name}] is outside the web middleware group (no CSRF/session).");
        }
    }

    public function test_navigation_links_are_present_on_the_products_page(): void
    {
        Product::factory()->create();

        $response = $this->get(route('product.index'))
            ->assertOk()
            ->assertSee(route('home'), false)
            ->assertSee(route('product.create'), false)
            ->assertDontSee('Categories')
            ->assertDontSee('Suppliers')
            ->assertDontSee('Stock Movements');

        foreach ([
            'categories.index',
            'suppliers.index',
            'stock-movements.index',
            'stock-movements.create',
        ] as $removedRoute) {
            $this->assertFalse(Route::has($removedRoute), "Removed route [{$removedRoute}] still exists.");
        }
    }
}
