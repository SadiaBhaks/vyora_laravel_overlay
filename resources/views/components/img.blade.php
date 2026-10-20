@props(['image' => null, 'alt' => '', 'class' => '', 'eager' => false, 'seed' => null, 'cat' => 'lifestyle'])
@php
    $src = $image?->src ?? route('art', ['seed' => $seed ?: \Illuminate\Support\Str::slug($alt ?: 'vyora'), 'c' => $cat]);
    $fallback = route('art', ['seed' => $seed ?: \Illuminate\Support\Str::slug($alt ?: 'vyora'), 'c' => $cat]);
@endphp
{{-- If a remote/uploaded image ever fails, it swaps to generated artwork so layouts never break. --}}
<img src="{{ $src }}" alt="{{ $alt ?: ($image?->alt ?? '') }}" width="800" height="800"
     loading="{{ $eager ? 'eager' : 'lazy' }}" decoding="async" @if($eager) fetchpriority="high" @endif
     onerror="this.onerror=null;this.src='{{ $fallback }}'"
     {{ $attributes->merge(['class' => 'size-full object-cover '.$class]) }}>
