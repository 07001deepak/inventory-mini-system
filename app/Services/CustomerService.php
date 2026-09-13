<?php

namespace App\Services;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Collection;

class CustomerService
{
    public function getAllCustomers(?string $search = null): Collection
    {
        $query = Customer::withCount('orders')->latest();

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        return $query->get();
    }

    public function createCustomer(array $data): Customer
    {
        $data['email'] = strtolower(trim($data['email']));
        return Customer::create($data);
    }

    public function updateCustomer(Customer $customer, array $data): Customer
    {
        if (isset($data['email'])) {
            $data['email'] = strtolower(trim($data['email']));
        }
        $customer->update($data);
        return $customer->fresh();
    }

    public function deleteCustomer(Customer $customer): bool
    {
        return $customer->delete();
    }
}
