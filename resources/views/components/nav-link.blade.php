@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center border-b-2 border-indigo-600 px-1 pt-1 text-sm font-semibold leading-5 text-indigo-700 transition duration-150 ease-in-out focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-600'
            : 'inline-flex items-center border-b-2 border-transparent px-1 pt-1 text-sm font-medium leading-5 text-slate-600 transition duration-150 ease-in-out hover:border-slate-300 hover:text-slate-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-600';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
