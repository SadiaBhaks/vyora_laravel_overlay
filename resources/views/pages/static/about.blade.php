<x-layouts.app title="About VYORA" description="VYORA is intelligent commerce: a considered, editorial way to discover products that match your world.">
    <section class="mx-auto max-w-[88rem] px-4 pt-8 md:px-8">
        <x-breadcrumbs :items="[['label' => 'About']]" />
        <p class="eyebrow mb-3">Our story</p>
        <h1 class="hero-title !text-[clamp(2.6rem,9vw,7.5rem)]">A new era<br>of shopping.</h1>
        <p class="mt-8 max-w-2xl text-xl text-muted text-balance">VYORA exists because finding the right thing shouldn't mean scrolling through everything. We pair a tightly edited catalogue with honest, explainable tools that help you get to “yes” faster.</p>
    </section>
    <section class="mx-auto mt-20 grid max-w-[88rem] gap-5 px-4 md:grid-cols-3 md:px-8">
        @foreach([['Edited, not endless', 'Every category is curated. We'd rather show you forty things worth owning than four thousand that aren\'t.', 'layout'], ['Intelligent, but honest', 'VYORA Match uses transparent scoring rules over our real catalogue and explains every recommendation. When something is rule-based, we say so — we never dress it up as magic.', 'sparkles'], ['Real prices, real urgency', 'Countdowns end when promotions end. Stock levels are live. We don\'t invent scarcity, reviews or activity to make a sale.', 'shield']] as [$t, $b, $i])
            <div class="card p-8"><span class="grid size-12 place-items-center rounded-full bg-accent text-accent-ink"><x-icon :name="$i" class="size-6" /></span><h2 class="mt-5 font-display text-2xl font-bold">{{ $t }}</h2><p class="mt-2 text-muted">{{ $b }}</p></div>
        @endforeach
    </section>
    <section class="mx-auto mt-20 grid max-w-[88rem] items-center gap-10 px-4 md:px-8 lg:grid-cols-2">
        <div class="aspect-[4/3] overflow-hidden rounded-[2rem] bg-surface-2"><img src="{{ route('art', ['seed' => 'about-philosophy', 'c' => 'lifestyle', 'w' => 'wide']) }}" alt="" loading="lazy" class="size-full object-cover" width="1600" height="900"></div>
        <div><p class="eyebrow mb-2">Design philosophy</p><h2 class="display-lg text-balance">Quiet interfaces. Loud products.</h2>
            <div class="prose-vyora mt-4"><p>We design VYORA the way we'd like to shop: generous space, confident typography, and as little chrome as possible between you and the product. Light and dark themes are each tuned by hand, motion is subtle and respects your reduced-motion setting, and every interaction works with a keyboard, a thumb or a mouse.</p><p>Behind the scenes, prices are always calculated on the server from our records, payments are handled by Stripe so your card details never touch our systems, and your account data is yours.</p></div>
            <div class="mt-6 flex gap-3"><a href="{{ route('shop') }}" class="btn btn-primary">Shop now</a><a href="{{ route('contact') }}" class="btn btn-ghost">Get in touch</a></div></div>
    </section>
</x-layouts.app>
