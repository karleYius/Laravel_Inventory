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
    <div class="flex min-h-screen flex-col items-center justify-center px-4 py-10">
        <a href="{{ route('home') }}" class="mb-6 rounded-xl focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-600">
            <x-brand-logo class="gap-3" />
        </a>

        <div class="card w-full max-w-md p-6 shadow-md sm:p-8">
            {{ $slot }}
        </div>

        <p class="mt-6 text-xs text-slate-600">
            Stockwise · Product management
        </p>
    </div>
</body>
</html>