@props(['partner'])

<span {{ $attributes->class(['inline-flex min-w-0 items-center gap-1.5']) }}>
    <span class="truncate">{{ $partner->business_name }}</span>
    @if($partner->verification_status === 'verified')
        <span title="Mitra Terverifikasi" aria-label="Mitra Terverifikasi" class="shrink-0 text-emerald-500">
            <x-icon name="shield" class="h-4 w-4" />
        </span>
    @endif
</span>
