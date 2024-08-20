<?php

namespace App\Interfaces;

use App\Models\Order;
use App\Models\OrderItem;

/**
 * Interface OrderItemRepositoryInterface
 */

interface OrderItemRepositoryInterface
{
    /**
     * Create order items.
     *
     * @param array $items
     * @param Order $order
     * @return void
     */
    public function createOrderItems(array $items, Order $order): void;


}
