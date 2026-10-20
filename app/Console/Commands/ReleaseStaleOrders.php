<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Console\Command;

class ReleaseStaleOrders extends Command
{
    protected $signature = 'vyora:release-stale-orders {--minutes=45}';

    protected $description = 'Cancel unpaid orders and return their reserved stock.';

    public function handle(OrderService $orders): int
    {
        $count = 0;
        Order::where('payment_status', 'pending')->where('status', 'pending')
            ->where('placed_at', '<', now()->subMinutes((int) $this->option('minutes')))
            ->each(function (Order $order) use ($orders, &$count) {
                $orders->markPaymentFailed($order, 'Payment not completed in time.');
                $count++;
            });
        $this->info("Released $count unpaid order(s).");

        return self::SUCCESS;
    }
}
