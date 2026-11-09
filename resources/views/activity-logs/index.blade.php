@extends('layouts.karle')

@section('title', 'Activity Logs')

@section('content')
    <div class="space-y-6">
        <div><h1 class="page-title">Activity Logs</h1><p class="mt-1 text-sm text-slate-600">A record of administrative changes across Stockwise.</p></div>
        <section class="card overflow-hidden shadow-md">
            <form method="GET" action="{{ route('activity-logs.index') }}" class="flex flex-col gap-3 border-b border-slate-200 p-5 sm:flex-row">
                <input type="search" name="search" value="{{ $search }}" class="input" placeholder="Search actor, action, or record" aria-label="Search activity">
                <select name="action" class="input sm:max-w-xs" aria-label="Filter by action">
                    <option value="">All actions</option>
                    @foreach ($actions as $availableAction)
                        <option value="{{ $availableAction }}" @selected($action === $availableAction)>{{ str_replace('.', ' · ', $availableAction) }}</option>
                    @endforeach
                </select>
                <button class="btn btn-secondary" type="submit">Filter</button>
                <a href="{{ route('activity-logs.index') }}" class="btn btn-secondary">Clear</a>
            </form>
            @if ($logs->isEmpty())
                <p class="px-5 py-10 text-center text-sm text-slate-600">No activity records found.</p>
            @else
                <div class="divide-y divide-slate-100">
                    @foreach ($logs as $log)
                        <article class="grid gap-3 px-5 py-4 sm:grid-cols-[minmax(0,1fr)_auto]">
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-slate-900">{{ str_replace('.', ' · ', $log->action) }}</p>
                                <p class="mt-1 text-sm text-slate-700">
                                    <span class="font-medium">{{ $log->user_name }}</span>
                                    @if ($log->subject_name) · {{ $log->subject_name }}@endif
                                    @if ($log->subject_id) <span class="text-slate-500">(#{{ $log->subject_id }})</span>@endif
                                </p>
                                @if ($log->properties)
                                    <details class="mt-2">
                                        <summary class="cursor-pointer text-xs font-medium text-indigo-700">Change details</summary>
                                        <pre class="mt-2 overflow-x-auto rounded-lg bg-slate-50 p-3 text-xs text-slate-700">{{ json_encode($log->properties, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                    </details>
                                @endif
                            </div>
                            <time class="text-xs text-slate-600" datetime="{{ $log->created_at->toIso8601String() }}">{{ $log->created_at->format('M j, Y H:i:s') }}</time>
                        </article>
                    @endforeach
                </div>
                <div class="border-t border-slate-200 px-5 py-4">{{ $logs->links() }}</div>
            @endif
        </section>
    </div>
@endsection
