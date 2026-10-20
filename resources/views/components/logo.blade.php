@props(['class' => ''])
{{-- Original VYORA mark: two offset slabs forming a "V" with a single accent node. Not derived from any existing brand. --}}
<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-2.5 '.$class]) }}>
    <svg viewBox="0 0 32 32" class="size-8 shrink-0" aria-hidden="true">
        <rect x="1" y="1" width="30" height="30" rx="9" fill="var(--ink)"/>
        <path d="M8 9.5 16 24l8-14.5" fill="none" stroke="var(--bg)" stroke-width="3.2" stroke-linecap="round" stroke-linejoin="round"/>
        <circle cx="23.6" cy="9" r="2.7" fill="var(--accent)"/>
    </svg>
    <span class="font-display text-[1.35rem] font-extrabold uppercase leading-none tracking-[-0.04em]">Vyora</span>
</span>
