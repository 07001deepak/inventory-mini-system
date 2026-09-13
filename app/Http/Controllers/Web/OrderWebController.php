<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class OrderWebController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Order::with(['customer', 'items'])->latest();

            return DataTables::of($query)
                ->editColumn('order_number', function ($order) {
                    return '<span class="fw-bold font-monospace text-primary">' . e($order->order_number) . '</span>';
                })
                ->addColumn('customer_name', function ($order) {
                    return '<div class="fw-semibold text-dark">' . e($order->customer?->name ?? 'Guest') . '</div>';
                })
                ->addColumn('customer_email', function ($order) {
                    return '<span class="text-muted small"><i class="bi bi-envelope me-1"></i>' . e($order->customer?->email ?? 'N/A') . '</span>';
                })
                ->addColumn('items_count', function ($order) {
                    return '<div class="text-center"><span class="badge bg-secondary-subtle text-secondary rounded-pill px-3">' . $order->items->count() . ' line items</span></div>';
                })
                ->editColumn('subtotal', function ($order) {
                    return '₹' . number_format((float) $order->subtotal, 2);
                })
                ->editColumn('tax_total', function ($order) {
                    return '₹' . number_format((float) $order->tax_total, 2);
                })
                ->editColumn('grand_total', function ($order) {
                    return '<div class="fw-bold text-dark fs-6">₹' . number_format((float) $order->grand_total, 2) . '</div>';
                })
                ->editColumn('created_at', function ($order) {
                    return $order->created_at ? $order->created_at->format('M d, Y H:i A') : '-';
                })
                ->addColumn('actions', function ($order) {
                    return '<div class="text-end">
                        <a href="' . route('orders.show', $order) . '" class="btn btn-sm btn-outline-primary"><i class="bi bi-file-earmark-text me-1"></i> View Receipt</a>
                    </div>';
                })
                ->rawColumns(['order_number', 'customer_name', 'customer_email', 'items_count', 'grand_total', 'actions'])
                ->make(true);
        }

        return view('orders.index');
    }

    public function show(Order $order)
    {
        $order->load(['customer', 'items']);
        return view('orders.show', compact('order'));
    }
}
