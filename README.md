# Retail Store Order & Inventory Mini-System

A high-performance, race-condition-safe Laravel 12 application built for retail counter operations. Manages product catalogs, customer registries, point-of-sale (POS) order processing, low-stock alerts, and queued confirmation notifications.

---

## Key Features

- **Modern UI & Typography**: Styled with Bootstrap 5 and Google Font **Roboto** (`font-family: 'Roboto', sans-serif;`) loaded via CDN.
- **Currency**: Configured for Indian Rupee (**INR `₹`**) formatting across all UI views, receipts, API resources, and datatables.
- **UUID Identifiers**: All database models (`Product`, `Customer`, `Order`, `OrderItem`) use 36-character UUID primary and foreign keys (`HasUuids`).
- **Race-Condition Safe Stock Engine**: Concurrency safety using Database Transactions + Pessimistic Locking (`lockForUpdate()`) and pre-sorted lock keys to prevent deadlocks and overselling under parallel counter checkout requests.
- **Product Management (CRUD)**: Web dashboard with Yajra DataTables server-side pagination, search, sorting, and Bootstrap modal dialogs for creating and editing products.
- **Customer Management (CRUD)**: Web registry featuring Yajra DataTables, modal creation/editing, order count badges, and customer purchase history modal.
- **Point of Sale (POS) Counter UI**: Interactive counter interface featuring **Select2** searchable customer dropdown, real-time cart calculations (subtotal, line tax, grand total in `₹`), live product search, stock validation, and printable receipt modal.
- **Orders Log & Invoice Viewer**: Comprehensive order ledger powered by Yajra DataTables server-side processing with click-to-view detailed invoice popups.
- **API List & Interactive Postman Tester Page**: Built-in interactive workspace ([`/api-list`](http://localhost:8000/api-list)) listing all API V1 endpoints with live `Send Request` testing capabilities, JSON payload editor, and HTTP response inspector.
- **API Versioning (V1)**: Reorganized API controllers under `App\Http\Controllers\Api\V1` namespace and versioned routes under `/api/v1/<route>`.
- **Standardized API Helper Functions**: Utilizes global `sendResponse()` and `sendError()` helpers ([`app/Helpers/helpers.php`](app/Helpers/helpers.php)) for consistent API JSON structures (`status`, `message`, `data`/`errors`).
- **Low Stock Alerts API**: Dedicated query endpoint (`/api/v1/products/low-stock`) returning products at or below threshold.
- **Async Queued Notifications**: `SendOrderConfirmationEmail` job queued via database driver logging formatted receipt output to `storage/logs/laravel.log`.
- **100% Test Suite Coverage**: 18 automated PHPUnit feature & unit tests (52 assertions) verifying order placement, insufficient stock failures, concurrent race-condition safety, low-stock threshold queries, and CRUD API endpoints.

---

## Architectural Breakdown

```
app/
├── Helpers/
│   └── helpers.php                         # Global API response helpers (sendResponse, sendError)
├── Http/
│   ├── Controllers/
│   │   ├── Api/
│   │   │   └── V1/                         # Version 1 API Controllers
│   │   │       ├── ProductController.php   # Product API V1 (CRUD + Low stock endpoint)
│   │   │       ├── CustomerController.php  # Customer API V1 (CRUD + Order history endpoint)
│   │   │       └── OrderController.php     # Order API V1 (Store + Index + Show)
│   │   └── Web/
│   │       ├── PosController.php           # POS Counter interface
│   │       ├── ProductWebController.php    # Product CRUD web view & DataTables API
│   │       ├── CustomerWebController.php   # Customer CRUD web view & DataTables API
│   │       ├── OrderWebController.php      # Orders log web view & DataTables API
│   │       └── ApiListController.php       # Interactive API List tester page
│   ├── Requests/                         # Strict FormRequest validation classes
│   └── Resources/                        # Eloquent API Resources (INR currency formatting)
├── Services/
│   ├── OrderService.php                   # Core business logic with lockForUpdate() & stock deduction
│   ├── ProductService.php                 # Inventory queries & CRUD logic
│   └── CustomerService.php                # Customer management service
├── Jobs/
│   └── SendOrderConfirmationEmail.php      # Queued notification job
├── Models/
│   ├── Product.php                        # Uses HasUuids & SoftDeletes
│   ├── Customer.php                       # Uses HasUuids & SoftDeletes
│   ├── Order.php                          # Uses HasUuids
│   └── OrderItem.php                      # Uses HasUuids & stores price snapshots
└── Exceptions/
    └── InsufficientStockException.php      # Custom stock validation exception
```

---

## Technical Design Decisions & Assumptions

1. **UUID Keys**: UUIDs (`HasUuids`) were selected over auto-incrementing integer IDs to ensure distributed system compatibility, secure non-sequential order/customer identifiers, and API safety.
2. **Pessimistic Locking (`lockForUpdate()`)**: High-concurrency retail systems risk stock overselling when two requests attempt to order the last item simultaneously. Wrapping the check and deduction step inside `DB::transaction()` with `lockForUpdate()` guarantees serial access per product row.
3. **Deadlock Prevention**: Before acquiring row locks in `OrderService`, product IDs in the order request are sorted deterministically.
4. **Historical Data Snapshots**: `order_items` stores copies of `product_name`, `product_code`, `unit_price`, and `tax_percentage` at the time of purchase. Subsequent changes to product pricing or catalog updates do not alter past order records.
5. **Server-Side DataTables**: All record lists (Products, Customers, Orders Log) leverage Yajra DataTables server-side processing to handle large datasets efficiently.

---

## Quick Setup Instructions

### Prerequisites
- PHP >= 8.2
- Composer
- MySQL / MariaDB (XAMPP default)

> **Note**: Frontend assets (Bootstrap 5, FontAwesome 6, jQuery, Select2, DataTables) are delivered via CDN. No Node.js or NPM build step is required.

### Step-by-Step Installation

1. **Clone & Install Dependencies**:
   ```bash
   git clone <repo-url> inventory-system
   cd inventory-system
   composer install
   ```

2. **Environment Configuration**:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   *The `.env.example` file is synced with MySQL credentials (`DB_DATABASE=inventory_mini_system`, `DB_USERNAME=root`, `DB_PASSWORD=root`).*

3. **Run Migrations & Seed Sample Data**:
   ```bash
   php artisan migrate:fresh --seed
   ```

4. **Serve Application & Queue Worker**:
   In one terminal:
   ```bash
   php artisan serve
   ```
   In a second terminal (for async queued email jobs):
   ```bash
   php artisan queue:work
   ```

5. **Access Application**:
   - **POS Counter**: [http://localhost:8000](http://localhost:8000)
   - **Products Catalog**: [http://localhost:8000/products](http://localhost:8000/products)
   - **Customers Registry**: [http://localhost:8000/customers](http://localhost:8000/customers)
   - **Orders Log**: [http://localhost:8000/orders](http://localhost:8000/orders)
   - **API List & Postman Tester**: [http://localhost:8000/api-list](http://localhost:8000/api-list)

---

## API V1 Documentation Quick Reference

All API routes are prefixed with `/api/v1/`:

| Method | Endpoint | Description |
|---|---|---|
| `POST` | `/api/v1/orders` | Create an order & deduct stock |
| `GET` | `/api/v1/orders/{id}` | View order details |
| `GET` | `/api/v1/customers/orders?email={email}` | Fetch customer order history by email |
| `GET` | `/api/v1/products/low-stock?threshold=5` | Get products below low-stock threshold |
| `GET` | `/api/v1/products` | List all products (supports `?search=...`) |
| `POST` | `/api/v1/products` | Create product |
| `PUT` | `/api/v1/products/{id}` | Update product |
| `DELETE` | `/api/v1/products/{id}` | Delete product |
| `GET` | `/api/v1/customers` | List all customers |
| `POST` | `/api/v1/customers` | Create customer |
| `PUT` | `/api/v1/customers/{id}` | Update customer |
| `DELETE` | `/api/v1/customers/{id}` | Delete customer |

---

## Running Automated Tests

Run the full PHPUnit feature test suite:
```bash
php artisan test
```

Test Suite Execution Result:
```
PASS  Tests\Unit\ExampleTest
✓ that true is true

PASS  Tests\Feature\ConcurrentStockTest
✓ stock deduction is race condition safe

PASS  Tests\Feature\CustomerCrudTest
✓ can list customers via api
✓ can create customer via api
✓ can update customer via api
✓ can delete customer via api

PASS  Tests\Feature\CustomerHistoryTest
✓ can fetch customer order history by email
✓ customer order history returns empty when email has no orders

PASS  Tests\Feature\ExampleTest
✓ the application returns a successful response

PASS  Tests\Feature\LowStockTest
✓ low stock endpoint returns products at or below threshold
✓ low stock endpoint accepts custom threshold param

PASS  Tests\Feature\OrderTest
✓ order can be created via api
✓ order creation fails when stock is insufficient
✓ order creates snapshot details in order items

PASS  Tests\Feature\ProductCrudTest
✓ can list products via api
✓ can create product via api
✓ can update product via api
✓ can delete product via api

Tests:    18 passed (52 assertions)
```

---

## Documentation, Prompt History & Screenshots Index

All task specifications, master design plans, user prompt histories, session screenshots, and step-by-step implementation logs are stored in the [`docs/`](docs/) directory:

- **Original Task Specification**: [`docs/Laravel_Developer_Mini_Task.md`](docs/Laravel_Developer_Mini_Task.md)
- **Master Implementation Plan**: [`docs/implementation_plan.md`](docs/implementation_plan.md)
- **Complete User Prompt & Chat History**: [`docs/user_prompts_history.md`](docs/user_prompts_history.md)
- **Session Screenshots Directory**: [`docs/images/`](docs/images/) *(Save your session screenshots here as `prompt_image_1.png`)* and so on

### Implementation Tracker Logs (`docs/implementation-tracker/`)
1. Log #01 - Initial Core System Implementation: [`docs/implementation-tracker/01_initial_implementation_tracker.md`](docs/implementation-tracker/01_initial_implementation_tracker.md)
2. Log #02 - Customer & Product CRUD UI Modals & API List Tester Page: [`docs/implementation-tracker/02_corrections_and_api_list_page.md`](docs/implementation-tracker/02_corrections_and_api_list_page.md)
3. Log #03 - Web & API Route Naming Isolation Fix: [`docs/implementation-tracker/03_route_name_isolation_fix.md`](docs/implementation-tracker/03_route_name_isolation_fix.md)
4. Log #04 - Select2 & Yajra DataTables Integration: [`docs/implementation-tracker/04_select2_and_yajra_datatables_integration.md`](docs/implementation-tracker/04_select2_and_yajra_datatables_integration.md)
5. Log #05 - Currency Symbol Update to INR (`₹`): [`docs/implementation-tracker/05_currency_symbol_update_to_inr.md`](docs/implementation-tracker/05_currency_symbol_update_to_inr.md)
6. Log #06 - API Helper Function Refactoring (`sendResponse` / `sendError`): [`docs/implementation-tracker/06_api_helper_function_refactoring.md`](docs/implementation-tracker/06_api_helper_function_refactoring.md)
7. Log #07 - API Controller & Route Versioning (`api/v1/...`): [`docs/implementation-tracker/07_api_v1_versioning.md`](docs/implementation-tracker/07_api_v1_versioning.md)
