<?php

namespace App\Services;

use App\Interfaces\InventoryManagerInterface;
use App\Interfaces\OrderRepositoryInterface;
use App\Repositories\PaymentGateways\PaypalRepository;
use App\Repositories\NotificationRepository;
use App\Models\Order;
use App\Repositories\InvoiceRepository;

class OrderService
{
    /**
     * OrderService constructor.
     *
     * @param OrderRepositoryInterface $orderRepository
     * @param InventoryManagerInterface $inventoryManagerRepository
     * @param PaypalRepository $paymentGatewayRepository
     * @param NotificationRepository $notificationRepository
     */
    public function __construct(
        private OrderRepositoryInterface $orderRepository,
        private InventoryManagerInterface $inventoryManagerRepository,
        private PaypalRepository $paymentGatewayRepository,
        private NotificationRepository $notificationRepository,
        private InvoiceRepository $invoiceRepository
    ) {}

    /**
     * Validates the order, calculates order details, processes the order and payment,
     * notifies the customer, and sends an invoice.
     *
     * @param Order $order
     *
     * @return void
     */
    public function placeOrder(Order $order): void
    {

        $this->orderRepository->validateOrder($order);
        $order = $this->orderRepository->calculateOrderDetails($order);

        $this->orderRepository->storeOrder($order);
        $this->inventoryManagerRepository->updateInventory($order);

        $this->paymentGatewayRepository->processPayment($order->getTotalAmount());

        $this->notificationRepository->notifyCustomer($order);
        $this->invoiceRepository->sendInvoice($order);
    }
}
