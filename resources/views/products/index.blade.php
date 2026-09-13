@extends('layouts.app')

@section('title', 'Product Catalog & Inventory Management')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <div>
        <h3 class="fw-bold mb-1"><i class="bi bi-box-seam me-2 text-primary"></i>Product Catalog</h3>
        <p class="text-muted mb-0">Manage stock inventory, pricing, tax percentages, and low-stock alert thresholds with server-side DataTables.</p>
    </div>
    <div>
        <button type="button" class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#createProductModal">
            <i class="bi bi-plus-lg me-1"></i> Add New Product
        </button>
    </div>
</div>

<!-- Search & Filter Bar -->
<div class="card card-custom p-3 mb-4">
    <div class="row g-2 align-items-center">
        <div class="col-md-6">
            <label class="form-label small fw-semibold text-muted mb-1">Filter Inventory Status</label>
            <select id="filterSelect" class="form-select">
                <option value="">All Inventory Items</option>
                <option value="low_stock">⚠️ Low Stock Items Only</option>
            </select>
        </div>
    </div>
</div>

<!-- Products DataTable -->
<div class="card card-custom shadow-sm p-4">
    <div class="table-responsive">
        <table class="table table-hover table-custom align-middle w-100" id="productsTable">
            <thead>
                <tr>
                    <th>Product Name</th>
                    <th>SKU Code</th>
                    <th class="text-end">Unit Price</th>
                    <th class="text-center">Tax %</th>
                    <th class="text-center">Stock on Hand</th>
                    <th class="text-center">Threshold</th>
                    <th class="text-center">Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <!-- Server-Side DataTables -->
            </tbody>
        </table>
    </div>
</div>

<!-- Create Product Modal -->
<div class="modal fade" id="createProductModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('products.store') }}" class="modal-content border-0 shadow">
            @csrf
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-plus-circle me-2"></i>Add New Product</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Product Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" required placeholder="e.g. Wireless Ergonomic Mouse">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Product Code / SKU <span class="text-danger">*</span></label>
                    <input type="text" name="code" class="form-control" required placeholder="e.g. TECH-MOUSE-01">
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Unit Price (₹) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0" name="price_per_unit" class="form-control" required placeholder="99.99">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Tax Percentage (%) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0" max="100" name="tax_percentage" class="form-control" value="18.00" required>
                    </div>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Stock on Hand <span class="text-danger">*</span></label>
                        <input type="number" min="0" name="stock_on_hand" class="form-control" value="10" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Low Stock Threshold</label>
                        <input type="number" min="0" name="low_stock_threshold" class="form-control" value="5">
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary fw-semibold"><i class="bi bi-save me-1"></i> Save Product</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Product Modal -->
<div class="modal fade" id="editProductModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="editProductForm" method="POST" action="" class="modal-content border-0 shadow">
            @csrf
            @method('PUT')
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square me-2"></i>Edit Product</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Product Name <span class="text-danger">*</span></label>
                    <input type="text" id="edit_name" name="name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Product Code / SKU <span class="text-danger">*</span></label>
                    <input type="text" id="edit_code" name="code" class="form-control" required>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Unit Price (₹) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0" id="edit_price_per_unit" name="price_per_unit" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Tax Percentage (%) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0" max="100" id="edit_tax_percentage" name="tax_percentage" class="form-control" required>
                    </div>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Stock on Hand <span class="text-danger">*</span></label>
                        <input type="number" min="0" id="edit_stock_on_hand" name="stock_on_hand" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Low Stock Threshold</label>
                        <input type="number" min="0" id="edit_low_stock_threshold" name="low_stock_threshold" class="form-control">
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-dark fw-semibold"><i class="bi bi-check-lg me-1"></i> Update Product</button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Product Form -->
<form id="deleteProductForm" method="POST" action="" class="d-none">
    @csrf
    @method('DELETE')
</form>

@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        const table = $('#productsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('products.index') }}",
                data: function(d) {
                    d.filter = $('#filterSelect').val();
                }
            },
            columns: [
                { data: 'name', name: 'name' },
                { data: 'code', name: 'code' },
                { data: 'price_per_unit', name: 'price_per_unit', className: 'text-end font-weight-bold' },
                { data: 'tax_percentage', name: 'tax_percentage', className: 'text-center' },
                { data: 'stock_on_hand', name: 'stock_on_hand', className: 'text-center' },
                { data: 'low_stock_threshold', name: 'low_stock_threshold', className: 'text-center' },
                { data: 'status', name: 'status', className: 'text-center', orderable: false, searchable: false },
                { data: 'actions', name: 'actions', className: 'text-end', orderable: false, searchable: false }
            ],
            order: [[0, 'asc']]
        });

        $('#filterSelect').on('change', function() {
            table.ajax.reload();
        });
    });

    function editProduct(product) {
        document.getElementById('editProductForm').action = `/products/${product.id}`;
        document.getElementById('edit_name').value = product.name;
        document.getElementById('edit_code').value = product.code;
        document.getElementById('edit_price_per_unit').value = product.price_per_unit;
        document.getElementById('edit_tax_percentage').value = product.tax_percentage;
        document.getElementById('edit_stock_on_hand').value = product.stock_on_hand;
        document.getElementById('edit_low_stock_threshold').value = product.low_stock_threshold;

        const modal = new bootstrap.Modal(document.getElementById('editProductModal'));
        modal.show();
    }

    function confirmDeleteProduct(id, name) {
        if (confirm(`Are you sure you want to delete product '${name}'?`)) {
            const form = document.getElementById('deleteProductForm');
            form.action = `/products/${id}`;
            form.submit();
        }
    }
</script>
@endpush
