<x-account-shell title="Wishlist" active="wishlist">
    <h1 class="display-lg">Wishlist</h1>
    @if($items->isEmpty())
        <x-empty-state icon="heart" title="Nothing saved yet" text="Tap the heart on any product to keep it here." :action="route('shop')" actionLabel="Browse products" class="mt-8" />
    @else
        <ul class="mt-8 grid gap-4 md:grid-cols-2">
            @foreach($items as $item)
                @php $p = $item->product; $avail = $p->variants->where('stock', '>', 0); @endphp
                <li class="card grid grid-cols-[7rem_1fr] gap-4 p-3 sm:grid-cols-[9rem_1fr]" x-data="{ gone: false }" x-show="!gone" x-transition>
                    <a href="{{ route('product.show', $p) }}" class="aspect-square overflow-hidden rounded-xl bg-surface-2"><x-img :image="$p->images->first()" :alt="$p->name" :seed="$p->slug.'-1'" /></a>
                    <div class="flex flex-col py-1"><p class="eyebrow !text-[.62rem]">{{ $p->brand?->name }}</p><a href="{{ route('product.show', $p) }}" class="font-semibold hover:underline">{{ $p->name }}</a>
                        <div class="mt-1"><x-price :product="$p" /></div>
                        <p class="mt-1 text-xs {{ $avail->isEmpty() ? 'font-medium text-danger' : 'text-muted' }}">{{ $avail->isEmpty() ? 'Out of stock' : 'In stock' }} · saved {{ $item->created_at->diffForHumans() }}</p>
                        <div class="mt-auto flex flex-wrap gap-2 pt-3">
                            @if($avail->count() === 1)<button type="button" @click="vyora.addToCart({{ $avail->first()->id }})" class="btn btn-primary btn-sm">Move to cart</button>
                            @elseif($avail->count() > 1)<button type="button" @click="vyora.quickView({{ $p->id }})" class="btn btn-primary btn-sm">Choose options</button>@endif
                            <button type="button" class="btn btn-ghost btn-sm" @click="vyora.toggleWishlist('{{ $p->slug }}', {{ $p->id }}, $el); gone = true">Remove</button></div></div>
                </li>
            @endforeach
        </ul>
    @endif
</x-account-shell>
