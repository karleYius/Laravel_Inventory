<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Tests\TestCase;

class ProductImageUploadTest extends TestCase
{
    use RefreshDatabase;

    private function imageFile(string $name, string $format): UploadedFile
    {
        return UploadedFile::fake()->createWithContent($name, base64_decode($this->imageFixture($format)));
    }

    private function imageFixture(string $format): string
    {
        return match ($format) {
            'jpeg' => '/9j/4AAQSkZJRgABAQAAAQABAAD/2wCEAAkGBxAQEBAQEA8QDw8PDw8PDw8PDw8PDw8PFREWFhURFRUYHSggGBolGxUVITEhJSkrLi4uFx8zODMsNygtLisBCgoKDg0OGxAQGy0lICUtLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLf/AABEIAAEAAwMBIgACEQEDEQH/xAAXAAEBAQEAAAAAAAAAAAAAAAAAAQID/8QAFhEBAQEAAAAAAAAAAAAAAAAAAAER/9oADAMBAAIQAxAAAAH/xAAWEAEBAQAAAAAAAAAAAAAAAAAAARH/2gAIAQEAAQUCj//EABYRAQEBAAAAAAAAAAAAAAAAAAABEf/aAAgBAwEBPwFn/8QAFhEBAQEAAAAAAAAAAAAAAAAAAAER/9oACAECAQE/AYf/xAAXEAADAQAAAAAAAAAAAAAAAAAAAREh/9oACAEBAAY/Al//xAAWEAEBAQAAAAAAAAAAAAAAAAAAARH/2gAIAQEAAT8hV//aAAwDAQACAAMAAAAQ/wD/xAAVEQEBAAAAAAAAAAAAAAAAAAAAAf/aAAgBAwEBPxB//8QAFhEBAQEAAAAAAAAAAAAAAAAAAAER/9oACAECAQE/EF//xAAWEAEBAQAAAAAAAAAAAAAAAAAAARH/2gAIAQEAAT8QP//Z',
            'png' => 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAIAAACQd1PeAAAADUlEQVR4nGP4z8AAAAMBAQDJ/pLvAAAAAElFTkSuQmCC',
            'webp' => 'UklGRiIAAABXRUJQVlA4IBYAAAAwAQCdASoBAAEAAUAmJaQAA3AA/v89WAAAAA==',
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function productData(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Product with image',
            'qty' => 7,
            'price' => 18.50,
            'description' => '',
        ], $overrides);
    }

