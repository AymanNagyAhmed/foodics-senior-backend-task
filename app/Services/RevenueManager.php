<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Cache;

class RevenueManager
{
    /**
     * Calculate total revenue for all orders.
     *
     * @return float
     */
    public static function calculateTotalRevenue(): float
    {
        return Cache::remember('daily_total_revenue', now()->endOfDay(), function () {
            return Order::whereDate('created_at', today())
                ->with('items')
                ->get()
                ->sum(function ($order) {
                    return $order->items->sum(function ($item) {
                        return $item->quantity * $item->price;
                    });
                });
        });
    }

}
