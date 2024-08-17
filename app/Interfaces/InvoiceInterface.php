<?php

namespace App\Interfaces;

use App\Models\Order;

interface InvoiceInterface
{
    /**
     * Send an invoice.
     *
     * @param Order $order
     *
     * @return void
     */
    public function sendInvoice(Order $order): void;
}
