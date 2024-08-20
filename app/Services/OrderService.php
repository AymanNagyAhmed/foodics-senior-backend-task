<?php

namespace App\Services;

use App\Repositories\InventoryManagerRepository;
use App\Repositories\OrderRepository;
use App\Repositories\PaymentGateways\PaypalRepository;
use App\Repositories\NotificationRepository;
use App\Models\Order;
use App\Repositories\InvoiceRepository;
use App\Repositories\OrderItemRepository;
use Illuminate\Support\Facades\DB;

class OrderService
{
    /**
     * OrderService constructor.
     *
     * @param OrderRepository $orderRepository
     * @param InventoryManagerRepository $inventoryManagerRepository
     * @param PaypalRepository $paymentGatewayRepository
     * @param NotificationRepository $notificationRepository
     * @param InvoiceRepository $invoiceRepository
     */
    public function __construct(
        private OrderRepository $orderRepository,
        private InventoryManagerRepository $inventoryManagerRepository,
        private PaypalRepository $paymentGatewayRepository,
        private NotificationRepository $notificationRepository,
        private InvoiceRepository $invoiceRepository,
        private OrderItemRepository $orderItemRepository
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

        $this->orderRepository->store($order);
        $this->inventoryManagerRepository->updateInventory($order);

        $this->paymentGatewayRepository->processPayment($order->getTotalAmount());

        $this->notificationRepository->notifyCustomer($order);
        $this->invoiceRepository->sendInvoice($order);
    }

    /**
     * Creates a new order.
     *
     * @param array<string, mixed> $orderData
     *
     * @return Order
     */
    public function createOrder(array $orderData): Order
    {
        return DB::transaction(function () use ($orderData) {
            $order = $this->orderRepository->createOrder($orderData);

            $this->orderItemRepository->createOrderItems($orderData['items'], $order);
            return $order->load('branch', 'items');
        });
    }
}
