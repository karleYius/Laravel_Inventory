@extends('layouts.karle')

@section('title', $product->name)

@section('content')
    <div class="mx-auto max-w-5xl space-y-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <a href="{{ route('product.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">
                    &larr; Back to Products
                </a>
                <h1 class="page-title mt-2">{{ $product->name }}</h1>
            </div>
            @if (auth()->user()->isAdmin())
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('product.edit', $product) }}" class="btn btn-secondary">Edit</a>
                    <form method="POST" action="{{ route('product.destroy', $product) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            @endif
        </div>

        <section class="card overflow-hidden shadow-md">
            <div class="grid gap-6 p-5 sm:p-8 md:grid-cols-2">
                <div class="flex min-h-64 items-center justify-center overflow-hidden rounded-2xl border border-slate-200 bg-slate-50 p-3">
                    @if ($product->image_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($product->image_path))
                        <img src="{{ asset('storage/'.$product->image_path) }}"
                             alt="{{ $product->name }}"
                             class="max-h-[28rem] w-full object-contain">
                    @else
                        <div class="flex flex-col items-center gap-2 p-8 text-slate-400" role="img" aria-label="No image available">
                            <svg class="h-14 w-14" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                                <rect x="3" y="3" width="18" height="18" rx="2" stroke-width="1.5" />
                                <circle cx="8.5" cy="8.5" r="1.5" stroke-width="1.5" />
                                <path d="m21 15-5-5L5 21" stroke-width="1.5" />
                            </svg>
                            <span class="text-sm">No image available</span>
                        </div>
                    @endif
                </div>

                <div class="space-y-6">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500">Product details</h2>
                        <x-stock-badge :product="$product" />
                    </div>

                    <dl class="space-y-5">
                        <div>
                            <dt class="text-sm font-medium text-slate-500">Description</dt>
                            <dd class="mt-1 whitespace-pre-line text-sm leading-6 text-slate-800">
                                {{ $product->description ?: 'No description provided.' }}
                            </dd>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <dt class="text-sm font-medium text-slate-500">Unit price</dt>
                                <dd class="mt-1 text-lg font-semibold tabular-nums text-slate-900">
                                    ${{ number_format((float) $product->price, 2) }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-slate-500">Quantity in stock</dt>
                                <dd class="mt-1 text-lg font-semibold tabular-nums text-slate-900">{{ $product->qty }}</dd>
                            </div>
                        </div>
                    </dl>
                </div>
            </div>
        </section>
    </div>
@endsection
