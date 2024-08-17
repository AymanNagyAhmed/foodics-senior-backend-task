<?php

namespace App\Interfaces;

use App\Models\Order;

/**
 * Interface PaymentGatewayInterface
 */
interface PaymentGatewayInterface
{
    /**
     * The process payment method.
     *
     * @param float  $totalAmount
     * @return void
     */
    public function processPayment(float $totalAmount): void;
}
