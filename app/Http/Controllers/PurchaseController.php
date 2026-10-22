<?php

namespace App\Http\Controllers;

use App\Http\Requests\PurchaseRequest;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Setting;
use App\Models\StockMovement;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PurchaseController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $supplierId = $request->query('supplier_id');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $query = Purchase::with(['supplier', 'user', 'items.product']);

        if ($search) {
            $query->where('reference_no', 'like', "%{$search}%");
        }

        if ($supplierId) {
            $query->where('supplier_id', $supplierId);
        }

        if ($startDate) {
            $query->whereDate('purchase_date', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('purchase_date', '<=', $endDate);
        }

        $purchases = $query->latest('purchase_date')->latest('id')->paginate(10)->withQueryString();
        $suppliers = Supplier::active()->orderBy('name')->get();

        return view('purchases.index', compact('purchases', 'suppliers', 'search', 'supplierId', 'startDate', 'endDate'));
    }

    public function create(): View
    {
        $suppliers = Supplier::active()->orderBy('name')->get();
        $products = Product::active()->orderBy('name')->get();
        $prefix = Setting::get('purchase_prefix', 'PO-');
        $suggestedRef = $prefix . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

        return view('purchases.create', compact('suppliers', 'products', 'suggestedRef'));
    }

    public function store(PurchaseRequest $request): RedirectResponse
    {
        DB::beginTransaction();

        try {
            $prefix = Setting::get('purchase_prefix', 'PO-');
            $refNo = $prefix . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

            // Ensure unique reference
            while (Purchase::where('reference_no', $refNo)->exists()) {
                $refNo = $prefix . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));
            }

            $totalQty = 0;
            $subtotal = 0;
            $itemsData = [];

            foreach ($request->items as $item) {
                $qty = (int) $item['quantity'];
                $cost = (float) $item['unit_cost'];
                $lineTotal = $qty * $cost;

                $totalQty += $qty;
                $subtotal += $lineTotal;

                $itemsData[] = [
                    'product_id' => $item['product_id'],
                    'quantity' => $qty,
                    'unit_cost' => $cost,
                    'line_total' => $lineTotal,
                ];
            }

            $purchase = Purchase::create([
                'reference_no' => $refNo,
                'supplier_id' => $request->supplier_id,
                'purchase_date' => $request->purchase_date,
                'total_quantity' => $totalQty,
                'subtotal' => $subtotal,
                'grand_total' => $subtotal,
                'notes' => $request->notes,
                'user_id' => Auth::id(),
            ]);

            foreach ($itemsData as $item) {
                // Lock product row to prevent race conditions
                $product = Product::where('id', $item['product_id'])->lockForUpdate()->firstOrFail();

                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'unit_cost' => $item['unit_cost'],
                    'line_total' => $item['line_total'],
                ]);

                // Increase current stock
                $previousStock = $product->current_stock;
                $newStock = $previousStock + $item['quantity'];
                
                // Also update product purchase_price to latest cost
                $product->current_stock = $newStock;
                $product->purchase_price = $item['unit_cost'];
                $product->save();

                // Trace stock movement
                StockMovement::create([
                    'product_id' => $product->id,
                    'type' => 'purchase',
                    'reference_id' => $purchase->id,
                    'reference_no' => $purchase->reference_no,
                    'previous_stock' => $previousStock,
                    'quantity_change' => $item['quantity'],
                    'new_stock' => $newStock,
                    'user_id' => Auth::id(),
                    'notes' => "Stock intake from purchase: {$purchase->reference_no}",
                ]);
            }

            DB::commit();

            return redirect()->route('purchases.show', $purchase)
                ->with('success', "Purchase order '{$purchase->reference_no}' created and stock updated successfully.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error creating purchase order: ' . $e->getMessage());
        }
    }

    public function show(Purchase $purchase): View
    {
        $purchase->load(['supplier', 'user', 'items.product.category']);
        $settings = Setting::getAllSettings();

        return view('purchases.show', compact('purchase', 'settings'));
    }

    public function print(Purchase $purchase): View
    {
        $purchase->load(['supplier', 'user', 'items.product.category']);
        $settings = Setting::getAllSettings();

        return view('purchases.print', compact('purchase', 'settings'));
    }
}
