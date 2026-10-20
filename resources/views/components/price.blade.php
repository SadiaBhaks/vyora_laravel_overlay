@props(['product', 'size' => 'text-base'])
@php $list = $product->list_price; @endphp
<span {{ $attributes->merge(['class' => 'inline-flex flex-wrap items-baseline gap-x-2 tabular']) }}>
    <span class="font-semibold {{ $size }}">${{ number_format((float) $product->effective_price, 2) }}</span>
    @if($list)
        <s class="text-sm text-muted" aria-label="Original price ${{ number_format((float) $list, 2) }}">${{ number_format((float) $list, 2) }}</s>
    @endif
</span>
