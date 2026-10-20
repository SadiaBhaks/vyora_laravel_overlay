<?php

namespace App\Http\Controllers\Admin;

use App\Models\Collection;
use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CollectionController extends ResourceController
{
    protected string $model = Collection::class;
    protected string $route = 'admin.collections';
    protected string $title = 'Collections';
    protected string $singular = 'Collection';

    protected function columns(): array
    {
        return ['Name' => 'name', 'Products' => fn ($c) => $c->products()->count(), 'Featured' => fn ($c) => $c->is_featured ? 'Yes' : 'No', 'Active' => fn ($c) => $c->is_active ? 'Yes' : 'No'];
    }

    protected function fields(): array
    {
        return [
            ['name' => 'name', 'label' => 'Name', 'type' => 'text'],
            ['name' => 'slug', 'label' => 'Slug', 'type' => 'text', 'help' => 'Leave blank to generate from the name.'],
            ['name' => 'tagline', 'label' => 'Tagline', 'type' => 'text'],
            ['name' => 'description', 'label' => 'Editorial description', 'type' => 'textarea'],
            ['name' => 'sort_order', 'label' => 'Sort order', 'type' => 'number'],
            ['name' => 'is_featured', 'label' => 'Feature on homepage', 'type' => 'checkbox'],
            ['name' => 'is_active', 'label' => 'Visible in store', 'type' => 'checkbox'],
            ['name' => 'product_ids', 'label' => 'Products', 'type' => 'multiselect', 'options' => fn () => Product::orderBy('name')->pluck('name', 'id')->all(),
                'value' => fn ($m) => $m->exists ? $m->products()->pluck('products.id')->all() : []],
        ];
    }

    protected function rules(?Model $m): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['nullable', 'alpha_dash', 'max:140', Rule::unique('collections', 'slug')->ignore($m?->id)],
            'tagline' => ['nullable', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:4000'],
            'sort_order' => ['nullable', 'integer', 'between:0,999'],
            'is_featured' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'product_ids' => ['nullable', 'array'],
            'product_ids.*' => ['integer', 'exists:products,id'],
        ];
    }

    protected function prepare(array $data, ?Model $m): array
    {
        $data['slug'] = $data['slug'] ?: Str::slug($data['name']);
        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['is_featured'] = request()->boolean('is_featured');
        $data['is_active'] = request()->boolean('is_active');
        unset($data['product_ids']);

        return $data;
    }

    protected function afterSave(Model $model, Request $request): void
    {
        $sync = [];
        foreach (array_values($request->input('product_ids', [])) as $i => $id) {
            $sync[$id] = ['sort_order' => $i];
        }
        $model->products()->sync($sync);
    }
}
