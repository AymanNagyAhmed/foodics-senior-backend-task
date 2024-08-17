<?php

namespace App\Repositories;

use App\Interfaces\NotificationInterface;
use App\Models\Order;

class NotificationRepository implements NotificationInterface
{
    /**
     * The notify method.
     * Notification to the customer.
     *
     * @param Order  $order
     * @return void
     */
    public function notifyCustomer(Order $order): void
    {
        // Notify the customer.
    }
}
