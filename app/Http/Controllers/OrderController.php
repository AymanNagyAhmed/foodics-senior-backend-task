<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\OrderService;
use App\Helpers\ResponseHelper;
use Illuminate\Http\JsonResponse;

class OrderController extends Controller
{
    /**
     * OrderController constructor.
     *
     * @param OrderService $service
     */
    public function __construct(private OrderService $service) {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }


    /**
     * Store a new order.
     *
     * @param StoreOrderRequest $request
     *
     * @return JsonResponse
     */
    public function store(StoreOrderRequest $request): JsonResponse
    {
        try {
            $order = $this->service->createOrder($request->validated());
            return ResponseHelper::successResponse(["order" => OrderResource::make($order)], "Order created successfully", 201);
        } catch (\Exception $e) {
            return ResponseHelper::failResponse("Order creation failed.", [$e->getMessage()], $e->getCode() ?? 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Order $order)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateOrderRequest $request, Order $order)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Order $order)
    {
        //
    }
}
