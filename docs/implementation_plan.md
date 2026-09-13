# Implementation Plan: Store Order & Inventory Mini-System

System Architecture, Database Design, API Specifications, Concurrency Strategy, and Comprehensive Development Roadmap for the Laravel Store Order & Inventory Mini-System.

---

## 1. Executive Summary & Scope

The objective is to build a robust, scalable, and fully tested **Store Order & Inventory Mini-System** using Laravel 12. The system enables retail counter operators to manage product catalogs, handle customer records, process point-of-sale (POS) orders, maintain automatic stock synchronization with race-condition safety, and view low-stock alerts.

### Scope Summary
1. **Product CRUD & Management** (Included as requested):
   - Full CRUD operations for Products (Name, SKU/Code, Price, Tax Rate %, Stock on Hand, Low Stock Threshold).
   - Low stock threshold indicators and low-stock filter.
2. **Customer CRUD & Management** (Included as requested):
   - Full CRUD operations for Customers (Name, Email, Phone, Order History lookup).
3. **Order Processing & POS Counter**:
   - Order creation API & web POS interface accepting Customer details and one or more Product lines.
   - Real-time total calculation: `line_subtotal`, `line_tax`, `subtotal`, `tax_total`, `grand_total`.
   - Safe, atomic stock deduction with pessimistic DB locking to prevent race conditions & overselling.
4. **Low Stock Alerts API & Widget**:
   - Configurable low-stock query endpoint (`/api/products/low-stock`).
5. **Asynchronous Queued Notification**:
   - `SendOrderConfirmationEmail` queued job dispatched upon successful order placement, logging formatted order details to `storage/logs/laravel.log` (or sending mail).
6. **Automated Testing Suite**:
   - Feature & Unit tests covering happy path order creation, insufficient stock edge cases, concurrent stock safety, low stock alerts, customer order history, and CRUD validation.
7. **Web UI & Interactive POS Counter**:
   - Modern, responsive interface built with Bootstrap 5 (latest version), featuring a POS counter, Product CRUD modal/pages, Customer CRUD pages, and Order Details view.

---

## 2. Database Schema & Models

The database schema is normalized into 4 core tables: `products`, `customers`, `orders`, and `order_items`.

```
 +------------------+          +------------------+          +-------------------+
 |    customers     |          |      orders      |          |    order_items    |
 +------------------+          +------------------+          +-------------------+
 | id (PK)          |<---------| customer_id (FK) |          | id (PK)           |
 | name             |          | id (PK)          |<---------| order_id (FK)     |
 | email (UNIQUE)   |          | order_number     |          | product_id (FK)   |
 | phone            |          | subtotal         |          | product_name      |
 | created_at       |          | tax_total        |          | product_code      |
 | updated_at       |          | grand_total      |          | unit_price        |
 +------------------+          | status           |          | tax_percentage    |
                               | created_at       |          | quantity          |
                               | updated_at       |          | line_subtotal     |
                               +------------------+          | line_tax          |
                                                             | line_total        |
 +------------------+                                        | created_at        |
 |     products     |                                        +-------------------+
 +------------------+                                                  |
 | id (PK)          |<-------------------------------------------------+
 | name             |
 | code (UNIQUE)    |
 | price_per_unit   |
 | tax_percentage   |
 | stock_on_hand    |
 | low_stock_thresh |
 | created_at       |
 | updated_at       |
 +------------------+
```

### Table Definitions

#### `products` Table
| Column | Type | Attributes | Description |
|---|---|---|---|
| `id` | Uuid | PRIMARY KEY | Unique UUID identifier |
| `name` | String(255) | NOT NULL | Product name |
| `code` | String(100) | UNIQUE, NOT NULL | Unique product code / SKU |
| `price_per_unit` | Decimal(10,2) | NOT NULL, >= 0 | Unit selling price before tax |
| `tax_percentage` | Decimal(5,2) | DEFAULT 0.00, >= 0 | Tax percentage (e.g. 18.00 for 18%) |
| `stock_on_hand` | Integer | DEFAULT 0, >= 0 | Current available stock units |
| `low_stock_threshold`| Integer | DEFAULT 5, >= 0 | Configurable low stock trigger |
| `deleted_at` | Timestamp | NULLABLE | Soft delete timestamp |
| `created_at / updated_at` | Timestamp | | Laravel standard timestamps |

