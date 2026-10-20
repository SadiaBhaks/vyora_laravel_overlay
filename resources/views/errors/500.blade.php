<x-layouts.app title="Something went wrong" :noindex="true">
    <div class="mx-auto flex min-h-[60vh] max-w-xl flex-col items-center justify-center px-4 py-20 text-center">
        <p class="font-display text-8xl font-bold tracking-tighter">500</p>
        <h1 class="mt-4 font-display text-2xl font-bold">Something went wrong</h1>
        <p class="mt-2 text-muted">We've hit an unexpected problem. Please try again shortly.</p>
        <div class="mt-8 flex gap-3"><a href="{{ route('home') }}" class="btn btn-primary">Back home</a><a href="{{ route('shop') }}" class="btn btn-ghost">Shop</a></div>
    </div>
</x-layouts.app>
