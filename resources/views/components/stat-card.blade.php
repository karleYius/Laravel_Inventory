@props(['label', 'value', 'hint' => null, 'tone' => 'indigo', 'labelClass' => 'text-slate-500', 'valueClass' => 'text-slate-900'])

@php
    $hintTones = [
        'indigo' => 'text-indigo-600',
        'emerald' => 'text-emerald-600',
        'amber' => 'text-amber-600',
        'rose' => 'text-rose-600',
    ];

    $hintClass = $hintTones[$tone] ?? $hintTones['indigo'];
@endphp

<div {{ $attributes->merge(['class' => 'card p-5 sm:p-6']) }}>
    <p class="text-sm font-medium {{ $labelClass }}">{{ $label }}</p>
    <p class="mt-2 text-3xl font-semibold tracking-tight tabular-nums {{ $valueClass }}">{{ $value }}</p>
    @if ($hint)
        <p class="mt-1.5 text-xs font-medium {{ $hintClass }}">{{ $hint }}</p>
    @endif
</div>
