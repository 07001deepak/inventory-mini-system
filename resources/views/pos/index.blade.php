@extends('layouts.app')

@section('title', 'Point of Sale Counter')

@section('content')
<div class="row g-4">
    <!-- Left Column: Product Selection Catalog -->
    <div class="col-lg-7 col-xl-8">
        <div class="card card-custom p-3 mb-3">
            <div class="row g-2 align-items-center">
                <div class="col-md-7">
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" id="productSearchInput" class="form-control" placeholder="Search product by name or SKU code...">
                    </div>
                </div>
                <div class="col-md-5 text-md-end">
                    <span class="text-muted small me-2">Available Products: {{ $products->count() }}</span>
                </div>
            </div>
        </div>

        <div class="row g-3" id="productsGrid">
            @forelse($products as $product)
                <div class="col-md-6 col-xl-4 product-card-wrapper" data-name="{{ strtolower($product->name) }}" data-code="{{ strtolower($product->code) }}">
                    <div class="card card-custom h-100 p-3 position-relative border-0 shadow-sm hover-shadow cursor-pointer product-card"
                         onclick="addToCart('{{ $product->id }}', '{{ addslashes($product->name) }}', '{{ $product->code }}', {{ $product->price_per_unit }}, {{ $product->tax_percentage }}, {{ $product->stock_on_hand }})">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="badge bg-light text-dark fw-normal border"><i class="bi bi-barcode me-1"></i>{{ $product->code }}</span>
                            @if($product->isLowStock())
                                <span class="badge bg-warning text-dark"><i class="bi bi-exclamation-triangle me-1"></i>Low Stock ({{ $product->stock_on_hand }})</span>
                            @else
                                <span class="badge bg-success-subtle text-success fw-semibold"><i class="bi bi-box me-1"></i>Stock: {{ $product->stock_on_hand }}</span>
                            @endif
                        </div>
                        <h6 class="fw-bold mb-1 text-truncate">{{ $product->name }}</h6>
                        <div class="d-flex justify-content-between align-items-baseline mt-auto pt-2">
                            <div>
                                <span class="fs-5 fw-bold text-primary">₹{{ number_format($product->price_per_unit, 2) }}</span>
                                <span class="text-muted small">/ unit</span>
                            </div>
                            <span class="badge bg-secondary-subtle text-secondary small">+{{ $product->tax_percentage }}% Tax</span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="card card-custom p-5 text-center text-muted">
                        <i class="bi bi-box-seam display-4 mb-2"></i>
                        <p class="mb-0">No active products with stock available. Please add products in the Product Catalog.</p>
                    </div>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Right Column: Cart & Checkout Panel -->
    <div class="col-lg-5 col-xl-4">
        <div class="card card-custom p-3 shadow-sm sticky-top" style="top: 80px;">
            <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                <h5 class="fw-bold mb-0"><i class="bi bi-cart3 me-2 text-primary"></i>Current Order</h5>
                <button type="button" class="btn btn-outline-danger btn-sm rounded-pill" onclick="clearCart()">
                    <i class="bi bi-trash me-1"></i>Clear
                </button>
            </div>

            <!-- Customer Details -->
            <div class="mb-3 bg-light p-3 rounded-3 border">
                <h6 class="fw-semibold mb-2 text-secondary small text-uppercase"><i class="bi bi-person me-1"></i>Customer Details</h6>
                <div class="mb-2">
                    <select id="existingCustomerSelect" class="form-select form-select-sm mb-2" onchange="onCustomerSelectChange(this)">
                        <option value="">-- Select Existing Customer or New --</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}" data-name="{{ $c->name }}" data-email="{{ $c->email }}">{{ $c->name }} ({{ $c->email }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="row g-2">
                    <div class="col-12">
                        <input type="text" id="customerName" class="form-control form-control-sm" placeholder="Customer Name *" required>
                    </div>
                    <div class="col-12">
                        <input type="email" id="customerEmail" class="form-control form-control-sm" placeholder="Customer Email *" required>
                    </div>
                </div>
            </div>

            <!-- Cart Line Items Table -->
            <div class="cart-items-container mb-3" style="max-height: 280px; overflow-y: auto;">
                <table class="table table-sm align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Product</th>
                            <th class="text-center" style="width: 80px;">Qty</th>
                            <th class="text-end">Total</th>
                            <th style="width: 30px;"></th>
                        </tr>
                    </thead>
                    <tbody id="cartTableBody">
                        <!-- JS populated -->
                    </tbody>
                </table>
                <div id="emptyCartMessage" class="text-center py-4 text-muted">
                    <i class="bi bi-cart-x fs-3 d-block mb-1"></i>
                    <span>Cart is empty. Click a product to add.</span>
                </div>
            </div>

            <!-- Calculation Summary -->
            <div class="bg-light p-3 rounded-3 mb-3 border">
                <div class="d-flex justify-content-between mb-1 text-secondary">
                    <span>Items Subtotal:</span>
                    <span id="summarySubtotal" class="fw-semibold">₹0.00</span>
                </div>
                <div class="d-flex justify-content-between mb-2 text-secondary">
                    <span>Tax Total:</span>
                    <span id="summaryTax" class="fw-semibold">₹0.00</span>
                </div>
                <hr class="my-2">
                <div class="d-flex justify-content-between align-items-baseline">
                    <span class="fw-bold fs-5">Grand Total:</span>
                    <span id="summaryGrandTotal" class="fw-bold fs-4 text-primary">₹0.00</span>
                </div>
            </div>

            <!-- Error Banner -->
            <div id="checkoutError" class="alert alert-danger d-none py-2 mb-3 small"></div>

            <!-- Submit Order Button -->
            <button type="button" id="btnPlaceOrder" class="btn btn-primary w-100 py-2 fw-bold rounded-3 shadow-sm" onclick="submitOrder()">
                <i class="bi bi-check2-circle me-1"></i> Complete Order
            </button>
        </div>
    </div>
