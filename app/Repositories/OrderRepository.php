<?php

namespace App\Repositories;

use App\Interfaces\OrderRepositoryInterface;
use App\Models\Order;

class OrderRepository implements OrderRepositoryInterface
{
    /**
     * The validate order method.
     *
     * @param Order  $order
     * @return bool
     */
    public function validateOrder(Order $order): bool
    {
        // Validate the order.
        return true;
    }

    /**
     * The calculate order details method.
     *
     * @param Order  $order
     * @return Order
     */
    public function calculateOrderDetails(Order $order): Order
    {
        // Calculate the order details.
        return $order;
    }

    /**
     * The store order method.
     *
     * @param Order  $order
     * @return void
     */
    public function store(Order $order): void
    {
        // Store the order.
    }

    /**
     * The create order method.
     *
     * @param array  $orderData
     * @return Order
     */
    public function createOrder(array $orderData): Order
    {
        return Order::create([
            'name' => $orderData['name'],
            'branch_id' => $orderData['branch_id'],
        ]);
    }
}
