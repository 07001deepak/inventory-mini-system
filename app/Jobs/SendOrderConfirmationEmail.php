<?php

namespace App\Jobs;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendOrderConfirmationEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Order $order)
    {
    }

    public function handle(): void
    {
        $this->order->loadMissing(['customer', 'items']);

        $customerName = $this->order->customer?->name ?? 'Customer';
        $customerEmail = $this->order->customer?->email ?? 'N/A';

        $logOutput = sprintf(
            "\n=======================================================\n" .
            "SIMULATED ORDER CONFIRMATION EMAIL\n" .
            "-------------------------------------------------------\n" .
            "To: %s <%s>\n" .
            "Subject: Order Confirmation - %s\n" .
            "-------------------------------------------------------\n" .
            "Order Date: %s\n" .
            "Subtotal:   ₹%s\n" .
            "Tax Total:  ₹%s\n" .
            "Grand Total: ₹%s\n" .
            "Line Items:\n",
            $customerName,
            $customerEmail,
            $this->order->order_number,
            $this->order->created_at?->format('Y-m-d H:i:s'),
            number_format((float)$this->order->subtotal, 2),
            number_format((float)$this->order->tax_total, 2),
            number_format((float)$this->order->grand_total, 2)
        );

        foreach ($this->order->items as $item) {
            $logOutput .= sprintf(
                " - [%s] %s | Qty: %d x ₹%s | Tax: %s%% | Total: ₹%s\n",
                $item->product_code,
                $item->product_name,
                $item->quantity,
                number_format((float)$item->unit_price, 2),
                number_format((float)$item->tax_percentage, 2),
                number_format((float)$item->line_total, 2)
            );
        }

        $logOutput .= "=======================================================\n";

        Log::info($logOutput);
    }
}
