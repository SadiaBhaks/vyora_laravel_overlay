<?php

namespace App\Http\Controllers\Admin;

use App\Models\Category;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CategoryController extends ResourceController
{
    protected string $model = Category::class;
    protected string $route = 'admin.categories';
    protected string $title = 'Categories';
    protected string $singular = 'Category';

    protected function columns(): array
    {
        return ['Name' => 'name', 'Slug' => 'slug', 'Products' => fn ($c) => $c->products()->count(), 'Active' => fn ($c) => $c->is_active ? 'Yes' : 'No'];
    }

    protected function fields(): array
    {
        return [
            ['name' => 'name', 'label' => 'Name', 'type' => 'text'],
            ['name' => 'slug', 'label' => 'Slug', 'type' => 'text', 'help' => 'Leave blank to generate from the name.'],
            ['name' => 'tagline', 'label' => 'Tagline', 'type' => 'text'],
            ['name' => 'description', 'label' => 'Description', 'type' => 'textarea'],
            ['name' => 'sort_order', 'label' => 'Sort order', 'type' => 'number'],
            ['name' => 'is_active', 'label' => 'Visible in store', 'type' => 'checkbox'],
        ];
    }

    protected function rules(?Model $m): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['nullable', 'alpha_dash', 'max:140', Rule::unique('categories', 'slug')->ignore($m?->id)],
            'tagline' => ['nullable', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:2000'],
            'sort_order' => ['nullable', 'integer', 'between:0,999'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    protected function prepare(array $data, ?Model $m): array
    {
        $data['slug'] = $data['slug'] ?: Str::slug($data['name']);
        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['is_active'] = request()->boolean('is_active');

        return $data;
    }

    protected function canDelete(Model $m): ?string
    {
        return $m->products()->withTrashed()->exists() ? 'This category still has products. Move or archive them first.' : null;
    }
}
