<?php

namespace App\Http\Controllers\Admin;

use App\Models\Brand;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class BrandController extends ResourceController
{
    protected string $model = Brand::class;
    protected string $route = 'admin.brands';
    protected string $title = 'Brands';
    protected string $singular = 'Brand';

    protected function columns(): array
    {
        return ['Name' => 'name', 'Slug' => 'slug', 'Products' => fn ($b) => $b->products()->count()];
    }

    protected function fields(): array
    {
        return [
            ['name' => 'name', 'label' => 'Name', 'type' => 'text'],
            ['name' => 'slug', 'label' => 'Slug', 'type' => 'text', 'help' => 'Leave blank to generate from the name.'],
            ['name' => 'description', 'label' => 'Description', 'type' => 'textarea'],
        ];
    }

    protected function rules(?Model $m): array
    {
        return [
            'name' => ['required', 'string', 'max:120', Rule::unique('brands', 'name')->ignore($m?->id)],
            'slug' => ['nullable', 'alpha_dash', 'max:140', Rule::unique('brands', 'slug')->ignore($m?->id)],
            'description' => ['nullable', 'string', 'max:2000'],
        ];
    }

    protected function prepare(array $data, ?Model $m): array
    {
        $data['slug'] = $data['slug'] ?: Str::slug($data['name']);

        return $data;
    }

    protected function canDelete(Model $m): ?string
    {
        return $m->products()->withTrashed()->exists() ? 'This brand still has products.' : null;
    }
}
