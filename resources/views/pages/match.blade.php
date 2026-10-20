<x-layouts.app title="VYORA Match" description="Answer five quick questions and get products from the VYORA catalogue that fit your style, budget and needs — with the reasons why.">
    <div class="relative isolate overflow-hidden">
        <div class="pointer-events-none absolute -left-32 top-0 -z-10 size-[36rem] rounded-full bg-accent/25 blur-3xl dark:bg-accent/10" aria-hidden="true"></div>
        <div class="mx-auto max-w-[88rem] px-4 pb-10 pt-10 md:px-8 md:pt-16">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <p class="mb-4 inline-flex items-center gap-2 rounded-full bg-accent px-3 py-1 text-xs font-bold uppercase tracking-widest text-accent-ink"><x-icon name="sparkles" class="size-3.5" /> Vyora Match</p>
                <h1 class="display-xl text-balance uppercase">Find what fits your world</h1>
            </div>
            <livewire:match-wizard />
        </div>
    </div>
</x-layouts.app>
