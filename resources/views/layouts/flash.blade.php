@if (session('success'))
    <div class="mb-6 flex items-start justify-between gap-4 rounded-xl border border-emerald-200 bg-white px-4 py-3 text-sm text-emerald-800 shadow-sm"
         role="status">
        <div class="flex items-start gap-2.5">
            <svg class="mt-0.5 h-4 w-4 shrink-0 text-emerald-600" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.7-9.3a1 1 0 0 0-1.4-1.4L9 10.58l-1.3-1.3a1 1 0 0 0-1.4 1.42l2 2a1 1 0 0 0 1.4 0l4-4Z" clip-rule="evenodd"/>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
        <button type="button" class="shrink-0 rounded p-0.5 text-emerald-600 hover:bg-emerald-100"
                onclick="this.closest('[role=status]').remove()" aria-label="Dismiss message">
            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path d="M6.3 4.9 4.9 6.3 8.6 10l-3.7 3.7 1.4 1.4L10 11.4l3.7 3.7 1.4-1.4L11.4 10l3.7-3.7-1.4-1.4L10 8.6 6.3 4.9Z"/>
            </svg>
        </button>
    </div>
@endif

@if (session('error'))
    <div class="mb-6 flex items-start justify-between gap-4 rounded-xl border border-rose-200 bg-white px-4 py-3 text-sm text-rose-800 shadow-sm"
         role="alert">
        <div class="flex items-start gap-2.5">
            <svg class="mt-0.5 h-4 w-4 shrink-0 text-rose-600" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm-.75-4.75a.75.75 0 1 0 1.5 0 .75.75 0 0 0-1.5 0Zm0-5.5a.75.75 0 0 0-.75.75V6a.75.75 0 0 0 1.5 0v1.75a.75.75 0 0 0-.75-.75Z" clip-rule="evenodd"/>
            </svg>
            <span>{{ session('error') }}</span>
        </div>
        <button type="button" class="shrink-0 rounded p-0.5 text-rose-600 hover:bg-rose-100"
                onclick="this.closest('[role=alert]').remove()" aria-label="Dismiss message">
            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path d="M6.3 4.9 4.9 6.3 8.6 10l-3.7 3.7 1.4 1.4L10 11.4l3.7 3.7 1.4-1.4L11.4 10l3.7-3.7-1.4-1.4L10 8.6 6.3 4.9Z"/>
            </svg>
        </button>
    </div>
@endif

@if ($errors->any())
    <div class="mb-6 rounded-xl border border-rose-200 bg-white px-4 py-3 text-sm text-rose-800 shadow-sm" role="alert">
        <p class="font-semibold">Please review the following:</p>
        <ul class="mt-1.5 list-disc space-y-0.5 pl-5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
