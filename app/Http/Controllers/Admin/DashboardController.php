<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Review;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __invoke()
    {
        // "Revenue" = recorded paid orders that were not cancelled. Demo-mode orders are reported separately.
        $paid = fn () => Order::where('payment_status', 'paid')->where('status', '!=', 'cancelled');
        $real = $paid()->where('is_demo', false);
        $demo = $paid()->where('is_demo', true);

        $realCount = (clone $real)->count();
        $realRevenue = (int) (clone $real)->sum('total_cents');

        $days = collect(range(29, 0))->map(fn ($i) => now()->subDays($i)->toDateString());
        $byDay = $paid()->where('placed_at', '>=', now()->subDays(29)->startOfDay())
            ->get(['placed_at', 'total_cents', 'is_demo'])
            ->groupBy(fn ($o) => $o->placed_at->toDateString());

        $series = fn (bool $isDemo) => $days->map(fn ($d) => round(($byDay[$d] ?? collect())->where('is_demo', $isDemo)->sum('total_cents') / 100, 2))->all();

        return view('admin.dashboard', [
            'stats' => [
                'revenue_cents' => $realRevenue,
                'demo_revenue_cents' => (int) (clone $demo)->sum('total_cents'),
                'orders' => Order::count(),
                'paid_orders' => $realCount,
                'aov_cents' => $realCount ? (int) round($realRevenue / $realCount) : 0,
                'customers' => User::where('role', 'customer')->count(),
                'products' => Product::count(),
                'pending_reviews' => Review::where('status', 'pending')->count(),
            ],
            'lowStock' => ProductVariant::with('product')->where('is_active', true)->where('stock', '<=', config('vyora.low_stock_threshold'))->orderBy('stock')->limit(8)->get(),
            'recentOrders' => Order::latest('placed_at')->limit(8)->get(),
            'chart' => ['labels' => $days->map(fn ($d) => \Carbon\Carbon::parse($d)->format('M j'))->all(), 'real' => $series(false), 'demo' => $series(true)],
            'topProducts' => DB::table('order_items')->join('orders', 'orders.id', '=', 'order_items.order_id')
                ->where('orders.payment_status', 'paid')->where('orders.status', '!=', 'cancelled')
                ->selectRaw('order_items.name, SUM(order_items.quantity) as units')->groupBy('order_items.name')->orderByDesc('units')->limit(5)->get(),
        ]);
    }
}
