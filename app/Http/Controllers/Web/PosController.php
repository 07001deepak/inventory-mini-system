<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Product;
use Illuminate\Http\Request;

class PosController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::where('stock_on_hand', '>', 0)->orderBy('name')->get();
        $customers = Customer::orderBy('name')->get();

        return view('pos.index', compact('products', 'customers'));
    }
}