#### `customers` Table
| Column | Type | Attributes | Description |
|---|---|---|---|
| `id` | Uuid | PRIMARY KEY | Unique UUID identifier |
| `name` | String(255) | NOT NULL | Customer full name |
| `email` | String(255) | UNIQUE, NOT NULL | Customer email address |
| `phone` | String(50) | NULLABLE | Contact phone number |
| `deleted_at` | Timestamp | NULLABLE | Soft delete timestamp |
| `created_at / updated_at` | Timestamp | | Laravel standard timestamps |

#### `orders` Table
| Column | Type | Attributes | Description |
|---|---|---|---|
| `id` | Uuid | PRIMARY KEY | Unique UUID identifier |
| `order_number` | String(50) | UNIQUE, NOT NULL | Human-readable ID (e.g. ORD-20260913-9821) |
| `customer_id` | ForeignUuid | FK -> customers.id | Associated customer UUID |
| `subtotal` | Decimal(12,2) | NOT NULL, >= 0 | Sum of all line subtotals |
| `tax_total` | Decimal(12,2) | NOT NULL, >= 0 | Sum of all line tax amounts |
| `grand_total` | Decimal(12,2) | NOT NULL, >= 0 | `subtotal` + `tax_total` |
| `status` | String(30) | DEFAULT 'completed'| Order status (`completed`, `cancelled`) |
| `created_at / updated_at` | Timestamp | | Laravel standard timestamps |

#### `order_items` Table
| Column | Type | Attributes | Description |
|---|---|---|---|
| `id` | Uuid | PRIMARY KEY | Unique UUID identifier |
| `order_id` | ForeignUuid | FK -> orders.id (Cascade Delete) | Parent order UUID |
| `product_id` | ForeignUuid | FK -> products.id (Null on Delete) | Associated product UUID |
| `product_name` | String(255) | NOT NULL | Snapshot of product name at order time |
| `product_code` | String(100) | NOT NULL | Snapshot of product code at order time |
| `unit_price` | Decimal(10,2) | NOT NULL | Snapshot of unit price at order time |
| `tax_percentage` | Decimal(5,2) | NOT NULL | Snapshot of tax % at order time |
| `quantity` | Integer | NOT NULL, > 0 | Units purchased |
| `line_subtotal` | Decimal(12,2) | NOT NULL | `quantity * unit_price` |
| `line_tax` | Decimal(12,2) | NOT NULL | `line_subtotal * (tax_percentage / 100)` |
| `line_total` | Decimal(12,2) | NOT NULL | `line_subtotal + line_tax` |
| `created_at / updated_at` | Timestamp | | Standard timestamps |

---

## 3. Concurrency Safety & Race Condition Prevention

### The Challenge
When multiple cashier terminals or API consumers submit orders concurrently for the same product with limited remaining stock (e.g., stock = 1, and 2 concurrent orders arrive at the exact same millisecond), naive check-then-update logic can lead to **overselling** (stock going negative or double allocation).

### Technical Solution
We implement a dual-layer concurrency guarantee inside `OrderService::createOrder()` using **Database Transactions + Pessimistic Locking (`lockForUpdate()`)**:

```php
return DB::transaction(function () use ($customer, $itemsData) {
    // 1. Collect product IDs and lock rows in deterministic sorted order (prevents deadlock)
    $productIds = collect($itemsData)->pluck('product_id')->sort()->values()->toArray();
    
    // 2. Acquire FOR UPDATE pessimistic lock on target product rows
    $products = Product::whereIn('id', $productIds)
        ->lockForUpdate()
        ->get()
        ->keyBy('id');

    // 3. Verify stock availability for each item
    foreach ($itemsData as $item) {
        $product = $products->get($item['product_id']);
        if (!$product || $product->stock_on_hand < $item['quantity']) {
            throw new InsufficientStockException(
                "Insufficient stock for product '{$product->name}'. Available: {$product->stock_on_hand}, Requested: {$item['quantity']}"
            );
        }
    }

    // 4. Create Order & Items, deduct stock atomically
    foreach ($itemsData as $item) {
        $product = $products->get($item['product_id']);
        $product->decrement('stock_on_hand', $item['quantity']);
        // create line item snapshot...
    }

    // 5. Dispatch async confirmation email job
    dispatch(new SendOrderConfirmationEmail($order));

    return $order;
});
```

#### Why This Is Bulletproof:
- **Pessimistic Locking (`lockForUpdate`)**: Blocks parallel transactions reading or updating the same product rows until the current transaction commits or rolls back.
- **Sorted Lock Acquisition**: Sorting product IDs prior to querying prevents cross-table deadlocks during multi-product order processing.
- **Unsigned DB Constraints**: `stock_on_hand` column has `unsigned` integer validation in MySQL/SQLite preventing negative values at the DB level.

---

## 4. Architectural Structure & Components

