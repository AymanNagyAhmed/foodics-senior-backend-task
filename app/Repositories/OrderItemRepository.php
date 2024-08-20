<?php

namespace App\Repositories;

use App\Interfaces\OrderItemRepositoryInterface;
use App\Models\Order;
use App\Models\OrderItem;

class OrderItemRepository implements OrderItemRepositoryInterface
{
    /**
     * Create order items.
     *
     * @param array $items
     * @param Order $order
     * @return void
     */
    public function createOrderItems(array $items, Order $order): void
    {
        $orderItems = collect($items)->map(function ($item) {
            $orderItem = new OrderItem();
            $orderItem->product_id = $item['product_id'];
            $orderItem->quantity = $item['quantity'];
            $orderItem->price = $item['price'];

            return $orderItem;
        });

        $order->items()->saveMany($orderItems);
    }
}
