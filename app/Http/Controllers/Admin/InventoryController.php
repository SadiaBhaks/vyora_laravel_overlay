<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryMovement;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $threshold = config('vyora.low_stock_threshold');
        $query = ProductVariant::with('product')->where('is_active', true)->orderBy('stock');
        if ($request->boolean('low')) {
            $query->where('stock', '<=', $threshold);
        }

        return view('admin.inventory.index', [
            'variants' => $query->paginate(25)->withQueryString(),
            'movements' => InventoryMovement::with(['variant.product', 'user'])->latest('id')->limit(12)->get(),
            'threshold' => $threshold,
            'low' => $request->boolean('low'),
        ]);
    }

    public function adjust(Request $request, ProductVariant $variant)
    {
        $data = $request->validate([
            'change' => ['required', 'integer', 'between:-100000,100000', 'not_in:0'],
            'note' => ['nullable', 'string', 'max:190'],
        ]);

        try {
            DB::transaction(function () use ($variant, $data, $request) {
                $locked = ProductVariant::whereKey($variant->id)->lockForUpdate()->firstOrFail();
                if ($locked->stock + $data['change'] < 0) {
                    throw new \DomainException('Stock cannot go below zero.');
                }
                InventoryMovement::apply($locked, (int) $data['change'], $data['change'] > 0 ? 'restock' : 'adjustment', null, $request->user()->id, $data['note'] ?? null);
            });
        } catch (\DomainException $e) {
            return back()->withErrors(['change' => $e->getMessage()]);
        }

        return back()->with('toast', 'Stock updated.');
    }
}
