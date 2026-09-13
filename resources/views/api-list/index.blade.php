@extends('layouts.app')

@section('title', 'API Documentation & Interactive Postman Tester')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <div>
        <h3 class="fw-bold mb-1"><i class="bi bi-code-slash me-2 text-primary"></i>API List & Interactive Postman Tester</h3>
        <p class="text-muted mb-0">Browse and test all API endpoints defined in the assignment requirements directly from your browser.</p>
    </div>
    <div>
        <span class="badge bg-primary fs-6 px-3 py-2 shadow-sm"><i class="bi bi-lightning-charge-fill me-1"></i>Live REST API v1.0</span>
    </div>
</div>

<div class="row g-4">
    <!-- Left Column: API Endpoints List Navigation -->
    <div class="col-lg-4 col-xl-3">
        <div class="card card-custom shadow-sm overflow-hidden sticky-top" style="top: 80px;">
            <div class="card-header bg-white py-3 fw-bold border-bottom">
                <i class="bi bi-list-nested me-2 text-primary"></i>API Endpoints List
            </div>
            <div class="list-group list-group-flush" id="apiListGroup">
                <!-- Orders Group -->
                <div class="list-group-item bg-light text-uppercase fs-7 fw-bold text-muted py-2 px-3">
                    <i class="bi bi-cart-check me-1"></i> 1. Orders API
                </div>
                <button type="button" class="list-group-item list-group-item-action api-item active p-3" onclick="selectEndpoint('create_order')">
                    <div class="d-flex align-items-center mb-1">
                        <span class="badge bg-success me-2 px-2">POST</span>
                        <span class="fw-bold font-monospace small">/api/v1/orders</span>
                    </div>
                    <small class="text-muted d-block">Create Order & Deduct Stock <span class="badge bg-warning text-dark ms-1">Req #2</span></small>
                </button>

                <!-- Customer Order History Group -->
                <div class="list-group-item bg-light text-uppercase fs-7 fw-bold text-muted py-2 px-3">
                    <i class="bi bi-people me-1"></i> 2. Customer Order History API
                </div>
                <button type="button" class="list-group-item list-group-item-action api-item p-3" onclick="selectEndpoint('customer_orders')">
                    <div class="d-flex align-items-center mb-1">
                        <span class="badge bg-primary me-2 px-2">GET</span>
                        <span class="fw-bold font-monospace small">/api/v1/customers/orders</span>
                    </div>
                    <small class="text-muted d-block">Order History by Email <span class="badge bg-warning text-dark ms-1">Req #3</span></small>
                </button>

                <!-- Products Low Stock Group -->
                <div class="list-group-item bg-light text-uppercase fs-7 fw-bold text-muted py-2 px-3">
                    <i class="bi bi-box-seam me-1"></i> 3. Inventory & Low Stock API
                </div>
                <button type="button" class="list-group-item list-group-item-action api-item p-3" onclick="selectEndpoint('low_stock')">
                    <div class="d-flex align-items-center mb-1">
                        <span class="badge bg-primary me-2 px-2">GET</span>
                        <span class="fw-bold font-monospace small">/api/v1/products/low-stock</span>
                    </div>
                    <small class="text-muted d-block">Products Below Low-Stock Threshold <span class="badge bg-warning text-dark ms-1">Req #4</span></small>
                </button>
                <button type="button" class="list-group-item list-group-item-action api-item p-3" onclick="selectEndpoint('list_products')">
                    <div class="d-flex align-items-center mb-1">
                        <span class="badge bg-primary me-2 px-2">GET</span>
                        <span class="fw-bold font-monospace small">/api/v1/products</span>
                    </div>
                    <small class="text-muted d-block">List All Products</small>
                </button>
                <button type="button" class="list-group-item list-group-item-action api-item p-3" onclick="selectEndpoint('create_product')">
                    <div class="d-flex align-items-center mb-1">
                        <span class="badge bg-success me-2 px-2">POST</span>
                        <span class="fw-bold font-monospace small">/api/v1/products</span>
                    </div>
                    <small class="text-muted d-block">Create Product</small>
                </button>
                <button type="button" class="list-group-item list-group-item-action api-item p-3" onclick="selectEndpoint('update_product')">
                    <div class="d-flex align-items-center mb-1">
                        <span class="badge bg-warning text-dark me-2 px-2">PUT</span>
                        <span class="fw-bold font-monospace small">/api/v1/products/{id}</span>
                    </div>
                    <small class="text-muted d-block">Update Product</small>
                </button>
                <button type="button" class="list-group-item list-group-item-action api-item p-3" onclick="selectEndpoint('delete_product')">
                    <div class="d-flex align-items-center mb-1">
                        <span class="badge bg-danger me-2 px-2">DELETE</span>
                        <span class="fw-bold font-monospace small">/api/v1/products/{id}</span>
                    </div>
                    <small class="text-muted d-block">Delete Product</small>
                </button>

                <!-- Customer CRUD Group -->
                <div class="list-group-item bg-light text-uppercase fs-7 fw-bold text-muted py-2 px-3">
                    <i class="bi bi-person-lines-fill me-1"></i> 4. Customer CRUD API
                </div>
                <button type="button" class="list-group-item list-group-item-action api-item p-3" onclick="selectEndpoint('list_customers')">
                    <div class="d-flex align-items-center mb-1">
                        <span class="badge bg-primary me-2 px-2">GET</span>
                        <span class="fw-bold font-monospace small">/api/v1/customers</span>
                    </div>
                    <small class="text-muted d-block">List All Customers</small>
                </button>
                <button type="button" class="list-group-item list-group-item-action api-item p-3" onclick="selectEndpoint('create_customer')">
                    <div class="d-flex align-items-center mb-1">
                        <span class="badge bg-success me-2 px-2">POST</span>
                        <span class="fw-bold font-monospace small">/api/v1/customers</span>
                    </div>
                    <small class="text-muted d-block">Create Customer</small>
                </button>
                <button type="button" class="list-group-item list-group-item-action api-item p-3" onclick="selectEndpoint('update_customer')">
                    <div class="d-flex align-items-center mb-1">
                        <span class="badge bg-warning text-dark me-2 px-2">PUT</span>
                        <span class="fw-bold font-monospace small">/api/v1/customers/{id}</span>
                    </div>
                    <small class="text-muted d-block">Update Customer</small>
                </button>
                <button type="button" class="list-group-item list-group-item-action api-item p-3" onclick="selectEndpoint('delete_customer')">
                    <div class="d-flex align-items-center mb-1">
                        <span class="badge bg-danger me-2 px-2">DELETE</span>
                        <span class="fw-bold font-monospace small">/api/v1/customers/{id}</span>
                    </div>
                    <small class="text-muted d-block">Delete Customer</small>
                </button>
            </div>
        </div>
    </div>

    <!-- Right Column: Interactive Postman Tester Workspace -->
    <div class="col-lg-8 col-xl-9">
        <div class="card card-custom p-4 shadow-sm mb-4">
            <!-- Endpoint Header & Info -->
            <div class="d-flex justify-content-between align-items-start mb-3 border-bottom pb-3">
                <div>
                    <h4 class="fw-bold mb-1" id="endpointTitle">Create Order & Deduct Stock</h4>
                    <p class="text-muted mb-0" id="endpointDescription">Accepts customer email/name and product list; validates stock availability, computes totals including tax, deducts stock atomically, and dispatches an async queued confirmation job.</p>
                </div>
                <span class="badge bg-warning text-dark font-monospace fs-6" id="requirementBadge">Requirement #2</span>
            </div>

            <!-- HTTP Method & URL Bar -->
            <div class="input-group input-group-lg mb-3 shadow-sm">
                <span class="input-group-text fw-bold text-white bg-success" id="methodBadge">POST</span>
                <input type="text" class="form-control font-monospace bg-light" id="urlInput" value="/api/v1/orders">
                <button type="button" class="btn btn-primary fw-bold px-4" id="btnSendRequest" onclick="executeApiRequest()">
                    <i class="bi bi-send-fill me-1"></i> Send Request
                </button>
            </div>

            <!-- Request Payload Body Tab -->
            <div class="mb-4" id="requestBodySection">
                <label class="form-label fw-semibold d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-braces me-1 text-primary"></i> Request JSON Body (Payload):</span>
                    <button type="button" class="btn btn-sm btn-link text-decoration-none p-0" onclick="resetDefaultPayload()"><i class="bi bi-arrow-counterclockwise me-1"></i>Reset Sample</button>
                </label>
                <textarea id="requestBodyInput" class="form-control font-monospace bg-dark text-light p-3" rows="7" spellcheck="false"></textarea>
            </div>

            <!-- Response Section -->
            <div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="fw-bold mb-0"><i class="bi bi-terminal me-1 text-primary"></i> Live Response Output:</h6>
                    <div class="d-flex gap-2 align-items-center">
                        <span id="responseStatus" class="badge bg-secondary">Status: Ready</span>
                        <span id="responseTime" class="badge bg-light text-dark border">Time: -</span>
                    </div>
                </div>
                <div class="position-relative">
                    <pre id="responseBodyOutput" class="bg-dark text-success p-3 rounded-3 font-monospace mb-0" style="min-height: 200px; max-height: 450px; overflow-y: auto;">// Click "Send Request" to test this API endpoint...</pre>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const sampleProductUuid = "{{ $sampleProduct?->id ?? '' }}";
    const sampleCustomerUuid = "{{ $sampleCustomer?->id ?? '' }}";
    const sampleCustomerEmail = "{{ $sampleCustomer?->email ?? 'john.doe@example.com' }}";

    const endpointsData = {
        create_order: {
            title: "Create Order & Deduct Stock",
            req: "Requirement #2",
            desc: "Accepts customer email/name and list of {product_id, quantity}; validates stock availability, computes totals including tax, deducts stock atomically with lockForUpdate(), and dispatches queued confirmation email.",
            method: "POST",
            methodBg: "bg-success",
            url: "/api/v1/orders",
            hasBody: true,
            payload: JSON.stringify({
                customer_name: "John Counter",
                customer_email: "john.counter@example.com",
                items: [
                    { product_id: sampleProductUuid, quantity: 1 }
                ]
            }, null, 2)
        },
        customer_orders: {
            title: "Fetch Customer Order History by Email",
            req: "Requirement #3",
            desc: "Fetches complete order history records, line items, and totals for a specified customer email address.",
            method: "GET",
            methodBg: "bg-primary",
            url: `/api/v1/customers/orders?email=${encodeURIComponent(sampleCustomerEmail)}`,
            hasBody: false,
            payload: ""
        },
        low_stock: {
            title: "Fetch Low Stock Products",
            req: "Requirement #4",
            desc: "Returns all products where stock_on_hand is at or below the low_stock_threshold (or configurable query parameter ?threshold=5).",
            method: "GET",
            methodBg: "bg-primary",
            url: "/api/v1/products/low-stock?threshold=5",
            hasBody: false,
            payload: ""
        },
        list_products: {
            title: "List Inventory Products",
            req: "Product CRUD",
            desc: "Returns all products in the catalog with optional search filter query parameter.",
            method: "GET",
            methodBg: "bg-primary",
            url: "/api/v1/products",
            hasBody: false,
            payload: ""
        },
        create_product: {
            title: "Create New Product",
            req: "Product CRUD",
            desc: "Adds a new product line item into the inventory system.",
            method: "POST",
            methodBg: "bg-success",
            url: "/api/v1/products",
            hasBody: true,
            payload: JSON.stringify({
                name: "Ultra Ergonomic Desk Mat",
                code: "ACC-MAT-99",
                price_per_unit: 29.99,
                tax_percentage: 12.00,
                stock_on_hand: 20,
                low_stock_threshold: 5
            }, null, 2)
        },
        update_product: {
            title: "Update Product Details / Stock",
            req: "Product CRUD",
            desc: "Updates name, SKU code, unit price, tax %, stock on hand, or low-stock threshold for a product.",
            method: "PUT",
            methodBg: "bg-warning text-dark",
            url: `/api/v1/products/${sampleProductUuid}`,
            hasBody: true,
            payload: JSON.stringify({
                name: "Updated Product Title",
                stock_on_hand: 30
            }, null, 2)
        },
        delete_product: {
            title: "Delete Product",
            req: "Product CRUD",
            desc: "Soft-deletes a product from the active inventory catalog.",
            method: "DELETE",
            methodBg: "bg-danger",
            url: `/api/v1/products/${sampleProductUuid}`,
            hasBody: false,
            payload: ""
        },
        list_customers: {
            title: "List Customer Registry",
            req: "Customer CRUD",
            desc: "Lists all registered customers with total order counts.",
            method: "GET",
            methodBg: "bg-primary",
            url: "/api/v1/customers",
            hasBody: false,
            payload: ""
        },
        create_customer: {
            title: "Create Customer Record",
            req: "Customer CRUD",
            desc: "Creates a new customer record with unique email.",
            method: "POST",
            methodBg: "bg-success",
            url: "/api/v1/customers",
            hasBody: true,
            payload: JSON.stringify({
                name: "Robert Downey",
                email: "robert@example.com",
                phone: "+1 555-0199"
            }, null, 2)
        },
        update_customer: {
            title: "Update Customer Profile",
            req: "Customer CRUD",
            desc: "Updates customer name, email, or phone number.",
            method: "PUT",
            methodBg: "bg-warning text-dark",
            url: `/api/v1/customers/${sampleCustomerUuid}`,
            hasBody: true,
            payload: JSON.stringify({
                name: "Robert Downey Jr.",
                phone: "+1 555-9999"
            }, null, 2)
        },
        delete_customer: {
            title: "Delete Customer Record",
            req: "Customer CRUD",
            desc: "Soft-deletes a customer record from the directory.",
            method: "DELETE",
            methodBg: "bg-danger",
            url: `/api/v1/customers/${sampleCustomerUuid}`,
            hasBody: false,
            payload: ""
        }
    };

    let currentEndpointKey = 'create_order';

    function selectEndpoint(key) {
        currentEndpointKey = key;
        const ep = endpointsData[key];

        document.querySelectorAll('.api-item').forEach(el => el.classList.remove('active'));
        event.currentTarget.classList.add('active');

        document.getElementById('endpointTitle').textContent = ep.title;
        document.getElementById('requirementBadge').textContent = ep.req;
        document.getElementById('endpointDescription').textContent = ep.desc;

        const mBadge = document.getElementById('methodBadge');
        mBadge.className = `input-group-text fw-bold text-white ${ep.methodBg}`;
        mBadge.textContent = ep.method;

        document.getElementById('urlInput').value = ep.url;

        const bodySec = document.getElementById('requestBodySection');
        if (ep.hasBody) {
            bodySec.classList.remove('d-none');
            document.getElementById('requestBodyInput').value = ep.payload;
        } else {
            bodySec.classList.add('d-none');
        }

        // Reset response box
        document.getElementById('responseStatus').className = 'badge bg-secondary';
        document.getElementById('responseStatus').textContent = 'Status: Ready';
        document.getElementById('responseTime').textContent = 'Time: -';
        document.getElementById('responseBodyOutput').textContent = '// Click "Send Request" to test this API endpoint...';
    }

    function resetDefaultPayload() {
        const ep = endpointsData[currentEndpointKey];
        if (ep && ep.hasBody) {
            document.getElementById('requestBodyInput').value = ep.payload;
        }
    }

    async function executeApiRequest() {
        const ep = endpointsData[currentEndpointKey];
        const url = document.getElementById('urlInput').value.trim();
        const method = ep.method;
        const statusBadge = document.getElementById('responseStatus');
        const timeBadge = document.getElementById('responseTime');
        const outputPre = document.getElementById('responseBodyOutput');

        statusBadge.className = 'badge bg-warning text-dark';
        statusBadge.textContent = 'Status: Sending...';
        outputPre.textContent = 'Sending request to server...';

        const startTime = performance.now();

        const headers = {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        };

        let body = null;
        if (ep.hasBody) {
            headers['Content-Type'] = 'application/json';
            try {
                body = document.getElementById('requestBodyInput').value;
                JSON.parse(body); // Validate JSON format
            } catch (err) {
                statusBadge.className = 'badge bg-danger';
                statusBadge.textContent = 'Status: Invalid JSON';
                outputPre.textContent = `Invalid JSON Payload in Request Body:\n${err.message}`;
                return;
            }
        }

        try {
            const response = await fetch(url, {
                method: method,
                headers: headers,
                body: body
            });

            const endTime = performance.now();
            const duration = Math.round(endTime - startTime);
            timeBadge.textContent = `Time: ${duration} ms`;

            const data = await response.json();
            const jsonFormatted = JSON.stringify(data, null, 2);

            if (response.ok) {
                statusBadge.className = 'badge bg-success';
                statusBadge.textContent = `Status: ${response.status} ${response.statusText || 'OK'}`;
                outputPre.className = 'bg-dark text-success p-3 rounded-3 font-monospace mb-0';
            } else {
                statusBadge.className = 'badge bg-danger';
                statusBadge.textContent = `Status: ${response.status} ${response.statusText || 'Error'}`;
                outputPre.className = 'bg-dark text-warning p-3 rounded-3 font-monospace mb-0';
            }

            outputPre.textContent = jsonFormatted;

        } catch (err) {
            statusBadge.className = 'badge bg-danger';
            statusBadge.textContent = 'Status: Network Error';
            outputPre.className = 'bg-dark text-danger p-3 rounded-3 font-monospace mb-0';
            outputPre.textContent = `Error sending request: ${err.message}`;
        }
    }

    // Initialize first endpoint
    document.addEventListener('DOMContentLoaded', () => {
        document.getElementById('requestBodyInput').value = endpointsData.create_order.payload;
    });
</script>
@endpush
