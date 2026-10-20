@props(['eyebrow' => null, 'title', 'link' => null, 'linkLabel' => 'View all'])
<div {{ $attributes->merge(['class' => 'mb-7 flex flex-wrap items-end justify-between gap-4']) }}>
    <div>
        @if($eyebrow)<p class="eyebrow mb-2">{{ $eyebrow }}</p>@endif
        <h2 class="display-lg text-balance">{{ $title }}</h2>
    </div>
    @if($link)
        <a href="{{ $link }}" class="group inline-flex items-center gap-1.5 text-sm font-semibold underline-offset-4 hover:underline">
            {{ $linkLabel }} <x-icon name="arrow-right" class="size-4 transition group-hover:translate-x-0.5" />
        </a>
    @endif
</div>
