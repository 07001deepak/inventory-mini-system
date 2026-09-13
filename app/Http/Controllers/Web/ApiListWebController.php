<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Product;

class ApiListWebController extends Controller
{
    public function index()
    {
        $sampleProduct = Product::where('stock_on_hand', '>', 0)->first() ?? Product::first();
        $sampleCustomer = Customer::first();

        return view('api-list.index', compact('sampleProduct', 'sampleCustomer'));
    }
}
