<?php

namespace App\Repositories\PaymentGateways;

use App\Interfaces\PaymentGatewayInterface;
use App\Models\Order;

class PaypalRepository implements PaymentGatewayInterface
{
    /**
     * The process payment method.
     *
     * @param float $totalAmount
     *
     * @return void
     */
    public function processPayment(float $totalAmount): void
    {
        // Process the payment for the order.
    }
}
