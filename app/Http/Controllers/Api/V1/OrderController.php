<?php

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\InsufficientStockException;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OrderController extends Controller
{
    public function __construct(protected OrderService $orderService)
    {
    }

    public function index(): AnonymousResourceCollection
    {
        $orders = Order::with(['customer', 'items'])->latest()->get();
        return OrderResource::collection($orders);
    }

    public function store(StoreOrderRequest $request): JsonResponse
    {
        try {
            $order = $this->orderService->createOrder($request->validated());
            return sendResponse(new OrderResource($order), 'Order created successfully', 201);
        } catch (InsufficientStockException $e) {
            return sendError($e->getMessage(), [], 422);
        } catch (\InvalidArgumentException $e) {
            return sendError($e->getMessage(), [], 422);
        } catch (\Throwable $e) {
            return sendError('Failed to process order: ' . $e->getMessage(), [], 500);
        }
    }

    public function show(Order $order): JsonResponse
    {
        return sendResponse(new OrderResource($order->load(['customer', 'items'])), 'Order retrieved successfully');
    }
}
