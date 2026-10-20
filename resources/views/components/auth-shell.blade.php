@props(['title', 'subtitle' => null, 'art' => 'auth'])
<div class="mx-auto grid min-h-[calc(100vh-8rem)] max-w-[88rem] lg:grid-cols-2">
    <div class="relative isolate hidden overflow-hidden lg:block">
        <img src="{{ route('art', ['seed' => $art.'-panel', 'c' => 'lifestyle', 'w' => 'tall']) }}" alt="" class="absolute inset-0 -z-10 size-full object-cover" width="800" height="1000" loading="eager">
        <div class="absolute inset-0 -z-10 bg-gradient-to-t from-black/60 via-transparent to-black/10"></div>
        <div class="flex h-full flex-col justify-between p-12 text-white">
            <x-logo class="text-white [&_svg_rect]:fill-white [&_svg_path]:stroke-[#0f100d]" />
            <p class="hero-title !text-[clamp(2.5rem,5vw,4.5rem)]">Discover<br>differently.</p>
        </div>
    </div>
    <div class="flex items-center px-4 py-12 md:px-12">
        <div class="mx-auto w-full max-w-md">
            <h1 class="display-lg">{{ $title }}</h1>
            @if($subtitle)<p class="mt-2 text-muted">{{ $subtitle }}</p>@endif
            <div class="mt-8">{{ $slot }}</div>
        </div>
    </div>
</div>
