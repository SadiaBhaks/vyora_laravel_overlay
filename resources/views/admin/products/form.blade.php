@php
    $p = $product;
    $variants = old('variants', $p->exists ? $p->allVariants->where('is_active', true)->map(fn ($v) => ['id' => $v->id, 'color' => $v->color, 'color_hex' => $v->color_hex, 'size' => $v->size, 'price_override' => $v->price_override, 'stock' => $v->stock])->values()->all() : [['id' => null, 'color' => '', 'color_hex' => '', 'size' => '', 'price_override' => '', 'stock' => 0]]);
    $lines = fn ($a) => implode("\n", (array) $a);
    $pairs = fn ($a) => collect((array) $a)->map(fn ($v, $k) => "$k: $v")->implode("\n");
@endphp
<x-layouts.admin :title="$p->exists ? 'Edit product' : 'New product'">
    <a href="{{ route('admin.products.index') }}" class="text-sm text-muted hover:text-ink">← Products</a>
    <h1 class="display-lg mt-2">{{ $p->exists ? 'Edit product' : 'New product' }}</h1>
    <form method="POST" enctype="multipart/form-data" action="{{ $p->exists ? route('admin.products.update', $p) : route('admin.products.store') }}" class="mt-8 grid gap-6 lg:grid-cols-[1fr_20rem]" novalidate>
        @csrf @if($p->exists) @method('PUT') @endif
        <div class="space-y-6">
            <section class="card space-y-4 p-6"><h2 class="font-display text-lg font-bold">Details</h2>
                <x-field name="name" label="Name" :value="$p->name" required />
                <div class="grid gap-4 sm:grid-cols-2"><x-field name="slug" label="Slug" :value="$p->slug" help="Blank = generated from name." /><x-field name="sku" label="SKU" :value="$p->sku" required /></div>
                <x-field name="short_description" label="Short description" :value="$p->short_description" required />
                <x-field name="description" type="textarea" rows="6" label="Description" :value="$p->description" required />
                <x-field name="materials" label="Materials" :value="$p->materials" />
                <x-field name="features" type="textarea" label="Features (one per line)" :value="$lines($p->features)" />
                <x-field name="specifications" type="textarea" label="Specifications (Label: value, one per line)" :value="$pairs($p->specifications)" />
                <x-field name="attributes_text" type="textarea" label="Filter attributes (Name: value, one per line)" :value="$p->attributes()->get()->map(fn ($a) => $a->name.': '.$a->value)->implode(PHP_EOL)" help="Example: Style: Minimal" />
                <x-field name="tags" label="Tags (comma separated)" :value="$p->tags->pluck('name')->implode(', ')" /></section>
            <section class="card p-6" x-data="{ rows: @js($variants) }"><h2 class="font-display text-lg font-bold">Variants & stock</h2>
                <p class="field-help mb-3">Editing stock here records an inventory adjustment. Variants already sold are deactivated rather than deleted.</p>
                <div class="space-y-3"><template x-for="(r, i) in rows" :key="i">
                    <div class="grid grid-cols-2 gap-2 rounded-xl border border-line p-3 sm:grid-cols-6">
                        <input type="hidden" :name="`variants[${i}][id]`" :value="r.id">
                        <input class="input" placeholder="Color" :name="`variants[${i}][color]`" x-model="r.color" aria-label="Color">
                        <input class="input" placeholder="#hex" :name="`variants[${i}][color_hex]`" x-model="r.color_hex" aria-label="Color hex">
                        <input class="input" placeholder="Size" :name="`variants[${i}][size]`" x-model="r.size" aria-label="Size">
                        <input class="input" type="number" step="0.01" placeholder="Price override" :name="`variants[${i}][price_override]`" x-model="r.price_override" aria-label="Price override">
                        <input class="input" type="number" min="0" placeholder="Stock" :name="`variants[${i}][stock]`" x-model="r.stock" aria-label="Stock" required>
                        <button type="button" class="btn btn-ghost btn-sm" @click="rows.length > 1 && rows.splice(i, 1)" :disabled="rows.length < 2">Remove</button></div></template></div>
                <button type="button" class="btn btn-ghost btn-sm mt-3" @click="rows.push({id:null,color:'',color_hex:'',size:'',price_override:'',stock:0})">Add variant</button></section>
            <section class="card space-y-4 p-6"><h2 class="font-display text-lg font-bold">Images</h2>
                @if($p->exists && $p->images->isNotEmpty())<ul class="flex flex-wrap gap-3">@foreach($p->images as $img)<li class="w-24 text-center text-xs"><img src="{{ $img->src }}" alt="{{ $img->alt }}" class="aspect-square w-full rounded-lg object-cover" width="96" height="96"><button type="submit" form="del-{{ $img->id }}" class="mt-1 text-danger underline">Remove</button></li>@endforeach</ul>@else<p class="text-sm text-muted">No uploaded images — generated artwork is used until you add some.</p>@endif
                <div><label class="label" for="new_images">Upload (JPG/PNG/WebP, ≤4MB)</label><input id="new_images" type="file" name="new_images[]" multiple accept="image/jpeg,image/png,image/webp" class="input"></div>
                <x-field name="image_urls" type="textarea" rows="2" label="Or HTTPS image URLs (one per line)" />
                <x-field name="image_alt" label="Alt text for new images" /></section>
        </div>
        <aside class="space-y-6">
            <section class="card space-y-4 p-6"><h2 class="font-display text-lg font-bold">Publish</h2>
                <x-field name="status" type="select" label="Status">@foreach(['draft','active','archived'] as $s)<option value="{{ $s }}" @selected(old('status', $p->status) === $s)>{{ ucfirst($s) }}</option>@endforeach</x-field>
                <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_featured" value="1" class="size-4 accent-[var(--ink)]" @checked(old('is_featured', $p->is_featured))> Featured</label>
                <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_new" value="1" class="size-4 accent-[var(--ink)]" @checked(old('is_new', $p->is_new))> Mark as new</label>
                <button class="btn btn-primary w-full">Save product</button></section>
            <section class="card space-y-4 p-6"><h2 class="font-display text-lg font-bold">Organize</h2>
                <x-field name="brand_id" type="select" label="Brand" required><option value="">Select…</option>@foreach($brands as $b)<option value="{{ $b->id }}" @selected((int) old('brand_id', $p->brand_id) === $b->id)>{{ $b->name }}</option>@endforeach</x-field>
                <x-field name="category_id" type="select" label="Category" required><option value="">Select…</option>@foreach($categories as $c)<option value="{{ $c->id }}" @selected((int) old('category_id', $p->category_id) === $c->id)>{{ $c->name }}</option>@endforeach</x-field>
                <fieldset><legend class="label">Collections</legend>@foreach($collections as $c)<label class="flex items-center gap-2 py-0.5 text-sm"><input type="checkbox" name="collection_ids[]" value="{{ $c->id }}" class="size-4 accent-[var(--ink)]" @checked(in_array($c->id, old('collection_ids', $p->collections->pluck('id')->all())))> {{ $c->name }}</label>@endforeach</fieldset></section>
            <section class="card space-y-4 p-6"><h2 class="font-display text-lg font-bold">Pricing</h2>
                <x-field name="price" type="number" step="0.01" label="Price (USD)" :value="$p->price" required />
                <x-field name="compare_at_price" type="number" step="0.01" label="Compare-at price" :value="$p->compare_at_price" help="Only set a compare-at price that was genuinely charged before." /></section>
        </aside>
    </form>
    @if($p->exists)@foreach($p->images as $img)<form id="del-{{ $img->id }}" method="POST" action="{{ route('admin.products.images.destroy', [$p, $img]) }}">@csrf @method('DELETE')</form>@endforeach @endif
</x-layouts.admin>
