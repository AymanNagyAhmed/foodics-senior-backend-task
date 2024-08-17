<?php

namespace App\Interfaces;

use App\Models\Order;

/**
 * Interface CustomerNotifierInterface
 */
interface NotificationInterface
{
    /**
     * The notify method.
     * Notification to the customer.
     * @param Order  $order
     * @return void
     */
    public function notifyCustomer(Order $order): void;
}
