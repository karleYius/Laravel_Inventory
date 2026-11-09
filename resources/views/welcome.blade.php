<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Stockwise</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-[#D1D5DB] font-sans text-slate-900 antialiased">
        <div class="mx-auto flex min-h-screen max-w-7xl flex-col px-4 sm:px-6 lg:px-8">
            <header class="flex flex-wrap items-center justify-between gap-4 py-5 sm:py-7">
                <a href="{{ route('home') }}" class="rounded-xl focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-600">
                    <x-brand-logo />
                </a>
                <nav class="flex items-center gap-2 sm:gap-3" aria-label="Account">
                    <a href="{{ route('login') }}" class="btn btn-secondary">Log in</a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="btn btn-primary">Register</a>
                    @endif
                </nav>
            </header>

            <main class="flex flex-1 items-center py-10 sm:py-16">
                <div class="grid w-full items-center gap-10 lg:grid-cols-[1.05fr_0.95fr] lg:gap-16">
                    <section class="max-w-2xl">
                        <p class="inline-flex items-center gap-2 rounded-full border border-indigo-200 bg-white/70 px-3 py-1.5 text-xs font-semibold uppercase tracking-wide text-indigo-700">
                            <span class="h-2 w-2 rounded-full bg-indigo-600" aria-hidden="true"></span>
                            Inventory, in good order
                        </p>
                        <h1 class="mt-6 text-4xl font-semibold tracking-tight text-slate-950 sm:text-5xl lg:text-6xl">
                            Know your stock.<br>
                            <span class="text-indigo-700">Move with confidence.</span>
                        </h1>
                        <p class="mt-5 max-w-xl text-base leading-7 text-slate-700 sm:text-lg">
                            Keep your product catalog, stock levels and inventory value clear in one practical workspace.
                        </p>
                        <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                            <a href="{{ route('login') }}" class="btn btn-primary px-5 py-3">Open your workspace</a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="btn btn-secondary px-5 py-3">Create an account</a>
                            @endif
                        </div>
                        <p class="mt-6 text-sm text-slate-600">A clearer view of your inventory, from first item to next reorder.</p>
                    </section>

                    <section class="card relative overflow-hidden p-5 shadow-md sm:p-8" aria-label="Stockwise inventory preview">
                        <div class="flex items-center justify-between gap-4 border-b border-slate-200 pb-5">
                            <div>
                                <p class="text-sm font-semibold text-slate-900">Inventory overview</p>
                                <p class="mt-1 text-xs text-slate-500">A considered view of your catalog</p>
                            </div>
                            <span class="rounded-lg bg-indigo-50 p-2.5 text-indigo-700" aria-hidden="true">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path d="m12 3 8 4.5v9L12 21l-8-4.5v-9L12 3Z" stroke-linejoin="round" stroke-width="1.7"/>
                                    <path d="m4.5 7.7 7.5 4.4 7.5-4.4M12 12.1v8.4" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"/>
                                </svg>
                            </span>
                        </div>
                        <div class="grid grid-cols-2 gap-3 py-5 sm:gap-4">
                            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                                <p class="text-xs font-medium text-slate-600">Products tracked</p>
                                <p class="mt-2 text-2xl font-semibold tracking-tight text-slate-900">Catalog</p>
                            </div>
                            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                                <p class="text-xs font-medium text-slate-600">Stock visibility</p>
                                <p class="mt-2 text-2xl font-semibold tracking-tight text-slate-900">In focus</p>
                            </div>
                        </div>
                        <div class="space-y-3 rounded-xl bg-slate-50 p-4">
                            <div class="flex items-center justify-between gap-3">
                                <span class="text-sm font-medium text-slate-800">Product details</span>
                                <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">Organized</span>
                            </div>
                            <div class="h-2 rounded-full bg-slate-200">
                                <div class="h-2 w-3/4 rounded-full bg-indigo-600"></div>
                            </div>
                            <p class="text-xs leading-5 text-slate-600">Search, review and manage your inventory from one place.</p>
                        </div>
                    </section>
                </div>
            </main>

            <footer class="border-t border-slate-400/70 py-5 text-center text-xs text-slate-600">
                Stockwise · Product management
            </footer>
        </div>
    </body>
</html>
