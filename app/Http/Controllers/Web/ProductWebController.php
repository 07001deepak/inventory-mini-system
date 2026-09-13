<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ProductWebController extends Controller
{
    public function __construct(protected ProductService $productService)
    {
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $filter = $request->query('filter');
            $query = Product::query()->latest();

            if ($filter === 'low_stock') {
                $query->lowStock();
            }

            return DataTables::of($query)
                ->editColumn('name', function ($product) {
                    return '<div><div class="fw-bold text-dark">' . e($product->name) . '</div><small class="text-muted">UUID: ' . substr($product->id, 0, 8) . '...</small></div>';
                })
                ->editColumn('code', function ($product) {
                    return '<span class="badge bg-light text-dark border font-monospace">' . e($product->code) . '</span>';
                })
                ->editColumn('price_per_unit', function ($product) {
                    return '₹' . number_format((float) $product->price_per_unit, 2);
                })
                ->editColumn('tax_percentage', function ($product) {
                    return '<span class="badge bg-secondary-subtle text-secondary">' . number_format((float) $product->tax_percentage, 2) . '%</span>';
                })
                ->editColumn('stock_on_hand', function ($product) {
                    $class = $product->stock_on_hand <= 0 ? 'text-danger' : ($product->isLowStock() ? 'text-warning' : 'text-dark');
                    return '<span class="fs-6 fw-bold ' . $class . '">' . $product->stock_on_hand . '</span>';
                })
                ->editColumn('low_stock_threshold', function ($product) {
                    return '<span class="text-muted small">' . $product->low_stock_threshold . ' units</span>';
                })
                ->addColumn('status', function ($product) {
                    if ($product->stock_on_hand <= 0) {
                        return '<span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Out of Stock</span>';
                    } elseif ($product->isLowStock()) {
                        return '<span class="badge bg-warning text-dark"><i class="bi bi-exclamation-triangle me-1"></i>Low Stock</span>';
                    }
                    return '<span class="badge bg-success-subtle text-success border border-success-subtle"><i class="bi bi-check-circle me-1"></i>In Stock</span>';
                })
                ->addColumn('actions', function ($product) {
                    $json = htmlspecialchars(json_encode($product), ENT_QUOTES, 'UTF-8');
                    return '<div class="text-end">
                        <button type="button" class="btn btn-sm btn-outline-primary me-1" onclick="editProduct(' . $json . ')"><i class="bi bi-pencil"></i></button>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDeleteProduct(\'' . $product->id . '\', \'' . addslashes($product->name) . '\')"><i class="bi bi-trash"></i></button>
                    </div>';
                })
                ->rawColumns(['name', 'code', 'price_per_unit', 'tax_percentage', 'stock_on_hand', 'low_stock_threshold', 'status', 'actions'])
                ->make(true);
        }

        return view('products.index');
    }

    public function store(StoreProductRequest $request)
    {
        $this->productService->createProduct($request->validated());

        return redirect()->route('products.index')
            ->with('success', 'Product created successfully.');
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        $this->productService->updateProduct($product, $request->validated());

        return redirect()->route('products.index')
            ->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        $this->productService->deleteProduct($product);

        return redirect()->route('products.index')
            ->with('success', 'Product deleted successfully.');
    }
}
