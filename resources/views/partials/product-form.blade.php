@php($product = $product ?? new \App\Models\Product)

<div class="space-y-5">
    <div>
        <label for="name" class="label">Product name <span class="text-rose-500">*</span></label>
        <input id="name" name="name" type="text" maxlength="255" required
               value="{{ old('name', $product->name) }}"
               placeholder="e.g. Ergonomic Office Chair"
               class="input @error('name') input-invalid @enderror">
        @error('name')
            <p class="field-error">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="qty" class="label">Quantity in stock <span class="text-rose-500">*</span></label>
        <input id="qty" name="qty" type="number" min="0" step="1" required
               value="{{ old('qty', $product->qty) }}"
               placeholder="0"
               class="input @error('qty') input-invalid @enderror">
        @error('qty')
            <p class="field-error">{{ $message }}</p>
        @enderror
    </div>

    <div class="grid gap-5 sm:grid-cols-2">
        <div>
            <label for="price" class="label">Unit price <span class="text-rose-500">*</span></label>
            <div class="relative">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex w-9 items-center justify-center border-r border-slate-200 text-sm text-slate-400">$</span>
                <input id="price" name="price" type="number" min="0" step="0.01" required
                       value="{{ old('price', $product->price) }}"
                       placeholder="0.00"
                       class="input pl-10 @error('price') input-invalid @enderror">
            </div>
            @error('price')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div>
        <label for="description" class="label">Description</label>
        <textarea id="description" name="description" rows="4"
                  placeholder="Describe the product, its key features and details..."
                  class="input resize-y @error('description') input-invalid @enderror">{{ old('description', $product->description) }}</textarea>
        @error('description')
            <p class="field-error">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="image" class="label">Product image <span class="text-slate-400">(optional, max 2 MB)</span></label>
        @if ($product->image_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($product->image_path))
            <div class="mb-3">
                <img src="{{ asset('storage/'.$product->image_path) }}"
                     alt="{{ $product->name }}"
                     class="h-[96px] w-[96px] max-h-[96px] max-w-[96px] rounded-[8px] border border-[#e2e8f0] bg-[#f8fafc] p-0.5 object-contain">
                <p class="mt-1 text-xs text-slate-500">Choose a new file to replace the current image.</p>
            </div>
        @else
            <div class="mb-3 flex h-[96px] w-[96px] max-h-[96px] max-w-[96px] items-center justify-center rounded-[8px] border border-[#e2e8f0] bg-[#f8fafc] p-0.5 text-slate-400"
                 role="img" aria-label="No image available">
                <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                    <rect x="3" y="3" width="18" height="18" rx="2" stroke-width="1.5" />
                    <circle cx="8.5" cy="8.5" r="1.5" stroke-width="1.5" />
                    <path d="m21 15-5-5L5 21" stroke-width="1.5" />
                </svg>
            </div>
        @endif
        <input id="image" name="image" type="file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
               class="input @error('image') input-invalid @enderror">
        <p class="mt-1 text-xs text-slate-500">Accepted formats: JPEG, PNG, WebP.</p>
        @error('image')
            <p class="field-error">{{ $message }}</p>
        @enderror
    </div>
</div>
