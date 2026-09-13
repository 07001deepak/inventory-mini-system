## Mallow Technologies

## Laravel Developer

Mini Task — Take-Home Assignment

Thank you for taking the time to work on this assignment. Please read the full brief before you start. If anything is ambiguous, use your best judgment, make a reasonable assumption, and document it in your README — we're as interested in how you handle ambiguity as in the result.

## Brief: Store Order & Inventory Mini-System

Build a small Laravel application that lets a retail counter record customer orders against a product catalog and keep stock in sync.

## Scope

- Products: name, unique code, price per unit, tax percentage, stock on hand.

- Customers: name, email (unique).

- Orders: one customer, one or more product lines (product + quantity), computed subtotal, tax and grand total.

- Seed the database with sample products and customers using factories/seeders.

## Functional Requirements

- 1. Design a normalized schema (migrations) for the above, adding any supporting tables you judge necessary.

- 2. API endpoint to create an order: accepts customer email/name and a list of {product_id, quantity}; validates stock availability, computes totals including tax, deducts stock, and returns the created order.

- 3. API endpoint to fetch a customer's order history by email.

- 4. API endpoint returning products below a configurable low-stock threshold.

- 5. Dispatch a queued job on order creation that simulates sending an order-confirmation email (a log entry or fake mailer is fine — no real SMTP needed).

- 6. Write feature/unit tests (PHPUnit or Pest) covering order creation, including at least one edge case (e.g. insufficient stock).

- 7. Ensure the stock check-and-deduct step is safe under concurrent requests — if two orders for the same product arrive at nearly the same time and only one unit is left, exactly one should succeed and the other should fail cleanly (no overselling).

## Suggested UI Reference (Wireframe)

This is a low-fidelity layout reference — it shows the expected screen structure and key fields so you can visualize scope before you start. Visual styling, colors and exact layout are entirely up to you; we're evaluating the data and logic underneath, not pixel- perfect design.


## What We're Looking For

- Correct use of Eloquent relationships, migrations and form-request validation.

- Clean, readable code organized the Laravel way (controllers thin, logic in services/actions as appropriate).

- Sensible use of a queued job — shows you understand async processing, not just direct calls.

- Test coverage that reflects how you think about edge cases, not just the happy path.

- A README documenting setup steps and any assumptions made.

AI-assisted development (Copilot, Cursor, Claude, etc.) is welcome and encouraged for this task — we're as interested in how well you use these tools as in the output itself. See the Prompt Log requirement below.

## What to Submit

- Codebase: a GitHub repo (private, shared with us) containing the full Laravel application.

- README.md: setup/run instructions, plus a clear write-up of the assumptions and design decisions you made. If any requirement is ambiguous, make a reasonable assumption, proceed, and document it here — don't stop and wait to ask.

- Prompt Log: if you used AI-assisted tools (Copilot/Cursor/Claude etc.), include screenshots of the actual prompts you used (from your chat/IDE panel) — so we can see exactly what was asked. Save these in a /prompts folder or embed them in the README.

- Screen Recording: a short (5–10 min) video walkthrough of the working application, narrated by you — walk us through what you built and why. A silent screen capture isn't sufficient.

## Timebox & Submission

- Timebox: 3 days elapsed time from receiving this brief.

- Please share your GitHub repo link, README, prompt screenshots (if applicable) and narrated screen recording with [recruiter/HR email] once ready.

- If anything in this brief is unclear, think it through, make the call you believe is most reasonable, and document your reasoning in the README.md — we're evaluating your judgment under ambiguity, not whether you guessed our exact intent.
