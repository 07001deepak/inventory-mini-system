@extends('layouts.app')

@section('title', 'Customer Directory & Order History')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <div>
        <h3 class="fw-bold mb-1"><i class="bi bi-people me-2 text-primary"></i>Customer Directory</h3>
        <p class="text-muted mb-0">Manage customer records, contact info, and inspect customer order history with server-side DataTables.</p>
    </div>
    <div>
        <button type="button" class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#createCustomerModal">
            <i class="bi bi-person-plus me-1"></i> Add New Customer
        </button>
    </div>
</div>

<!-- Customers DataTable -->
<div class="card card-custom shadow-sm p-4">
    <div class="table-responsive">
        <table class="table table-hover table-custom align-middle w-100" id="customersTable">
            <thead>
                <tr>
                    <th>Customer Name</th>
                    <th>Email Address</th>
                    <th>Phone</th>
                    <th class="text-center">Total Orders</th>
                    <th>Joined Date</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <!-- Server-Side DataTables -->
            </tbody>
        </table>
    </div>
</div>

<!-- Create Customer Modal -->
<div class="modal fade" id="createCustomerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('customers.store') }}" class="modal-content border-0 shadow">
            @csrf
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-person-plus me-2"></i>Add New Customer</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Customer Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" required placeholder="e.g. Jane Smith">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Email Address <span class="text-danger">*</span></label>
                    <input type="email" name="email" class="form-control" required placeholder="jane@example.com">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Phone Number</label>
                    <input type="text" name="phone" class="form-control" placeholder="+1 (555) 019-2834">
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary fw-semibold"><i class="bi bi-save me-1"></i> Save Customer</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Customer Modal -->
<div class="modal fade" id="editCustomerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="editCustomerForm" method="POST" action="" class="modal-content border-0 shadow">
            @csrf
            @method('PUT')
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square me-2"></i>Edit Customer</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Customer Name <span class="text-danger">*</span></label>
                    <input type="text" id="edit_cust_name" name="name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Email Address <span class="text-danger">*</span></label>
                    <input type="email" id="edit_cust_email" name="email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Phone Number</label>
                    <input type="text" id="edit_cust_phone" name="phone" class="form-control">
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-dark fw-semibold"><i class="bi bi-check-lg me-1"></i> Update Customer</button>
            </div>
        </form>
    </div>
</div>

<!-- Customer History Modal -->
<div class="modal fade" id="customerHistoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-clock-history me-2"></i>Order History - <span id="historyCustomerName"></span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div id="historyLoading" class="text-center py-4">
                    <div class="spinner-border text-info" role="status"></div>
                    <p class="mt-2 text-muted small">Fetching order history via API...</p>
                </div>
                <div id="historyContent" class="d-none"></div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Customer Form -->
<form id="deleteCustomerForm" method="POST" action="" class="d-none">
    @csrf
    @method('DELETE')
</form>

@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#customersTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('customers.index') }}",
            columns: [
                { data: 'name', name: 'name' },
                { data: 'email', name: 'email' },
                { data: 'phone', name: 'phone' },
                { data: 'orders_count', name: 'orders_count', className: 'text-center', searchable: false },
                { data: 'created_at', name: 'created_at' },
                { data: 'actions', name: 'actions', className: 'text-end', orderable: false, searchable: false }
            ],
            order: [[0, 'asc']]
        });
    });

    function editCustomer(customer) {
        document.getElementById('editCustomerForm').action = `/customers/${customer.id}`;
        document.getElementById('edit_cust_name').value = customer.name;
        document.getElementById('edit_cust_email').value = customer.email;
        document.getElementById('edit_cust_phone').value = customer.phone || '';

        const modal = new bootstrap.Modal(document.getElementById('editCustomerModal'));
        modal.show();
    }

    function confirmDeleteCustomer(id, name) {
        if (confirm(`Are you sure you want to delete customer '${name}'?`)) {
            const form = document.getElementById('deleteCustomerForm');
            form.action = `/customers/${id}`;
            form.submit();
        }
    }

    async function viewCustomerHistory(email, name) {
        document.getElementById('historyCustomerName').textContent = name;
        const modal = new bootstrap.Modal(document.getElementById('customerHistoryModal'));
        modal.show();

        const loading = document.getElementById('historyLoading');
        const content = document.getElementById('historyContent');

        loading.classList.remove('d-none');
        content.classList.add('d-none');

        try {
            const res = await fetch(`/api/v1/customers/orders?email=${encodeURIComponent(email)}`);
            const json = await res.json();
            const orders = json.data || [];

            if (orders.length === 0) {
                content.innerHTML = `
                    <div class="alert alert-light text-center py-4 border">
                        <i class="bi bi-receipt-cutoff fs-2 text-muted d-block mb-2"></i>
                        No orders recorded yet for <strong>${email}</strong>.
                    </div>
                `;
            } else {
                let html = `
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered align-middle small">
                            <thead class="table-light">
                                <tr>
                                    <th>Order #</th>
                                    <th>Date</th>
                                    <th class="text-end">Subtotal</th>
                                    <th class="text-end">Tax</th>
                                    <th class="text-end">Grand Total</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                `;

                orders.forEach(ord => {
                    html += `
                        <tr>
                            <td class="fw-bold">${ord.order_number}</td>
                            <td>${new Date(ord.created_at).toLocaleDateString()}</td>
                            <td class="text-end">₹${ord.subtotal.toFixed(2)}</td>
                            <td class="text-end">₹${ord.tax_total.toFixed(2)}</td>
                            <td class="text-end fw-bold text-primary">₹${ord.grand_total.toFixed(2)}</td>
                            <td class="text-center">
                                <a href="/orders/${ord.id}" class="btn btn-xs btn-outline-primary py-0 px-2" target="_blank"><i class="bi bi-eye"></i> View</a>
                            </td>
                        </tr>
                    `;
                });

                html += `
                            </tbody>
                        </table>
                    </div>
                `;
                content.innerHTML = html;
            }
        } catch (err) {
            content.innerHTML = `<div class="alert alert-danger py-2">Error loading order history: ${err.message}</div>`;
        } finally {
            loading.classList.add('d-none');
            content.classList.remove('d-none');
        }
    }
</script>
@endpush
