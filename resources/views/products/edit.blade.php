@extends('layouts.karle')

@section('title', 'Edit Product')

@section('content')
    <div class="mx-auto max-w-2xl">
        <div class="mb-6 flex items-center gap-3">
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="page-title">Edit Product</h1>
                    <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600 tabular-nums">
                        #{{ $product->id }}
                    </span>
                </div>
                <p class="mt-1 text-sm text-slate-500">Update the details for &ldquo;{{ $product->name }}&rdquo;.</p>
            </div>
        </div>

        <div class="card p-6 shadow-md sm:p-8">
            <form method="POST" action="{{ route('product.update', $product) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                @include('partials.product-form', ['product' => $product])

                <div class="mt-8 flex flex-col-reverse gap-3 border-t border-slate-200 pt-6 sm:flex-row sm:justify-end">
                    <a href="{{ route('product.index') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Save changes</button>
                </div>
            </form>
        </div>
    </div>
@endsection