    private function admin(): User
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => User::ROLE_ADMIN])->save();

        return $admin;
    }

    public function test_admin_can_create_a_product_with_a_valid_jpeg_image(): void
    {
        Storage::fake('public');

        $response = $this->actingAs($this->admin())
            ->post(route('product.store'), $this->productData([
                'image' => $this->imageFile('product.jpg', 'jpeg'),
            ]));

        $response->assertRedirect(route('product.index'));

        $product = Product::query()->where('name', 'Product with image')->firstOrFail();
        $this->assertNotNull($product->image_path);
        $this->assertStringStartsWith('products/', $product->image_path);
        Storage::disk('public')->assertExists($product->image_path);
    }

    public function test_admin_can_create_and_display_a_product_with_a_valid_jpeg_image(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin())
            ->post(route('product.store'), $this->productData([
                'name' => 'Displayed Product Image',
                'image' => $this->imageFile('displayed-product.jpg', 'jpeg'),
            ]))
            ->assertRedirect(route('product.index'));

        $product = Product::query()->where('name', 'Displayed Product Image')->firstOrFail();
        $this->assertNotNull($product->image_path);
        Storage::disk('public')->assertExists($product->image_path);

        $imageUrl = asset('storage/'.$product->image_path);

        $this->get(route('product.index'))
            ->assertOk()
            ->assertSee('Displayed Product Image')
            ->assertSee($imageUrl, false);

        $this->get(route('product.show', $product))
            ->assertOk()
            ->assertSee('Displayed Product Image')
            ->assertSee($imageUrl, false);
    }

    public function test_admin_can_create_a_product_with_a_valid_png_image(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin())
            ->post(route('product.store'), $this->productData([
                'image' => $this->imageFile('product.png', 'png'),
            ]))
            ->assertRedirect(route('product.index'));

        $product = Product::query()->where('name', 'Product with image')->firstOrFail();
        Storage::disk('public')->assertExists($product->image_path);
    }

    public function test_admin_can_create_a_product_with_a_valid_webp_image(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin())
            ->post(route('product.store'), $this->productData([
                'image' => $this->imageFile('product.webp', 'webp'),
            ]))
            ->assertRedirect(route('product.index'));

        $product = Product::query()->where('name', 'Product with image')->firstOrFail();
        Storage::disk('public')->assertExists($product->image_path);
    }

    public function test_admin_can_create_a_product_without_an_image(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin())
            ->post(route('product.store'), $this->productData())
            ->assertRedirect(route('product.index'));

        $this->assertDatabaseHas('products', [
            'name' => 'Product with image',
            'image_path' => null,
        ]);
        $this->assertSame([], Storage::disk('public')->allFiles('products'));
    }

    public function test_unsupported_image_format_is_rejected(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin())
            ->from(route('product.create'))
            ->post(route('product.store'), $this->productData([
                'image' => UploadedFile::fake()->create('document.pdf', 20, 'application/pdf'),
            ]))
            ->assertSessionHasErrors('image');

        $this->assertDatabaseCount('products', 0);
        $this->assertSame([], Storage::disk('public')->allFiles('products'));
    }

    public function test_image_larger_than_two_megabytes_is_rejected(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin())
            ->from(route('product.create'))
            ->post(route('product.store'), $this->productData([
                'image' => UploadedFile::fake()->createWithContent(
                    'large.png',
                    str_pad(base64_decode($this->imageFixture('png')), 2 * 1024 * 1024 + 1, "\0")
                ),
            ]))
            ->assertSessionHasErrors('image');

        $this->assertDatabaseCount('products', 0);
        $this->assertSame([], Storage::disk('public')->allFiles('products'));
    }

    public function test_replacing_product_image_stores_new_file_then_removes_old_file(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('products/old-image.jpg', 'old image');
        $product = Product::factory()->create(['image_path' => 'products/old-image.jpg']);

        $this->actingAs($this->admin())
            ->put(route('product.update', $product), $this->productData([
                'name' => $product->name,
                'qty' => $product->qty,
                'price' => $product->price,
                'description' => $product->description,
                'image' => $this->imageFile('replacement.webp', 'webp'),
            ]))
            ->assertRedirect(route('product.index'));

        $product->refresh();
        $this->assertNotSame('products/old-image.jpg', $product->image_path);
        Storage::disk('public')->assertExists($product->image_path);
        Storage::disk('public')->assertMissing('products/old-image.jpg');
    }

    public function test_updating_without_a_new_image_preserves_existing_image(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('products/current-image.jpg', 'existing image');
        $product = Product::factory()->create(['image_path' => 'products/current-image.jpg']);

        $this->actingAs($this->admin())
            ->put(route('product.update', $product), $this->productData([
                'name' => $product->name,
                'qty' => $product->qty,
                'price' => $product->price,
                'description' => $product->description,
            ]))
            ->assertRedirect(route('product.index'));

        $this->assertSame('products/current-image.jpg', $product->fresh()->image_path);
        Storage::disk('public')->assertExists('products/current-image.jpg');
    }

    public function test_failed_database_update_preserves_previous_image_and_cleans_new_upload(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('products/previous-image.jpg', 'previous image');
        $product = Product::factory()->create(['image_path' => 'products/previous-image.jpg']);
        Product::updating(function () {
            throw new RuntimeException('Simulated database update failure.');
        });

        try {
            $this->withoutExceptionHandling()
                ->actingAs($this->admin())
                ->put(route('product.update', $product), $this->productData([
                    'name' => $product->name,
                    'qty' => $product->qty,
                    'price' => $product->price,
                    'description' => $product->description,
                    'image' => $this->imageFile('failed-replacement.png', 'png'),
                ]));

            $this->fail('The simulated update failure should be rethrown.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Simulated database update failure.', $exception->getMessage());
        } finally {
            Product::flushEventListeners();
        }

        $this->assertSame('products/previous-image.jpg', $product->fresh()->image_path);
        Storage::disk('public')->assertExists('products/previous-image.jpg');
        $this->assertSame(['products/previous-image.jpg'], Storage::disk('public')->allFiles('products'));
    }

    public function test_deleting_product_removes_its_associated_image(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('products/delete-me.jpg', 'product image');
        $product = Product::factory()->create(['image_path' => 'products/delete-me.jpg']);

        $this->actingAs($this->admin())
            ->delete(route('product.destroy', $product))
            ->assertRedirect(route('product.index'));

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
        Storage::disk('public')->assertMissing('products/delete-me.jpg');
    }

    public function test_deleting_product_with_missing_image_file_is_safe(): void
    {
        Storage::fake('public');
        $product = Product::factory()->create(['image_path' => 'products/missing-image.jpg']);

        $this->actingAs($this->admin())
            ->delete(route('product.destroy', $product))
            ->assertRedirect(route('product.index'));

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    public function test_regular_users_can_view_product_image_url(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('products/public-image.jpg', 'product image');
        $product = Product::factory()->create(['image_path' => 'products/public-image.jpg']);
        $user = User::factory()->create();
        $imageUrl = asset('storage/'.$product->image_path);

        $this->actingAs($user)
            ->get(route('product.index'))
            ->assertOk()
            ->assertSee($imageUrl, false);
    }

    public function test_admin_edit_form_shows_the_current_product_image(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('products/edit-preview.jpg', 'product image');
        $product = Product::factory()->create(['image_path' => 'products/edit-preview.jpg']);
        $imageUrl = asset('storage/'.$product->image_path);

        $this->actingAs($this->admin())
            ->get(route('product.edit', $product))
            ->assertOk()
            ->assertSee($imageUrl, false)
            ->assertSee('Choose a new file to replace the current image.');
    }

    public function test_regular_users_can_view_product_details_and_image_without_admin_actions(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('products/detail-image.jpg', 'product image');
        $product = Product::factory()->create([
            'name' => 'Product Detail Monitor',
            'description' => 'A detailed product description.',
            'qty' => 6,
            'price' => 58.25,
            'image_path' => 'products/detail-image.jpg',
        ]);
        $user = User::factory()->create();
        $imageUrl = Storage::disk('public')->url($product->image_path);

        $this->actingAs($user)
            ->get(route('product.show', $product))
            ->assertOk()
            ->assertSee('Product Detail Monitor')
            ->assertSee('A detailed product description.')
            ->assertSee('$58.25')
            ->assertSee('6')
            ->assertSee('Low stock')
            ->assertSee('Back to Products')
            ->assertSee($imageUrl, false)
            ->assertDontSee(route('product.edit', $product), false)
            ->assertDontSee(route('product.destroy', $product), false);
    }

    public function test_missing_image_file_shows_placeholder_in_edit_form_and_listing(): void
    {
        Storage::fake('public');
        $product = Product::factory()->create(['image_path' => 'products/missing-image.jpg']);
        $admin = $this->admin();

        $this->actingAs($admin)
            ->get(route('product.edit', $product))
            ->assertOk()
            ->assertSee('No image available')
            ->assertDontSee(asset('storage/'.$product->image_path), false);

        $this->actingAs($admin)
            ->get(route('product.index'))
            ->assertOk()
            ->assertSee('No image available')
            ->assertDontSee(asset('storage/'.$product->image_path), false);
    }

    public function test_missing_image_file_shows_placeholder_in_product_details(): void
    {
        Storage::fake('public');
        $product = Product::factory()->create(['image_path' => 'products/missing-image.jpg']);

        $this->actingAs(User::factory()->create())
            ->get(route('product.show', $product))
            ->assertOk()
            ->assertSee('No image available')
            ->assertDontSee(asset('storage/'.$product->image_path), false);
    }

    public function test_admin_can_view_product_details_with_management_actions(): void
    {
        $product = Product::factory()->create(['name' => 'Admin Detail Product']);

        $this->actingAs($this->admin())
            ->get(route('product.show', $product))
            ->assertOk()
            ->assertSee('Admin Detail Product')
            ->assertSee(route('product.edit', $product), false)
            ->assertSee(route('product.destroy', $product), false);
    }

    public function test_product_details_page_shows_placeholder_when_no_image_exists(): void
    {
        $product = Product::factory()->create([
            'image_path' => null,
            'description' => null,
        ]);

        $this->actingAs(User::factory()->create())
            ->get(route('product.show', $product))
            ->assertOk()
            ->assertSee('No image available')
            ->assertSee('No description provided.');
    }

    public function test_regular_users_cannot_upload_or_replace_product_images(): void
    {
        Storage::fake('public');
        $product = Product::factory()->create();
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('product.store'), $this->productData([
                'image' => $this->imageFile('unauthorized.jpg', 'jpeg'),
            ]))
            ->assertForbidden();

        $this->actingAs($user)
            ->put(route('product.update', $product), $this->productData([
                'name' => $product->name,
                'qty' => $product->qty,
                'price' => $product->price,
                'description' => $product->description,
                'image' => $this->imageFile('unauthorized-replacement.jpg', 'jpeg'),
            ]))
            ->assertForbidden();

        $this->assertNull($product->fresh()->image_path);
        $this->assertSame([], Storage::disk('public')->allFiles('products'));
    }
}
