@props(['label', 'value', 'icon' => 'chart', 'tone' => 'indigo', 'hint' => null])
@php
$tones = [
    'indigo' => 'bg-indigo-50 text-indigo-600',
    'emerald' => 'bg-emerald-50 text-emerald-600',
    'amber' => 'bg-amber-50 text-amber-600',
    'violet' => 'bg-violet-50 text-violet-600',
];
@endphp
<div class="card flex items-center justify-between p-5">
    <div>
        <p class="text-sm font-medium text-slate-500">{{ $label }}</p>
        <p class="mt-1 text-2xl font-black tracking-tight text-ink">{{ $value }}</p>
        @if($hint)<p class="mt-2 text-xs font-medium text-emerald-600">{{ $hint }}</p>@endif
    </div>
    <span class="grid h-12 w-12 place-items-center rounded-2xl {{ $tones[$tone] ?? $tones['indigo'] }}">
        <x-icon :name="$icon" class="h-6 w-6" />
    </span>
</div>
