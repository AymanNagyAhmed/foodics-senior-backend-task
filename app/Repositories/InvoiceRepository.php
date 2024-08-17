<?php

namespace App\Repositories;

use App\Interfaces\InvoiceInterface;
use App\Models\Order;

class InvoiceRepository implements InvoiceInterface
{
    /**
     * Send an invoice.
     *
     * @param Order $order
     *
     * @return void
     */
    public function sendInvoice(Order $order): void
    {
        // Send invoice.
    }
}
