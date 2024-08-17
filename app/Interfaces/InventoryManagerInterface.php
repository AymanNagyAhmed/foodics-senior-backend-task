<?php

namespace App\Interfaces;
use App\Models\Order;

interface InventoryManagerInterface
{
    /**
     * Update the inventory.
     *
     * @param Order $order
     *
     * @return void
     */
    public function updateInventory(Order $order): void;
}
