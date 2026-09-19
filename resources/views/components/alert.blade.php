@props(['type' => 'success'])

@php
    $classes = match($type) {
        'success' => 'bg-emerald-50 border-emerald-200 text-emerald-800',
        'error', 'danger' => 'bg-red-50 border-red-200 text-red-800',
        'warning' => 'bg-amber-50 border-amber-200 text-amber-800',
        default => 'bg-blue-50 border-blue-200 text-blue-800',
    };
    $icon = match($type) {
        'success' => 'fa-circle-check text-emerald-500',
        'error', 'danger' => 'fa-circle-exclamation text-red-500',
        'warning' => 'fa-triangle-exclamation text-amber-500',
        default => 'fa-circle-info text-blue-500',
    };
@endphp

<div x-data="{ show: true }" x-show="show" x-transition class="flex items-center justify-between p-4 mb-4 text-sm border rounded-xl shadow-sm {{ $classes }}" role="alert">
    <div class="flex items-center gap-2">
        <i class="fa-solid {{ $icon }} text-base shrink-0"></i>
        <div>{{ $slot }}</div>
    </div>
    <button @click="show = false" type="button" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
        <i class="fa-solid fa-xmark"></i>
    </button>
</div>
