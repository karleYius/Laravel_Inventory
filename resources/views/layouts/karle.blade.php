<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') · Stockwise</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#D1D5DB] font-sans text-slate-900 antialiased">
    <header class="sticky top-0 z-40 border-b border-slate-200 bg-white shadow-sm">
        <div class="mx-auto flex min-h-16 max-w-7xl flex-wrap items-center justify-between gap-x-4 gap-y-2 px-4 py-2 sm:px-6 lg:px-8">
            <div class="flex min-w-0 flex-1 flex-wrap items-center gap-3 sm:gap-6">
                <a href="{{ route('dashboard') }}" class="flex shrink-0 items-center text-slate-900 focus-visible:rounded-xl focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-600">
                    <x-brand-logo class="gap-2" />
                </a>
                <nav class="flex flex-wrap items-center gap-0.5 text-xs font-medium sm:gap-1 sm:text-sm" aria-label="Main">
                    {{-- CHANGED: 'home' -> 'dashboard' (twice) --}}
                    <a href="{{ route('dashboard') }}"
                       aria-current="{{ request()->routeIs('dashboard') ? 'page' : 'false' }}"
                       class="rounded-lg px-2.5 py-2 transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-600 sm:px-3 {{ request()->routeIs('dashboard') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                        Dashboard
                    </a>
                    <a href="{{ route('product.index') }}"
                       aria-current="{{ request()->routeIs('product.*') ? 'page' : 'false' }}"
                       class="rounded-lg px-2.5 py-2 transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-600 sm:px-3 {{ request()->routeIs('product.*') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                        Products
                    </a>
                    @if (auth()->user()->isAdmin())
                        <a href="{{ route('activity-logs.index') }}" class="rounded-lg px-2.5 py-2 transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-600 sm:px-3 {{ request()->routeIs('activity-logs.*') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">Activity</a>
                        <a href="{{ route('users.index') }}" class="rounded-lg px-2.5 py-2 transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-600 sm:px-3 {{ request()->routeIs('users.*') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">Users</a>
                    @endif
                </nav>
            </div>

            <div class="flex shrink-0 items-center gap-2 sm:gap-3">
                @auth
                    @if (auth()->user()->role === \App\Models\User::ROLE_ADMIN)
                        <a href="{{ route('product.create') }}" class="btn btn-primary px-3 py-2 sm:px-4 sm:py-2.5">
                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path d="M10 3a1 1 0 0 1 1 1v5h5a1 1 0 1 1 0 2h-5v5a1 1 0 1 1-2 0v-5H4a1 1 0 1 1 0-2h5V4a1 1 0 0 1 1-1Z"/>
                            </svg>
                            <span class="hidden sm:inline">Add Product</span>
                            <span class="sm:hidden">Add</span>
                        </a>
                    @endif

                    <div class="flex items-center gap-2 border-l border-slate-200 pl-2 sm:gap-3 sm:pl-3">
                        <a href="{{ route('profile.edit') }}"
                           class="flex max-w-20 flex-col text-sm font-medium leading-tight text-slate-700 hover:text-slate-900 focus-visible:rounded focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-600 sm:max-w-40">
                            <span class="truncate">{{ Auth::user()->name }}</span>
                            <span @class([
                                'mt-1 inline-flex w-fit rounded-full px-1.5 py-0.5 text-[10px] font-semibold tracking-wide',
                                'bg-indigo-50 text-indigo-700' => Auth::user()->role === \App\Models\User::ROLE_ADMIN,
                                'bg-slate-100 text-slate-600' => Auth::user()->role !== \App\Models\User::ROLE_ADMIN,
                            ])>{{ strtoupper(Auth::user()->role) }}</span>
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="btn btn-secondary btn-sm">Log out</button>
                        </form>
                    </div>
                @endauth
            </div>
        </div>
    </header>

    <main class="mx-auto w-full max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        @include('layouts.flash')

        @yield('content')
    </main>

    <footer class="mx-auto w-full max-w-7xl px-4 pb-10 sm:px-6 lg:px-8">
        <p class="border-t border-slate-400/70 pt-6 text-center text-xs text-slate-600">
            Stockwise · Product management
        </p>
    </footer>

    @stack('scripts')
</body>
</html>