<?php

namespace App\Repositories;

use App\Interfaces\InventoryManagerInterface;
use App\Models\Order;

class InventoryManagerRepository implements InventoryManagerInterface
{
    /**
     * Update inventory.
     *
     * @param Order $order
     *
     * @return void
     */
    public function updateInventory(Order $order): void
    {
        // Update inventory.
    }
}
