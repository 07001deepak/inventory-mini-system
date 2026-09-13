<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Models\Customer;
use App\Services\CustomerService;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class CustomerWebController extends Controller
{
    public function __construct(protected CustomerService $customerService)
    {
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Customer::withCount('orders')->latest();

            return DataTables::of($query)
                ->editColumn('name', function ($customer) {
                    return '<div><div class="fw-bold text-dark">' . e($customer->name) . '</div><small class="text-muted">UUID: ' . substr($customer->id, 0, 8) . '...</small></div>';
                })
                ->editColumn('email', function ($customer) {
                    return '<a href="mailto:' . e($customer->email) . '" class="text-decoration-none fw-semibold"><i class="bi bi-envelope me-1"></i>' . e($customer->email) . '</a>';
                })
                ->editColumn('phone', function ($customer) {
                    return $customer->phone ? '<span><i class="bi bi-telephone me-1 text-muted"></i>' . e($customer->phone) . '</span>' : '<span class="text-muted small">N/A</span>';
                })
                ->addColumn('orders_count', function ($customer) {
                    return '<div class="text-center"><span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 fs-6"><i class="bi bi-receipt me-1"></i>' . $customer->orders_count . ' orders</span></div>';
                })
                ->editColumn('created_at', function ($customer) {
                    return $customer->created_at ? $customer->created_at->format('M d, Y') : '-';
                })
                ->addColumn('actions', function ($customer) {
                    $json = htmlspecialchars(json_encode($customer), ENT_QUOTES, 'UTF-8');
                    return '<div class="text-end">
                        <button type="button" class="btn btn-sm btn-outline-info me-1" onclick="viewCustomerHistory(\'' . e($customer->email) . '\', \'' . addslashes($customer->name) . '\')"><i class="bi bi-clock-history me-1"></i> Orders</button>
                        <button type="button" class="btn btn-sm btn-outline-primary me-1" onclick="editCustomer(' . $json . ')"><i class="bi bi-pencil"></i></button>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDeleteCustomer(\'' . $customer->id . '\', \'' . addslashes($customer->name) . '\')"><i class="bi bi-trash"></i></button>
                    </div>';
                })
                ->rawColumns(['name', 'email', 'phone', 'orders_count', 'actions'])
                ->make(true);
        }

        return view('customers.index');
    }

    public function store(StoreCustomerRequest $request)
    {
        $this->customerService->createCustomer($request->validated());

        return redirect()->route('customers.index')
            ->with('success', 'Customer created successfully.');
    }

    public function update(UpdateCustomerRequest $request, Customer $customer)
    {
        $this->customerService->updateCustomer($customer, $request->validated());

        return redirect()->route('customers.index')
            ->with('success', 'Customer updated successfully.');
    }

    public function destroy(Customer $customer)
    {
        $this->customerService->deleteCustomer($customer);

        return redirect()->route('customers.index')
            ->with('success', 'Customer deleted successfully.');
    }
}
