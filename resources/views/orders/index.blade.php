@extends('layouts.app')

@section('title', 'Orders Log & Invoices')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <div>
        <h3 class="fw-bold mb-1"><i class="bi bi-receipt-cutoff me-2 text-primary"></i>Orders Log & Invoices</h3>
        <p class="text-muted mb-0">Browse all customer transactions, total calculations, item breakdowns, and print invoices with server-side DataTables.</p>
    </div>
    <div>
        <a href="{{ route('pos.index') }}" class="btn btn-success shadow-sm">
            <i class="bi bi-plus-circle me-1"></i> New Counter Order
        </a>
    </div>
</div>

<!-- Orders DataTable -->
<div class="card card-custom shadow-sm p-4">
    <div class="table-responsive">
        <table class="table table-hover table-custom align-middle w-100" id="ordersTable">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Customer Name</th>
                    <th>Customer Email</th>
                    <th class="text-center">Items Count</th>
                    <th class="text-end">Subtotal</th>
                    <th class="text-end">Tax Total</th>
                    <th class="text-end">Grand Total</th>
                    <th>Date & Time</th>
                    <th class="text-end">Invoice</th>
                </tr>
            </thead>
            <tbody>
                <!-- Server-Side DataTables -->
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#ordersTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('orders.index') }}",
            columns: [
                { data: 'order_number', name: 'order_number' },
                { data: 'customer_name', name: 'customer.name' },
                { data: 'customer_email', name: 'customer.email' },
                { data: 'items_count', name: 'items_count', className: 'text-center', orderable: false, searchable: false },
                { data: 'subtotal', name: 'subtotal', className: 'text-end' },
                { data: 'tax_total', name: 'tax_total', className: 'text-end' },
                { data: 'grand_total', name: 'grand_total', className: 'text-end' },
                { data: 'created_at', name: 'created_at' },
                { data: 'actions', name: 'actions', className: 'text-end', orderable: false, searchable: false }
            ],
            order: [[7, 'desc']]
        });
    });
</script>
@endpush
