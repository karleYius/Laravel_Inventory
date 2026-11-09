@extends('layouts.karle')

@section('title', 'Dashboard')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h1 class="page-title">Dashboard</h1>
                <p class="mt-1 text-sm text-slate-600">
                    {{ $isAdmin ? 'Overview of your catalog, stock levels and inventory value.' : 'A clear overview of your products and stock availability.' }}
                </p>
            </div>
            <a href="{{ route('product.index') }}" class="btn btn-secondary self-start sm:self-auto">View all products</a>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <x-stat-card label="Total Products" :value="number_format($stats['total'])" hint="Products in catalog" tone="indigo" />
            <x-stat-card :label="$isAdmin ? 'Units in Stock' : 'Units Available'" :value="number_format($stats['units'])" hint="Across all products" tone="emerald" />
            @if ($isAdmin)
                <x-stat-card label="Inventory Value" :value="'$'.number_format($stats['value'], 2)" hint="Qty × unit price" tone="indigo" />
                <x-stat-card label="Products Needing Attention" :value="number_format($stats['attention'])" hint="Low or out of stock" tone="amber" />
            @else
                <x-stat-card label="Low-stock Products" :value="number_format($stats['low_stock'])" hint="10 units or fewer" tone="amber" />
                <x-stat-card label="Out-of-stock Products" :value="number_format($stats['out_of_stock'])" hint="No units available" tone="rose" />
            @endif
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <section class="card overflow-hidden shadow-md lg:col-span-2">
                <div class="flex items-center justify-between gap-3 border-b border-slate-200 px-5 py-4">
                    <h2 class="text-sm font-semibold text-slate-900">Recently updated products</h2>
                    <a href="{{ route('product.index') }}" class="text-sm font-medium text-indigo-700 hover:text-indigo-800">View all</a>
                </div>

                @if ($recentProducts->isEmpty())
                    <p class="px-5 py-10 text-center text-sm text-slate-600">No products yet. Create your first product to get started.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full border-collapse">
                            <thead class="bg-slate-50">
                                <tr class="border-b border-slate-200">
                                    <th scope="col" class="th">Product</th>
                                    <th scope="col" class="th">Status</th>
                                    <th scope="col" class="th text-right">Qty</th>
                                    <th scope="col" class="th text-right">Unit Price</th>
                                    <th scope="col" class="th">Updated</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                @foreach ($recentProducts as $product)
                                    <tr class="transition hover:bg-slate-50">
                                        <td class="td">
                                            <a href="{{ route('product.show', $product) }}" class="font-medium text-slate-900 hover:text-indigo-700">
                                                {{ $product->name }}
                                            </a>
                                        </td>
                                        <td class="td"><x-stock-badge :product="$product" /></td>
                                        <td class="td text-right tabular-nums">{{ $product->qty }}</td>
                                        <td class="td text-right tabular-nums">${{ number_format((float) $product->price, 2) }}</td>
                                        <td class="td whitespace-nowrap text-slate-600">{{ $product->updated_at->format('M j, Y') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>

            <div class="space-y-6">
                <section class="card overflow-hidden shadow-md">
                    <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                        <h2 class="text-sm font-semibold text-slate-900">Low-stock products</h2>
                        <span class="rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-800">{{ $stats['low_stock'] }}</span>
                    </div>
                    @if ($lowStockProducts->isEmpty())
                        <p class="px-5 py-6 text-sm text-slate-600">No low-stock products.</p>
                    @else
                        <ul class="divide-y divide-slate-100">
                            @foreach ($lowStockProducts as $product)
                                <li class="flex items-center justify-between gap-3 px-5 py-3.5">
                                    <div class="min-w-0">
                                        <a href="{{ route('product.show', $product) }}" class="truncate text-sm font-medium text-slate-900 hover:text-indigo-700">{{ $product->name }}</a>
                                        <p class="mt-0.5 text-xs text-slate-600">Quantity: {{ $product->qty }}</p>
                                    </div>
                                    <x-stock-badge :product="$product" />
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </section>

                <section class="card overflow-hidden shadow-md">
                    <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                        <h2 class="text-sm font-semibold text-slate-900">Out-of-stock products</h2>
                        <span class="rounded-full bg-rose-50 px-2.5 py-1 text-xs font-semibold text-rose-800">{{ $stats['out_of_stock'] }}</span>
                    </div>
                    @if ($outOfStockProducts->isEmpty())
                        <p class="px-5 py-6 text-sm text-slate-600">No out-of-stock products.</p>
                    @else
                        <ul class="divide-y divide-slate-100">
                            @foreach ($outOfStockProducts as $product)
                                <li class="flex items-center justify-between gap-3 px-5 py-3.5">
                                    <a href="{{ route('product.show', $product) }}" class="truncate text-sm font-medium text-slate-900 hover:text-indigo-700">{{ $product->name }}</a>
                                    <x-stock-badge :product="$product" />
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </section>
            </div>
        </div>
    </div>
@endsection
