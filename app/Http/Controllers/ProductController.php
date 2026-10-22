<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $categoryId = $request->query('category_id');
        $stockStatus = $request->query('stock_status');

        $query = Product::with(['category', 'supplier'])->search($search);

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        if ($stockStatus === 'low_stock') {
            $query->lowStock();
        } elseif ($stockStatus === 'out_of_stock') {
            $query->outOfStock();
        } elseif ($stockStatus === 'in_stock') {
            $query->inStock();
        }

        $products = $query->latest('id')->paginate(10)->withQueryString();

        $categories = Category::active()->orderBy('name')->get();

        return view('products.index', compact('products', 'categories', 'search', 'categoryId', 'stockStatus'));
    }

    public function create(): View
    {
        $categories = Category::active()->orderBy('name')->get();
        $suppliers = Supplier::active()->orderBy('name')->get();

        return view('products.create', compact('categories', 'suppliers'));
    }

    public function store(ProductRequest $request): RedirectResponse
    {
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
        }

        DB::beginTransaction();
        try {
            $initialStock = max(0, (int) $request->current_stock);

            $product = Product::create([
                'name' => $request->name,
                'sku' => strtoupper(trim($request->sku)),
                'barcode' => $request->barcode,
                'category_id' => $request->category_id,
                'supplier_id' => $request->supplier_id,
                'purchase_price' => $request->purchase_price,
                'selling_price' => $request->selling_price,
                'current_stock' => $initialStock,
                'min_stock_threshold' => max(0, (int) $request->min_stock_threshold),
                'image' => $imagePath,
                'description' => $request->description,
                'is_active' => $request->boolean('is_active', true),
            ]);

            // Trace initial opening stock movement if stock > 0
            if ($initialStock > 0) {
                StockMovement::create([
                    'product_id' => $product->id,
                    'type' => 'adjustment',
                    'reference_id' => null,
                    'reference_no' => 'INIT-' . $product->sku,
                    'previous_stock' => 0,
                    'quantity_change' => $initialStock,
                    'new_stock' => $initialStock,
                    'user_id' => Auth::id(),
                    'notes' => 'Initial product opening stock setup.',
                ]);
            }

            DB::commit();

            return redirect()->route('products.index')
                ->with('success', "Product '{$product->name}' created successfully.");
        } catch (\Exception $e) {
            DB::rollBack();
            if ($imagePath && Storage::disk('public')->exists($imagePath)) {
                Storage::disk('public')->delete($imagePath);
            }

            return back()->withInput()->with('error', 'Error creating product: ' . $e->getMessage());
        }
    }

    public function show(Product $product): View
    {
        $product->load(['category', 'supplier', 'stockMovements.user' => function ($q) {
            $q->latest('id')->take(20);
        }]);

        return view('products.show', compact('product'));
    }

    public function edit(Product $product): View
    {
        $categories = Category::active()->orderBy('name')->get();
        $suppliers = Supplier::active()->orderBy('name')->get();

        return view('products.edit', compact('product', 'categories', 'suppliers'));
    }

    public function update(ProductRequest $request, Product $product): RedirectResponse
    {
        $imagePath = $product->image;

        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($imagePath && Storage::disk('public')->exists($imagePath)) {
                Storage::disk('public')->delete($imagePath);
            }
            $imagePath = $request->file('image')->store('products', 'public');
        } elseif ($request->boolean('remove_image')) {
            if ($imagePath && Storage::disk('public')->exists($imagePath)) {
                Storage::disk('public')->delete($imagePath);
            }
            $imagePath = null;
        }

        $product->update([
            'name' => $request->name,
            'sku' => strtoupper(trim($request->sku)),
            'barcode' => $request->barcode,
            'category_id' => $request->category_id,
            'supplier_id' => $request->supplier_id,
            'purchase_price' => $request->purchase_price,
            'selling_price' => $request->selling_price,
            'min_stock_threshold' => max(0, (int) $request->min_stock_threshold),
            'image' => $imagePath,
            'description' => $request->description,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('products.index')
            ->with('success', "Product '{$product->name}' updated successfully.");
    }

    public function destroy(Product $product): RedirectResponse
    {
        // Safe check: prevent deleting products with purchase or sale history
        if ($product->purchaseItems()->count() > 0 || $product->saleItems()->count() > 0) {
            return back()->with('error', "Cannot delete product '{$product->name}' because transaction records (purchases or sales) are associated with it. You can mark it as inactive instead.");
        }

        if ($product->image && Storage::disk('public')->exists($product->image)) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return redirect()->route('products.index')
            ->with('success', 'Product deleted successfully.');
    }
}
