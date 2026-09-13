# User Prompts & Revision History Log

This document records the chronological history of user requirements, corrections, prompt requests, and session screenshots provided during the development of the Retail Store Order & Inventory Mini-System.

---

## Session Prompt Screenshots

Place session prompt screenshots under [`docs/images/`](images/):
- **Session Screenshot**: ![Session Prompt Screenshot](images/prompt_session_1.png)

*(Note: Save screenshot image as `docs/images/prompt_session_1.png` to render automatically above.)*

---

## Chronological Chat / Request History

### Request #1: Initial Analysis & Implementation Planning
- **Prompt**: Analyze `docs/Laravel_Developer_Mini_Task.md` thoroughly, extract requirements, and prepare a detailed `implementation_plan.md` file under `docs/`. Include missing Product CRUD and Customer CRUD pages. Wait for approval before implementing.
- **Action Taken**: Created `docs/implementation_plan.md` specifying database schema, services, controllers, routes, views, and test suites.

### Request #2: Bootstrap 5 Requirement
- **Prompt**: Use latest Bootstrap version for the UI.
- **Action Taken**: Configured Bootstrap 5 CDN in Blade layout (`resources/views/layouts/app.blade.php`).

### Request #3: UUID Primary Keys
- **Prompt**: Use UUID for all tables instead of autoincrement ID columns.
- **Action Taken**: Configured Eloquent `HasUuids` trait and 36-char `uuid` schema definitions across `Product`, `Customer`, `Order`, and `OrderItem` models and migrations.

### Request #4: Start Implementation & Track Progress
- **Prompt**: Implement `docs/implementation_plan.md` with 100% accuracy and create an `implementation-tracker/` folder under `docs/` containing implementation log `#01`.
- **Action Taken**: Built core models, services, controllers, web views, queued jobs, unit/feature tests, and generated [`docs/implementation-tracker/01_initial_implementation_tracker.md`](implementation-tracker/01_initial_implementation_tracker.md).

### Request #5: Typography, Navigation & API Tester Page
- **Prompt**: 
  1. Font Family: Use **Roboto**.
  2. Customer and Product List CRUD Page: Add routes in header navigation.
  3. Header Section: Group API links under an "API List" section featuring an interactive Postman-style tester page.
- **Action Taken**: Applied Roboto font via Google Fonts, added Product & Customer links in navbar, built `/api-list` interactive tester page, and logged progress in [`docs/implementation-tracker/02_corrections_and_api_list_page.md`](implementation-tracker/02_corrections_and_api_list_page.md) & [`03_route_name_isolation_fix.md`](implementation-tracker/03_route_name_isolation_fix.md).

### Request #6: CRUD Modal Popups & Orders Log Table View
- **Prompt**: 
  1. Add modal popups for Add and Edit actions on Product and Customer CRUD pages.
  2. Orders Log page should display orders in a table with a click-to-view detail modal (invoice preview) instead of raw API text.
- **Action Taken**: Enhanced Product, Customer, and Orders Log web views with Bootstrap modals and AJAX submit handlers.

### Request #7: Select2 Dropdown & Yajra DataTables Integration
- **Prompt**: 
  1. POS Counter page: Upgrade Customer select dropdown to Select2.
  2. Customer, Product, and Orders Log pages: Implement Yajra DataTables for server-side table processing.
- **Action Taken**: Integrated Select2 on POS Counter customer select element, integrated Yajra DataTables server-side endpoints and datatable scripts, and logged progress in [`docs/implementation-tracker/04_select2_and_yajra_datatables_integration.md`](implementation-tracker/04_select2_and_yajra_datatables_integration.md).

### Request #8: Currency Update to INR
- **Prompt**: Change currency symbol from Dollar (`$`) to Indian Rupee (`₹`).
- **Action Taken**: Updated currency formatting across all views, JavaScript scripts, API resources, and test assertions to `₹`, and logged progress in [`docs/implementation-tracker/05_currency_symbol_update_to_inr.md`](implementation-tracker/05_currency_symbol_update_to_inr.md).

### Request #9: API Helper Functions (`sendResponse` / `sendError`)
- **Prompt**: Create helper functions (`sendResponse()`, `sendError()`) to eliminate duplicate `response()->json([])` boilerplate across API controllers.
- **Action Taken**: Created `app/Helpers/helpers.php`, registered it in `bootstrap/app.php` and `composer.json`, refactored all API controllers to use `sendResponse()` and `sendError()`, and logged progress in [`docs/implementation-tracker/06_api_helper_function_refactoring.md`](implementation-tracker/06_api_helper_function_refactoring.md).

### Request #10: API Controller Reorganization & Route Versioning (V1)
- **Prompt**: Version API controllers under `App\Http\Controllers\Api\V1` directory/namespace and update API routes to `/api/v1/<route>`.
- **Action Taken**: Created `App\Http\Controllers\Api\V1` namespace, moved `ProductController`, `CustomerController`, and `OrderController` to `V1`, updated `routes/api.php` with `Route::prefix('v1')->name('api.v1.')`, updated front-end fetch calls, updated test suite endpoints, verified 18/18 tests pass, and logged progress in [`docs/implementation-tracker/07_api_v1_versioning.md`](implementation-tracker/07_api_v1_versioning.md).

### Request #11: Sync README.md & Document Chat History
- **Prompt**: Sync `README.md` with all current features, architecture changes, API V1 endpoints, and link all documentation files and prompt history under `docs/`.
- **Action Taken**: Created `docs/user_prompts_history.md` and updated `README.md`.

### Request #12: Fix Installation Setup & Sync `.env.example`
- **Prompt**: Remove Node/NPM references from `README.md` since CDN assets are used, update setup steps, and ensure `.env.example` matches `.env`.
- **Action Taken**: Removed NPM instructions from `README.md` and synced `.env.example` with MySQL environment settings.

### Request #13: Session Screenshot Attachment Setup
- **Prompt**: Provide screenshot attachment instructions and wire up screenshot references in project documentation.
- **Action Taken**: Created `docs/images/` directory, added screenshot Markdown references in `user_prompts_history.md` and `README.md`.
