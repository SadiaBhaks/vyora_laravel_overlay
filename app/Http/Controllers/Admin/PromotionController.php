<?php

namespace App\Http\Controllers\Admin;

use App\Models\Product;
use App\Models\Promotion;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PromotionController extends ResourceController
{
    protected string $model = Promotion::class;
    protected string $route = 'admin.promotions';
    protected string $title = 'Promotions';
    protected string $singular = 'Promotion';

    protected function columns(): array
    {
        return [
            'Name' => 'name',
            'Discount' => fn ($p) => $p->discount_percent.'%',
            'Products' => fn ($p) => $p->products()->count(),
            'Runs' => fn ($p) => $p->starts_at->format('M j H:i').' → '.$p->ends_at->format('M j, Y H:i'),
            'Status' => fn ($p) => $p->isRunning() ? 'Running' : ($p->ends_at->isPast() ? 'Ended' : ($p->is_active ? 'Scheduled' : 'Off')),
        ];
    }

    protected function fields(): array
    {
        return [
            ['name' => 'name', 'label' => 'Name', 'type' => 'text'],
            ['name' => 'slug', 'label' => 'Slug', 'type' => 'text', 'help' => 'Leave blank to generate.'],
            ['name' => 'description', 'label' => 'Description', 'type' => 'textarea'],
            ['name' => 'discount_percent', 'label' => 'Discount %', 'type' => 'number'],
            ['name' => 'starts_at', 'label' => 'Starts', 'type' => 'datetime-local'],
            ['name' => 'ends_at', 'label' => 'Ends', 'type' => 'datetime-local', 'help' => 'The countdown on the Deals page counts down to exactly this time.'],
            ['name' => 'is_active', 'label' => 'Enabled', 'type' => 'checkbox'],
            ['name' => 'product_ids', 'label' => 'Products', 'type' => 'multiselect', 'options' => fn () => Product::orderBy('name')->pluck('name', 'id')->all(),
                'value' => fn ($m) => $m->exists ? $m->products()->pluck('products.id')->all() : []],
        ];
    }

    protected function rules(?Model $m): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['nullable', 'alpha_dash', 'max:140', Rule::unique('promotions', 'slug')->ignore($m?->id)],
            'description' => ['nullable', 'string', 'max:1000'],
            'discount_percent' => ['required', 'integer', 'between:1,90'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'is_active' => ['nullable', 'boolean'],
            'product_ids' => ['nullable', 'array'],
            'product_ids.*' => ['integer', 'exists:products,id'],
        ];
    }

    protected function prepare(array $data, ?Model $m): array
    {
        $data['slug'] = $data['slug'] ?: Str::slug($data['name']).'-'.Str::lower(Str::random(4));
        $data['is_active'] = request()->boolean('is_active');
        unset($data['product_ids']);

        return $data;
    }

    protected function afterSave(Model $model, Request $request): void
    {
        $model->products()->sync($request->input('product_ids', []));
    }
}