The application follows clean Laravel architecture principles (Slim Controllers, Actions/Services, Form Requests, Eloquent Resources).

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Api/
│   │   │   ├── ProductController.php       # Product API endpoints (CRUD + Low stock)
│   │   │   ├── CustomerController.php      # Customer API endpoints (CRUD + History)
│   │   │   └── OrderController.php         # Order API endpoints (Create + View)
│   │   └── Web/
│   │       ├── PosController.php           # Counter Web Interface
│   │       ├── ProductWebController.php    # Product Web Views
│   │       └── CustomerWebController.php   # Customer Web Views
│   ├── Requests/
│   │   ├── StoreProductRequest.php
│   │   ├── UpdateProductRequest.php
│   │   ├── StoreCustomerRequest.php
│   │   ├── UpdateCustomerRequest.php
│   │   └── StoreOrderRequest.php
│   └── Resources/
│       ├── ProductResource.php
│       ├── CustomerResource.php
│       ├── OrderResource.php
│       └── OrderItemResource.php
├── Services/
│   ├── OrderService.php                   # Business logic for order creation & calculation
│   ├── ProductService.php                 # Inventory & Low-stock logic
│   └── CustomerService.php                # Customer records & history
├── Jobs/
│   └── SendOrderConfirmationEmail.php      # Async queued notification
├── Models/
│   ├── Product.php
│   ├── Customer.php
│   ├── Order.php
│   └── OrderItem.php
└── Exceptions/
    ├── InsufficientStockException.php
    └── Handler.php
