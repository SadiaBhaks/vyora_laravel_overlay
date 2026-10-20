@props(['end', 'label' => 'Ends in'])
{{-- Counts down to the real promotion end time stored in the database. Shows "Ended" when it passes. --}}
<div x-data="vyoraCountdown('{{ $end->toIso8601String() }}')" x-init="start()" {{ $attributes->merge(['class' => 'inline-flex items-center gap-3']) }}>
    <span class="eyebrow" x-show="!over">{{ $label }}</span>
    <div class="flex items-center gap-1.5 font-display text-2xl font-bold tabular" x-show="!over" role="timer" aria-live="off">
        <template x-if="d > 0"><span class="flex items-baseline gap-0.5"><span x-text="d"></span><span class="text-xs font-medium text-muted">d</span></span></template>
        <span class="flex items-baseline gap-0.5"><span x-text="pad(h)"></span><span class="text-xs font-medium text-muted">h</span></span>
        <span class="flex items-baseline gap-0.5"><span x-text="pad(m)"></span><span class="text-xs font-medium text-muted">m</span></span>
        <span class="flex items-baseline gap-0.5"><span x-text="pad(s)"></span><span class="text-xs font-medium text-muted">s</span></span>
    </div>
    <span x-show="over" x-cloak class="badge badge-soft">Promotion ended</span>
    <span class="sr-only">Ends {{ $end->format('F j, Y g:i A T') }}</span>
</div>
