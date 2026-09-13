<?php

namespace App\Services;

use App\Exceptions\InsufficientStockException;
use App\Jobs\SendOrderConfirmationEmail;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderService
{
    /**
     * Create an order with transactional stock deduction & pessimistic locking.
     *
     * @param array $data ['customer_name' => string, 'customer_email' => string, 'items' => array]
     * @return Order
     * @throws InsufficientStockException|\Throwable
     */
    public function createOrder(array $data): Order
    {
        return DB::transaction(function () use ($data) {
            // 1. Resolve Customer (find by email or create new)
            $customer = Customer::firstOrCreate(
                ['email' => strtolower(trim($data['customer_email']))],
                ['name' => trim($data['customer_name'] ?? 'Customer')]
            );

            // If customer name updated in payload, update customer name
            if (!empty($data['customer_name']) && $customer->name !== trim($data['customer_name'])) {
                $customer->update(['name' => trim($data['customer_name'])]);
            }

            $rawItems = $data['items'] ?? [];
            if (empty($rawItems)) {
                throw new \InvalidArgumentException("An order must contain at least one product item.");
            }

            // Consolidate duplicate product_ids in the order payload
            $itemQuantities = [];
            foreach ($rawItems as $item) {
                $pId = $item['product_id'];
                $qty = (int) $item['quantity'];
                if ($qty <= 0) {
                    continue;
                }
                $itemQuantities[$pId] = ($itemQuantities[$pId] ?? 0) + $qty;
            }

            if (empty($itemQuantities)) {
                throw new \InvalidArgumentException("Invalid product quantities provided.");
            }

            // 2. Sort Product IDs to acquire pessimistic locks deterministically (prevents deadlocks)
            $productIds = array_keys($itemQuantities);
            sort($productIds);

            $products = Product::whereIn('id', $productIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            // 3. Stock Check & Calculations
            $orderItemsData = [];
            $orderSubtotal = 0.00;
            $orderTaxTotal = 0.00;

            foreach ($itemQuantities as $productId => $quantity) {
                /** @var Product|null $product */
                $product = $products->get($productId);

                if (!$product) {
                    throw new \InvalidArgumentException("Product with ID '{$productId}' not found.");
                }

                if ($product->stock_on_hand < $quantity) {
                    throw new InsufficientStockException(
                        "Insufficient stock for product '{$product->name}'. Available: {$product->stock_on_hand}, Requested: {$quantity}"
                    );
                }

                $unitPrice = (float) $product->price_per_unit;
                $taxPercentage = (float) $product->tax_percentage;

                $lineSubtotal = round($quantity * $unitPrice, 2);
                $lineTax = round($lineSubtotal * ($taxPercentage / 100.0), 2);
                $lineTotal = round($lineSubtotal + $lineTax, 2);

                $orderSubtotal += $lineSubtotal;
                $orderTaxTotal += $lineTax;

                $orderItemsData[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'tax_percentage' => $taxPercentage,
                    'line_subtotal' => $lineSubtotal,
                    'line_tax' => $lineTax,
                    'line_total' => $lineTotal,
                ];
            }

            $grandTotal = round($orderSubtotal + $orderTaxTotal, 2);
            $orderNumber = 'ORD-' . date('Ymd') . '-' . strtoupper(Str::random(6));

            // 4. Create Order Record
            $order = Order::create([
                'order_number' => $orderNumber,
                'customer_id' => $customer->id,
                'subtotal' => $orderSubtotal,
                'tax_total' => $orderTaxTotal,
                'grand_total' => $grandTotal,
                'status' => 'completed',
            ]);

            // 5. Create Order Items & Deduct Stock
            foreach ($orderItemsData as $itemData) {
                /** @var Product $product */
                $product = $itemData['product'];
                $qty = $itemData['quantity'];

                // Snapshot item record
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_code' => $product->code,
                    'unit_price' => $itemData['unit_price'],
                    'tax_percentage' => $itemData['tax_percentage'],
                    'quantity' => $qty,
                    'line_subtotal' => $itemData['line_subtotal'],
                    'line_tax' => $itemData['line_tax'],
                    'line_total' => $itemData['line_total'],
                ]);

                // Atomically decrement stock
                $product->decrement('stock_on_hand', $qty);
            }

            // 6. Queue Confirmation Email
            SendOrderConfirmationEmail::dispatch($order);

            return $order->load(['customer', 'items']);
        });
    }

    /**
     * Get customer order history by email.
     */
    public function getCustomerOrdersByEmail(string $email)
    {
        $customer = Customer::where('email', strtolower(trim($email)))->first();

        if (!$customer) {
            return collect();
        }

        return Order::with(['items', 'customer'])
            ->where('customer_id', $customer->id)
            ->latest()
            ->get();
    }
}
