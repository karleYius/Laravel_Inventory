<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));

        $products = Product::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($searchQuery) use ($search) {
                    $searchQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->appends(['search' => $search]);

        return view('products.index', [
            'products' => $products,
            'search' => $search,
            'stats' => Product::inventoryStats($request->user()->isAdmin()),
        ]);
    }

    public function show(Product $product)
    {
        return view('products.show', compact('product'));
    }

    public function create()
    {
        return view('products.create');
    }

    public function store(Request $request, ActivityLogger $activityLogger)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'qty' => ['required', 'integer', 'min:0'],
            'price' => ['required', 'numeric', 'min:0'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $imagePath = $this->storeImage($request);
        unset($validated['image']);

        try {
            $product = DB::transaction(function () use ($validated, $imagePath, $request, $activityLogger): Product {
                $product = new Product;
                $product->fill([...$validated, 'image_path' => $imagePath]);
                if (! $product->save()) {
                    throw new RuntimeException('The product could not be saved.');
                }

                $activityLogger->log($request->user(), 'product.created', $product, [
                    'after' => $this->productSnapshot($product),
                ]);

                return $product;
            });
        } catch (Throwable $exception) {
            if ($imagePath !== null) {
                $this->cleanupImageAfterFailure($imagePath);
            }

            throw $exception;
        }

        return redirect()->route('product.index')->with('success', 'Product created successfully.');
    }

    public function edit(Product $product)
    {
        return view('products.edit', compact('product'));
    }

    public function update(Request $request, Product $product, ActivityLogger $activityLogger)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'qty' => ['required', 'integer', 'min:0'],
            'price' => ['required', 'numeric', 'min:0'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $newImagePath = $this->storeImage($request);
        unset($validated['image']);
        $previousImagePath = null;

        try {
            DB::transaction(function () use ($product, $validated, $newImagePath, $request, $activityLogger, &$previousImagePath): void {
                $lockedProduct = Product::query()->lockForUpdate()->findOrFail($product->id);
                $previous = $this->productSnapshot($lockedProduct);
                $previousImagePath = $lockedProduct->image_path;

                $lockedProduct->fill($validated);
                if ($newImagePath !== null) {
                    $lockedProduct->image_path = $newImagePath;
                }

                if (! $lockedProduct->save()) {
                    throw new RuntimeException('The product could not be updated.');
                }

                $activityLogger->log($request->user(), 'product.updated', $lockedProduct, [
                    'before' => $previous,
                    'after' => $this->productSnapshot($lockedProduct->refresh()),
                ]);
            });
        } catch (Throwable $exception) {
            if ($newImagePath !== null) {
                $this->cleanupImageAfterFailure($newImagePath);
            }

            throw $exception;
        }

        if ($newImagePath !== null && $previousImagePath !== null && $previousImagePath !== $newImagePath) {
            $this->deleteStoredImage($previousImagePath);
        }

        return redirect()->route('product.index')->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product, ActivityLogger $activityLogger)
    {
        $imagePath = $product->image_path;

        DB::transaction(function () use ($product, $activityLogger): void {
            $lockedProduct = Product::query()->lockForUpdate()->findOrFail($product->id);
            $activityLogger->log(request()->user(), 'product.deleted', $lockedProduct, [
                'before' => $this->productSnapshot($lockedProduct),
            ]);
            $lockedProduct->delete();
        });

        if ($imagePath !== null) {
            $this->deleteStoredImage($imagePath);
        }

        return redirect()->route('product.index')->with('success', 'Product deleted successfully.');
    }

    private function productSnapshot(Product $product): array
    {
        return [
            'name' => $product->name,
            'qty' => (int) $product->qty,
            'price' => (string) $product->price,
            'description' => $product->description,
            'image_path' => $product->image_path,
        ];
    }

    private function storeImage(Request $request): ?string
    {
        if (! $request->hasFile('image')) {
            return null;
        }

        $path = $request->file('image')->store('products', 'public');
        if (! is_string($path) || $path === '') {
            throw new RuntimeException('The product image could not be stored.');
        }

        return $path;
    }

    private function deleteStoredImage(string $path): void
    {
        if (preg_match('/\Aproducts\/[^\/\\\\]+\z/', $path) !== 1) {
            throw new RuntimeException('Refusing to delete an image outside the products storage directory.');
        }

        $disk = Storage::disk('public');
        if ($disk->exists($path) && ! $disk->delete($path)) {
            throw new RuntimeException('The product image could not be deleted from public storage.');
        }
    }

    private function cleanupImageAfterFailure(string $path): void
    {
        try {
            $this->deleteStoredImage($path);
        } catch (Throwable $cleanupException) {
            report($cleanupException);
        }
    }
}
