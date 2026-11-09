@extends('layouts.karle')

@section('title', 'Products')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h1 class="page-title">Products</h1>
                <p class="mt-1 text-sm text-slate-500">Manage your product catalog, stock levels and pricing.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <x-stat-card label="Total Products" :value="number_format($stats['total'])" hint="Products in catalog" tone="indigo" />
            <x-stat-card label="Units in Stock" :value="number_format($stats['units'])" hint="Across all products" tone="emerald" />
            @if (auth()->user()->isAdmin())
                <x-stat-card label="Inventory Value" :value="'$'.number_format($stats['value'], 2)" hint="Qty × unit price" tone="indigo" />
            @endif
            <x-stat-card label="Needs Attention" :value="number_format($stats['attention'])" hint="Low or out of stock" tone="amber" />
        </div>

        <div class="card overflow-hidden shadow-md">
            <div class="flex flex-col gap-3 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-sm font-semibold text-slate-900">Product catalog</h2>
                </div>
                <a href="{{ route('dashboard') }}" class="text-sm font-medium text-indigo-700 hover:text-indigo-800">Back to dashboard</a>
            </div>

            <div class="border-b border-slate-200 px-5 py-4">
                <form method="GET" action="{{ route('product.index') }}" class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-end">
                    <input type="search" name="search" value="{{ $search }}" placeholder="Search products..."
                           class="input max-w-md" aria-label="Search products">
                    @if ($search !== '')
                        <a href="{{ route('product.index') }}" class="btn btn-secondary">Clear search</a>
                    @endif
                    <button type="submit" class="btn btn-secondary">Search</button>
                </form>
            </div>

            @if ($products->isEmpty())
                <p class="px-5 py-10 text-center text-sm text-slate-500">
                    {{ $search !== '' ? 'No products match' : 'No products yet' }}
                </p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full border-collapse">
                        <thead class="bg-slate-50">
                        <tr class="border-b border-slate-200">
                            <th scope="col" class="th w-20 px-3 text-left">Image</th>
                            <th scope="col" class="th text-left">Product</th>
                            <th scope="col" class="th text-left">Status</th>
                            <th scope="col" class="th text-right">Qty</th>
                            <th scope="col" class="th text-right">Unit Price</th>
                            <th scope="col" class="th text-left">Description</th>
                            @if (auth()->user()->role === \App\Models\User::ROLE_ADMIN)
                                <th scope="col" class="th text-right">Actions</th>
                            @endif
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                        @foreach ($products as $product)
                            <tr class="transition hover:bg-slate-50">
                                <td class="td w-20 px-3 py-2 align-middle">
                                    @if ($product->image_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($product->image_path))
                                        <img src="{{ asset('storage/'.$product->image_path) }}"
                                             alt="{{ $product->name }}"
                                             class="h-[56px] w-[56px] max-h-[56px] max-w-[56px] rounded-[8px] border border-[#e2e8f0] bg-[#f8fafc] p-0.5 object-contain">
                                    @else
                                        <span class="flex h-[56px] w-[56px] max-h-[56px] max-w-[56px] items-center justify-center rounded-[8px] border border-[#e2e8f0] bg-[#f8fafc] p-0.5 text-slate-400"
                                              role="img" aria-label="No image available">
                                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                                                <rect x="3" y="3" width="18" height="18" rx="2" stroke-width="1.5" />
                                                <circle cx="8.5" cy="8.5" r="1.5" stroke-width="1.5" />
                                                <path d="m21 15-5-5L5 21" stroke-width="1.5" />
                                            </svg>
                                        </span>
                                    @endif
                                </td>
                                <td class="td py-2 align-middle">
                                    <a href="{{ route('product.show', $product) }}"
                                       class="font-medium text-slate-900 hover:text-indigo-600">{{ $product->name }}</a>
                                </td>
                                <td class="td py-2 align-middle">
                                    <x-stock-badge :product="$product" />
                                </td>
                                <td class="td py-2 text-right align-middle tabular-nums">{{ $product->qty }}</td>
                                <td class="td py-2 text-right align-middle tabular-nums">${{ number_format((float) $product->price, 2) }}</td>
                                <td class="td py-2 align-middle text-slate-600">{{ $product->description }}</td>
                                @if (auth()->user()->role === \App\Models\User::ROLE_ADMIN)
                                    <td class="td py-2 text-right align-middle">
                                        <div class="flex justify-end gap-2">
                                            <a href="{{ route('product.edit', $product) }}" class="btn btn-secondary btn-sm">Edit</a>
                                            <form method="POST" action="{{ route('product.destroy', $product) }}" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-slate-200 px-5 py-4">
                    {{ $products->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