```

---

## 5. API Endpoints Specification

### 1. Order Endpoints

#### `POST /api/orders`
Creates a new order, calculates totals, deducts stock, and queues confirmation email.

- **Request Payload**:
```json
{
  "customer_name": "John Doe",
  "customer_email": "john@example.com",
  "items": [
    { "product_id": 1, "quantity": 2 },
    { "product_id": 3, "quantity": 1 }
  ]
}
```

- **Success Response (`201 Created`)**:
```json
{
  "status": "success",
  "message": "Order created successfully",
  "data": {
    "id": 12,
    "order_number": "ORD-20260913-7812",
    "customer": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com"
    },
    "subtotal": 250.00,
    "tax_total": 36.00,
    "grand_total": 286.00,
    "items": [
      {
        "product_id": 1,
        "product_name": "Wireless Mouse",
        "product_code": "TECH-001",
        "unit_price": 100.00,
        "tax_percentage": 18.00,
        "quantity": 2,
        "line_subtotal": 200.00,
        "line_tax": 36.00,
        "line_total": 236.00
      }
    ],
    "created_at": "2026-09-13T11:00:00.000000Z"
  }
}
```

- **Error Response (`422 Unprocessable Entity` - Out of stock)**:
```json
{
  "status": "error",
  "message": "Insufficient stock for product 'Wireless Mouse'. Available: 1, Requested: 2"
}
```

#### `GET /api/customers/orders` or `GET /api/orders?email={email}`
Retrieves customer's complete order history using their email address.

---

### 2. Product Endpoints (Including Low-Stock API & CRUD)

- `GET /api/products` - List products with optional search query & low stock filter.
- `GET /api/products/low-stock?threshold=5` - Return all products where `stock_on_hand <= threshold`.
- `GET /api/products/{id}` - Show product details.
- `POST /api/products` - Create new product line item.
- `PUT /api/products/{id}` - Update product attributes/stock.
- `DELETE /api/products/{id}` - Delete/soft-delete product.

---

### 3. Customer Endpoints (CRUD)

- `GET /api/customers` - List all customers with total order counts.
- `GET /api/customers/{id}` - Show customer profile & recent orders.
- `POST /api/customers` - Create customer.
- `PUT /api/customers/{id}` - Update customer.
- `DELETE /api/customers/{id}` - Delete customer.

---

## 6. User Interface Design & POS Counter

The project includes responsive web interfaces built with Laravel Blade & Bootstrap 5 (latest version, Bootstrap 5.3 with Bootstrap Icons) for easy operation by retail counter staff:

1. **POS Counter Screen (`/` or `/pos`)**:
   - Interactive product selection grid with instant search & low stock status badge.
   - Dynamic cart sidebar: add/remove items, adjust quantities.
   - Customer selection lookup or fast new customer entry (name + email).
   - Real-time breakdown of Subtotal, Tax Total, and Grand Total.
   - One-click "Complete Order" submission with receipt generation modal.

2. **Products Management Screen (`/products`)**:
   - Product table with live stock status indicators (Green: In Stock, Yellow: Low Stock, Red: Out of Stock).
   - Add/Edit product modal (Name, SKU code, Unit Price, Tax %, Stock on Hand, Low Stock Threshold).

3. **Customers Management Screen (`/customers`)**:
   - Customer registry with order counts & total spent.
   - Add/Edit customer details modal.
   - View order history drawer for any selected customer.

4. **Order History Screen (`/orders`)**:
   - Filterable order list (by date, customer email, order number).
   - Printable order invoice view.

---

## 7. Development Action Plan & Phase Breakdown

### Phase 1: Environment Setup & Routing Infrastructure
- Install API routes via `routes/api.php` registration in `bootstrap/app.php`.
- Configure `.env` database connection (SQLite / MySQL) and queue driver (`QUEUE_CONNECTION=database`).
- Run queue migrations (`php artisan queue:table`).

### Phase 2: Database Models & Migrations
- Create migrations for `products`, `customers`, `orders`, `order_items`.
- Implement Eloquent Models with relationships & attributes:
  - `Product`: `scopeLowStock()`, `isLowStock()`.
  - `Customer`: `hasMany(Order::class)`.
  - `Order`: `belongsTo(Customer::class)`, `hasMany(OrderItem::class)`.
  - `OrderItem`: `belongsTo(Order::class)`, `belongsTo(Product::class)`.

### Phase 3: Business Logic & Services
- Implement `OrderService`:
  - Customer resolution (find by email or create new).
  - Transactional order creation with pessimistic locking.
  - Snapshot pricing calculations.
- Implement `ProductService` & `CustomerService`.
- Create Form Request classes (`StoreOrderRequest`, `StoreProductRequest`, etc.).
- Create API Resources (`OrderResource`, `ProductResource`, `CustomerResource`).

### Phase 4: Controller & API Implementation
- Build `OrderController` (`store`, `index`, `customerHistory`).
- Build `ProductController` (CRUD endpoints + `lowStock`).
- Build `CustomerController` (CRUD endpoints).

### Phase 5: Async Queued Job & Mail Log Simulation
- Create `SendOrderConfirmationEmail` job (`php artisan make:job SendOrderConfirmationEmail`).
- Implement `handle()` method to log order summary details cleanly in `storage/logs/laravel.log`.

### Phase 6: Web UI & POS Counter Development
- Setup layout Blade template (`layouts/app.blade.php`) with modern header navigation.
- Implement POS Counter View (`views/pos/index.blade.php`).
- Implement Product CRUD Views (`views/products/index.blade.php`).
- Implement Customer CRUD Views (`views/customers/index.blade.php`).
- Implement Order History & Invoice Views (`views/orders/index.blade.php`, `views/orders/show.blade.php`).

### Phase 7: Seeders & Factories
- Create `ProductFactory` & `CustomerFactory`.
- Populate `DatabaseSeeder` with realistic catalog items (e.g. Laptops, Keyboards, Chargers, Cables with varying tax rates 5%, 12%, 18% and low-stock items).

### Phase 8: Comprehensive Automated Testing Suite
- `OrderTest.php`:
  - `test_order_can_be_created_successfully()`
  - `test_order_creation_fails_when_stock_is_insufficient()`
  - `test_stock_is_deducted_correctly_on_order_creation()`
  - `test_concurrent_orders_do_not_oversell_stock()`
- `LowStockTest.php`:
  - `test_low_stock_endpoint_returns_products_below_threshold()`
- `CustomerHistoryTest.php`:
  - `test_customer_order_history_can_be_fetched_by_email()`
- `ProductCrudTest.php` & `CustomerCrudTest.php`:
  - Test full CRUD operations and validation rules.

### Phase 9: Documentation & Deliverables
- Create comprehensive `README.md` containing setup commands, architectural breakdown, and concurrency strategy explanation.
- Verify `docs/implementation_plan.md`.

---

## 8. Verification & Testing Plan

### Automated Commands
```bash
# Run Database Migrations & Seeders
php artisan migrate:fresh --seed

# Execute Full Automated Test Suite
php artisan test

# Test Queue Job Execution
php artisan queue:work --once
```

### Manual Verification Scenarios
1. **POS Counter Flow**: Create order via UI, observe instant stock deduction and grand total calculation.
2. **Low Stock Detection**: Verify product badge turns yellow/red when stock drops below threshold.
3. **Queue Log**: Inspect `storage/logs/laravel.log` to verify order confirmation email log output.
4. **API Testing**: Verify `POST /api/orders`, `GET /api/products/low-stock`, `GET /api/customers/orders?email=...` using Postman / curl.

---
*Prepared for approval prior to implementation.*
