<?php

namespace App\Interfaces;

use App\Models\Order;

/**
 * Interface OrderRepositoryInterface
 */

interface OrderRepositoryInterface
{
    /**
     * Validates the order.
     *
     * @param Order $order
     *
     * @return bool
     */
    public function validateOrder(Order $order): bool;

    /**
     * Calculates the order details.
     *
     * @param Order $order
     *
     * @return Order
     */
    public function calculateOrderDetails(Order $order): Order;

    /**
     * Stores the order.
     *
     * @param Order $order
     *
     * @return void
     */
    public function storeOrder(Order $order): void;


}