</div>

<!-- Receipt Confirmation Modal -->
<div class="modal fade" id="receiptModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-check-circle-fill me-2"></i>Order Completed Successfully!</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4" id="receiptModalContent">
                <!-- JS Populated -->
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="window.print()"><i class="bi bi-printer me-1"></i>Print Receipt</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let cart = {};

    $(document).ready(function() {
        $('#existingCustomerSelect').select2({
            theme: 'bootstrap-5',
            placeholder: '-- Search & Select Customer --',
            allowClear: true,
            width: '100%'
        }).on('change', function() {
            const selectedOpt = $(this).find(':selected');
            if ($(this).val()) {
                $('#customerName').val(selectedOpt.data('name'));
                $('#customerEmail').val(selectedOpt.data('email'));
            }
        });
    });

    function addToCart(id, name, code, price, tax, maxStock) {
        if (cart[id]) {
            if (cart[id].qty + 1 > maxStock) {
                alert(`Cannot add more than available stock (${maxStock} units).`);
                return;
            }
            cart[id].qty++;
        } else {
            if (maxStock <= 0) {
                alert(`Product is out of stock!`);
                return;
            }
            cart[id] = {
                id: id,
                name: name,
                code: code,
                price: parseFloat(price),
                tax: parseFloat(tax),
                maxStock: parseInt(maxStock),
                qty: 1
            };
        }
        renderCart();
    }

    function updateCartQty(id, newQty) {
        newQty = parseInt(newQty);
        if (isNaN(newQty) || newQty <= 0) {
            delete cart[id];
        } else {
            if (newQty > cart[id].maxStock) {
                alert(`Cannot exceed stock on hand (${cart[id].maxStock}).`);
                newQty = cart[id].maxStock;
            }
            cart[id].qty = newQty;
        }
        renderCart();
    }

    function removeFromCart(id) {
        delete cart[id];
        renderCart();
    }

    function clearCart() {
        cart = {};
        renderCart();
    }

    function renderCart() {
        const tbody = document.getElementById('cartTableBody');
        const emptyMsg = document.getElementById('emptyCartMessage');
        tbody.innerHTML = '';

        const keys = Object.keys(cart);
        if (keys.length === 0) {
            emptyMsg.classList.remove('d-none');
        } else {
            emptyMsg.classList.add('d-none');
        }

        let subtotal = 0;
        let taxTotal = 0;

        keys.forEach(id => {
            const item = cart[id];
            const lineSubtotal = item.qty * item.price;
            const lineTax = lineSubtotal * (item.tax / 100);
            const lineTotal = lineSubtotal + lineTax;

            subtotal += lineSubtotal;
            taxTotal += lineTax;

            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>
                    <div class="fw-semibold text-truncate" style="max-width: 140px;" title="${item.name}">${item.name}</div>
                    <div class="text-muted small">₹${item.price.toFixed(2)} + ${item.tax}% tax</div>
                </td>
                <td class="text-center">
                    <input type="number" min="1" max="${item.maxStock}" class="form-control form-control-sm text-center px-1" value="${item.qty}" onchange="updateCartQty('${id}', this.value)">
                </td>
                <td class="text-end fw-semibold">₹${lineTotal.toFixed(2)}</td>
                <td class="text-end">
                    <button type="button" class="btn btn-link text-danger p-0 border-0" onclick="removeFromCart('${id}')"><i class="bi bi-x-circle"></i></button>
                </td>
            `;
            tbody.appendChild(tr);
        });

        const grandTotal = subtotal + taxTotal;

        document.getElementById('summarySubtotal').textContent = `₹${subtotal.toFixed(2)}`;
        document.getElementById('summaryTax').textContent = `₹${taxTotal.toFixed(2)}`;
        document.getElementById('summaryGrandTotal').textContent = `₹${grandTotal.toFixed(2)}`;
    }

    function onCustomerSelectChange(selectElem) {
        const selectedOpt = selectElem.options[selectElem.selectedIndex];
        if (selectElem.value) {
            document.getElementById('customerName').value = selectedOpt.getAttribute('data-name');
            document.getElementById('customerEmail').value = selectedOpt.getAttribute('data-email');
        }
    }

    // Live Product Filter
    document.getElementById('productSearchInput').addEventListener('input', function(e) {
        const query = e.target.value.toLowerCase().trim();
        const cards = document.querySelectorAll('.product-card-wrapper');
        cards.forEach(card => {
            const name = card.getAttribute('data-name');
            const code = card.getAttribute('data-code');
            if (name.includes(query) || code.includes(query)) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    });

    async function submitOrder() {
        const errorDiv = document.getElementById('checkoutError');
        errorDiv.classList.add('d-none');

        const customerName = document.getElementById('customerName').value.trim();
        const customerEmail = document.getElementById('customerEmail').value.trim();

        if (!customerName || !customerEmail) {
            errorDiv.textContent = 'Please provide customer name and email address.';
            errorDiv.classList.remove('d-none');
            return;
        }

        const items = Object.values(cart).map(i => ({
            product_id: i.id,
            quantity: i.qty
        }));

        if (items.length === 0) {
            errorDiv.textContent = 'Please add at least one product to the cart.';
            errorDiv.classList.remove('d-none');
            return;
        }

        const btn = document.getElementById('btnPlaceOrder');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';

        try {
            const response = await fetch('/api/v1/orders', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    customer_name: customerName,
                    customer_email: customerEmail,
                    items: items
                })
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'Error processing order.');
            }

            // Success Order Modal
            showReceiptModal(data.data);
            clearCart();
            document.getElementById('customerName').value = '';
            document.getElementById('customerEmail').value = '';
            document.getElementById('existingCustomerSelect').value = '';
            
            // Reload page after modal close to reflect updated stock counts
            document.getElementById('receiptModal').addEventListener('hidden.bs.modal', function () {
                window.location.reload();
            });

        } catch (err) {
            errorDiv.textContent = err.message;
            errorDiv.classList.remove('d-none');
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check2-circle me-1"></i> Complete Order';
        }
    }

    function showReceiptModal(order) {
        let itemsHtml = order.items.map(item => `
            <tr>
                <td>${item.product_name} <small class="text-muted">(${item.product_code})</small></td>
                <td class="text-center">${item.quantity}</td>
                <td class="text-end">₹${item.unit_price.toFixed(2)}</td>
                <td class="text-end">₹${item.line_total.toFixed(2)}</td>
            </tr>
        `).join('');

        document.getElementById('receiptModalContent').innerHTML = `
            <div class="text-center mb-3">
                <h4 class="fw-bold mb-0">RETAIL STORE RECEIPT</h4>
                <div class="text-muted small">Order #: <strong>${order.order_number}</strong></div>
                <div class="text-muted small">Date: ${new Date(order.created_at).toLocaleString()}</div>
            </div>
            <div class="border-top border-bottom py-2 mb-3">
                <div class="row small">
                    <div class="col-6"><strong>Customer:</strong> ${order.customer.name}</div>
                    <div class="col-6 text-end"><strong>Email:</strong> ${order.customer.email}</div>
                </div>
            </div>
            <table class="table table-sm align-middle small mb-3">
                <thead>
                    <tr class="table-light">
                        <th>Item</th>
                        <th class="text-center">Qty</th>
                        <th class="text-end">Price</th>
                        <th class="text-end">Total</th>
                    </tr>
                </thead>
                <tbody>
                    ${itemsHtml}
                </tbody>
            </table>
            <div class="bg-light p-3 rounded border">
                <div class="d-flex justify-content-between small mb-1">
                    <span>Subtotal:</span>
                    <span>₹${order.subtotal.toFixed(2)}</span>
                </div>
                <div class="d-flex justify-content-between small mb-2">
                    <span>Tax Total:</span>
                    <span>₹${order.tax_total.toFixed(2)}</span>
                </div>
                <div class="d-flex justify-content-between fw-bold fs-6 pt-2 border-top">
                    <span>Grand Total:</span>
                    <span class="text-primary">₹${order.grand_total.toFixed(2)}</span>
                </div>
            </div>
            <div class="alert alert-info py-2 mt-3 small text-center mb-0">
                <i class="bi bi-envelope-check me-1"></i> Queued confirmation email dispatched.
            </div>
        `;

        const modal = new bootstrap.Modal(document.getElementById('receiptModal'));
        modal.show();
    }
</script>
@endpush
