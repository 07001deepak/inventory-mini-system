<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Product;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed Standard Products
        $products = [
            [
                'name' => 'Logitech MX Master 3S Wireless Mouse',
                'code' => 'TECH-1001',
                'price_per_unit' => 99.99,
                'tax_percentage' => 18.00,
                'stock_on_hand' => 25,
                'low_stock_threshold' => 5,
            ],
            [
                'name' => 'Keychron K2 Mechanical Keyboard',
                'code' => 'TECH-1002',
                'price_per_unit' => 89.50,
                'tax_percentage' => 18.00,
                'stock_on_hand' => 15,
                'low_stock_threshold' => 5,
            ],
            [
                'name' => 'Dell UltraSharp 27" 4K Monitor',
                'code' => 'TECH-1003',
                'price_per_unit' => 450.00,
                'tax_percentage' => 18.00,
                'stock_on_hand' => 8,
                'low_stock_threshold' => 3,
            ],
            [
                'name' => 'Anker 100W USB-C Fast Charger',
                'code' => 'ACC-2001',
                'price_per_unit' => 39.99,
                'tax_percentage' => 12.00,
                'stock_on_hand' => 3, // Low stock on purpose!
                'low_stock_threshold' => 5,
            ],
            [
                'name' => 'Samsung T7 Shield 1TB Portable SSD',
                'code' => 'ACC-2002',
                'price_per_unit' => 119.00,
                'tax_percentage' => 12.00,
                'stock_on_hand' => 2, // Low stock on purpose!
                'low_stock_threshold' => 5,
            ],
            [
                'name' => 'Sony WH-1000XM5 Noise Canceling Headphones',
                'code' => 'AUDIO-3001',
                'price_per_unit' => 348.00,
                'tax_percentage' => 18.00,
                'stock_on_hand' => 12,
                'low_stock_threshold' => 4,
            ],
            [
                'name' => 'Organic Arabica Coffee Beans (1kg)',
                'code' => 'BEV-4001',
                'price_per_unit' => 24.50,
                'tax_percentage' => 5.00,
                'stock_on_hand' => 40,
                'low_stock_threshold' => 10,
            ],
        ];

        foreach ($products as $prod) {
            Product::create($prod);
        }

        // Seed Customers
        $customers = [
            [
                'name' => 'John Doe',
                'email' => 'john.doe@example.com',
                'phone' => '+1 (555) 123-4567',
            ],
            [
                'name' => 'Alice Smith',
                'email' => 'alice.smith@example.com',
                'phone' => '+1 (555) 987-6543',
            ],
            [
                'name' => 'Michael Brown',
                'email' => 'michael.brown@example.com',
                'phone' => '+1 (555) 456-7890',
            ],
        ];

        foreach ($customers as $cust) {
            Customer::create($cust);
        }
    }
}
