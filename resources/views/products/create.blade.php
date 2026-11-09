@extends('layouts.karle')

@section('title', 'New Product')

@section('content')
    <div class="mx-auto max-w-2xl">
        <div class="mb-6">
            <h1 class="page-title">New Product</h1>
            <p class="mt-1 text-sm text-slate-500">Add a new item to your catalog.</p>
        </div>

        <div class="card p-6 shadow-md sm:p-8">
            <form method="POST" action="{{ route('product.store') }}" enctype="multipart/form-data">
                @csrf

                @include('partials.product-form', ['product' => new \App\Models\Product])

                <div class="mt-8 flex flex-col-reverse gap-3 border-t border-slate-200 pt-6 sm:flex-row sm:justify-end">
                    <a href="{{ route('product.index') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Create product</button>
                </div>
            </form>
        </div>
    </div>
@endsection
