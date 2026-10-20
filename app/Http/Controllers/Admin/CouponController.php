<?php

namespace App\Http\Controllers\Admin;

use App\Models\Coupon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class CouponController extends ResourceController
{
    protected string $model = Coupon::class;
    protected string $route = 'admin.coupons';
    protected string $title = 'Coupons';
    protected string $singular = 'Coupon';
    protected array $searchable = ['code', 'description'];

    protected function columns(): array
    {
        return [
            'Code' => 'code',
            'Discount' => fn ($c) => $c->type === 'percent' ? rtrim(rtrim($c->value, '0'), '.').'%' : '$'.number_format($c->value, 2),
            'Used' => fn ($c) => $c->used_count.($c->max_uses ? ' / '.$c->max_uses : ''),
            'Valid until' => fn ($c) => $c->ends_at?->format('M j, Y H:i') ?? 'No expiry',
            'Status' => fn ($c) => $c->isCurrentlyValid() ? 'Live' : 'Inactive',
        ];
    }

    protected function fields(): array
    {
        return [
            ['name' => 'code', 'label' => 'Code', 'type' => 'text', 'help' => 'Case-insensitive. Example: WELCOME10'],
            ['name' => 'description', 'label' => 'Public description', 'type' => 'text', 'help' => 'If set, the coupon is listed on the Deals page.'],
            ['name' => 'type', 'label' => 'Type', 'type' => 'select', 'options' => ['percent' => 'Percentage off', 'fixed' => 'Fixed amount off']],
            ['name' => 'value', 'label' => 'Value', 'type' => 'number', 'step' => '0.01'],
            ['name' => 'min_subtotal', 'label' => 'Minimum subtotal', 'type' => 'number', 'step' => '0.01'],
            ['name' => 'max_uses', 'label' => 'Maximum uses', 'type' => 'number', 'help' => 'Blank for unlimited.'],
            ['name' => 'starts_at', 'label' => 'Starts', 'type' => 'datetime-local'],
            ['name' => 'ends_at', 'label' => 'Ends', 'type' => 'datetime-local'],
            ['name' => 'is_active', 'label' => 'Enabled', 'type' => 'checkbox'],
        ];
    }

    protected function rules(?Model $m): array
    {
        return [
            'code' => ['required', 'alpha_dash', 'max:40', Rule::unique('coupons', 'code')->ignore($m?->id)],
            'description' => ['nullable', 'string', 'max:190'],
            'type' => ['required', 'in:percent,fixed'],
            'value' => ['required', 'numeric', 'gt:0', request('type') === 'percent' ? 'max:100' : 'max:100000'],
            'min_subtotal' => ['nullable', 'numeric', 'min:0'],
            'max_uses' => ['nullable', 'integer', 'min:1'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after:starts_at'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    protected function prepare(array $data, ?Model $m): array
    {
        $data['code'] = strtoupper($data['code']);
        $data['min_subtotal'] = $data['min_subtotal'] ?? 0;
        $data['is_active'] = request()->boolean('is_active');

        return $data;
    }
}
