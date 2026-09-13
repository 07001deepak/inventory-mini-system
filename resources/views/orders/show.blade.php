@extends('layouts.app')

@section('title', 'Order Invoice - ' . $order->order_number)

@section('content')
<div class="container py-2" style="max-width: 800px;">
    <div class="d-flex justify-content-between align-items-center mb-3 d-print-none">
        <a href="{{ route('orders.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Orders Log
        </a>
        <button type="button" class="btn btn-primary shadow-sm" onclick="window.print()">
            <i class="bi bi-printer me-1"></i> Print Invoice
        </button>
    </div>

    <div class="card card-custom p-4 p-md-5 shadow-sm bg-white" id="printableArea">
        <div class="row align-items-center border-bottom pb-4 mb-4">
            <div class="col-md-6">
                <h3 class="fw-bold text-primary mb-1"><i class="bi bi-shop me-2"></i>RETAIL STORE INVOICE</h3>
                <p class="text-muted small mb-0">Order & Inventory Counter System</p>
            </div>
            <div class="col-md-6 text-md-end mt-3 mt-md-0">
                <h5 class="fw-bold mb-1 font-monospace">{{ $order->order_number }}</h5>
                <div class="badge bg-success-subtle text-success fs-6 border border-success-subtle mb-1"><i class="bi bi-check-circle me-1"></i>COMPLETED</div>
                <div class="text-muted small">Date: {{ $order->created_at?->format('F d, Y - h:i A') }}</div>
            </div>
        </div>

        <!-- Customer & Store Metadata -->
        <div class="row mb-4">
            <div class="col-md-6">
                <h6 class="fw-bold text-uppercase text-secondary small">Customer Information</h6>
                <div class="fw-bold fs-6">{{ $order->customer?->name }}</div>
                <div class="text-muted small"><i class="bi bi-envelope me-1"></i>{{ $order->customer?->email }}</div>
                @if($order->customer?->phone)
                    <div class="text-muted small"><i class="bi bi-telephone me-1"></i>{{ $order->customer?->phone }}</div>
                @endif
            </div>
            <div class="col-md-6 text-md-end mt-3 mt-md-0">
                <h6 class="fw-bold text-uppercase text-secondary small">Order Metadata</h6>
                <div class="small"><strong>UUID:</strong> <span class="font-monospace text-muted">{{ $order->id }}</span></div>
                <div class="small"><strong>Status:</strong> <span class="text-capitalize">{{ $order->status }}</span></div>
            </div>
        </div>

        <!-- Order Line Items -->
        <div class="table-responsive mb-4">
            <table class="table table-bordered align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Product Details</th>
                        <th class="text-center">Unit Price</th>
                        <th class="text-center">Tax Rate</th>
                        <th class="text-center">Qty</th>
                        <th class="text-end">Subtotal</th>
                        <th class="text-end">Line Tax</th>
                        <th class="text-end">Line Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <div class="fw-bold">{{ $item->product_name }}</div>
                                <span class="badge bg-light text-dark border font-monospace small">{{ $item->product_code }}</span>
                            </td>
                            <td class="text-center">₹{{ number_format($item->unit_price, 2) }}</td>
                            <td class="text-center"><span class="badge bg-secondary-subtle text-secondary">{{ number_format($item->tax_percentage, 2) }}%</span></td>
                            <td class="text-center fw-bold">{{ $item->quantity }}</td>
                            <td class="text-end">₹{{ number_format($item->line_subtotal, 2) }}</td>
                            <td class="text-end text-muted">₹{{ number_format($item->line_tax, 2) }}</td>
                            <td class="text-end fw-bold">₹{{ number_format($item->line_total, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Calculation Totals Summary -->
        <div class="row justify-content-end">
            <div class="col-md-5">
                <div class="bg-light p-3 rounded-3 border">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-secondary">Items Subtotal:</span>
                        <span class="fw-semibold">₹{{ number_format($order->subtotal, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-secondary">Tax Total:</span>
                        <span class="fw-semibold">₹{{ number_format($order->tax_total, 2) }}</span>
                    </div>
                    <hr class="my-2">
                    <div class="d-flex justify-content-between align-items-baseline">
                        <span class="fw-bold fs-5">Grand Total:</span>
                        <span class="fw-bold fs-4 text-primary">₹{{ number_format($order->grand_total, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-5 pt-4 border-top text-center text-muted small">
            <p class="mb-0">Thank you for shopping with us! This order confirmation has been queued & logged.</p>
        </div>
    </div>
</div>
@endsection
