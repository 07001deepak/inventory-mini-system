<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Http\Resources\CustomerResource;
use App\Http\Resources\OrderResource;
use App\Models\Customer;
use App\Services\CustomerService;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CustomerController extends Controller
{
    public function __construct(
        protected CustomerService $customerService,
        protected OrderService $orderService
    ) {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $customers = $this->customerService->getAllCustomers($request->query('search'));
        return CustomerResource::collection($customers);
    }

    public function store(StoreCustomerRequest $request): JsonResponse
    {
        $customer = $this->customerService->createCustomer($request->validated());
        return sendResponse(new CustomerResource($customer), 'Customer created successfully', 201);
    }

    public function show(Customer $customer): JsonResponse
    {
        return sendResponse(new CustomerResource($customer->loadCount('orders')), 'Customer retrieved successfully');
    }

    public function update(UpdateCustomerRequest $request, Customer $customer): JsonResponse
    {
        $updatedCustomer = $this->customerService->updateCustomer($customer, $request->validated());
        return sendResponse(new CustomerResource($updatedCustomer), 'Customer updated successfully');
    }

    public function destroy(Customer $customer): JsonResponse
    {
        $this->customerService->deleteCustomer($customer);
        return sendResponse([], 'Customer deleted successfully');
    }

    public function orders(Request $request): AnonymousResourceCollection|JsonResponse
    {
        $email = $request->query('email');
        if (empty($email)) {
            return sendError('The email query parameter is required.', [], 422);
        }

        $orders = $this->orderService->getCustomerOrdersByEmail($email);
        return OrderResource::collection($orders);
    }
}
