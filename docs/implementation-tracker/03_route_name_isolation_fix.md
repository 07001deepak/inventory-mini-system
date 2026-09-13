# Implementation Tracker Log #03 - Route Name Isolation Fix

**Date & Time**: 2026-09-13
**System**: Store Order & Inventory Mini-System (Laravel 12)
**Author / Assistant**: Antigravity AI

---

## 1. Issue & Resolution Summary

### Problem
Previously, both `routes/api.php` and `routes/web.php` generated duplicate default route names (`products.index`, `customers.index`, `orders.index`). In Laravel route resolution, the API route definitions overwrote the Web route names, causing header links like `Product Catalog`, `Customer Directory`, and `Orders Log` to navigate directly to raw JSON API endpoints (`/api/products`, `/api/customers`, `/api/orders`) instead of rendering the full Bootstrap 5 HTML Blade views.

### Resolution
- Isolated all API route names by adding `Route::name('api.')->group(...)` in `routes/api.php`.
- API endpoints are now unambiguously named:
  - `api.products.index`, `api.products.store`, `api.products.update`, `api.products.destroy`, `api.products.low-stock`
  - `api.customers.index`, `api.customers.store`, `api.customers.update`, `api.customers.destroy`, `api.customers.orders`
  - `api.orders.index`, `api.orders.store`, `api.orders.show`
- Web Blade routes remain cleanly bound to user-facing pages:
  - `products.index` -> `/products` (Renders Product CRUD page with Add/Edit Bootstrap 5 Modals)
  - `customers.index` -> `/customers` (Renders Customer Directory page with Add/Edit Bootstrap 5 Modals)
  - `orders.index` -> `/orders` (Renders Orders Log table with View Receipt button)
  - `orders.show` -> `/orders/{order}` (Renders Printable Order Invoice/Receipt page)

---

## 2. Verified Page Behaviors

1. **Product Catalog Page (`/products`)**:
   - Renders HTML table listing all inventory items with status badges.
   - Clicking **"Add New Product"** opens the Bootstrap 5 Create Product modal.
   - Clicking **Edit** opens the Bootstrap 5 Edit Product modal with pre-populated values.

2. **Customer Directory Page (`/customers`)**:
   - Renders HTML table listing customer records with order count badges.
   - Clicking **"Add New Customer"** opens the Bootstrap 5 Create Customer modal.
   - Clicking **Edit** opens the Bootstrap 5 Edit Customer modal.
   - Clicking **Orders** opens the Order History drawer modal.

3. **Orders Log Page (`/orders`)**:
   - Renders HTML table listing all completed orders (Order #, Customer Name, Email, Line Item count, Subtotal, Tax, Grand Total, Date).
   - Clicking **"View Receipt"** opens the full Order Details & Invoice view ([`/orders/{order}`](file:///C:/xampp/htdocs/mallow-mini-task/inventory-system/resources/views/orders/show.blade.php)).

---

## 3. Test Suite Verification

Executed command: `php artisan test`
Result: **18 tests passed (52 assertions)**.

---
*End of Tracker Log #03*
