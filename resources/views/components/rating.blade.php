@props(['value' => 0, 'count' => 0, 'size' => 'size-3.5', 'showCount' => true])
<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 text-xs']) }}>
    @if($count > 0)
        <span class="inline-flex" role="img" aria-label="Rated {{ number_format($value, 1) }} out of 5">
            @for($i = 1; $i <= 5; $i++)
                <x-icon name="star" :class="$size.' '.($i <= round($value) ? 'fill-current text-ink' : 'text-line')" :stroke="1.5" />
            @endfor
        </span>
        @if($showCount)<span class="text-muted tabular">{{ number_format($value, 1) }} <span aria-hidden="true">·</span> {{ $count }}</span>@endif
    @else
        <span class="text-muted">No reviews yet</span>
    @endif
</span>
