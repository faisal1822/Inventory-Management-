<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaleRequest;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Setting;
use App\Models\StockMovement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SaleController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $paymentMethod = $request->query('payment_method');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $query = Sale::with(['user', 'items.product']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('invoice_no', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_phone', 'like', "%{$search}%");
            });
        }

        if ($paymentMethod) {
            $query->where('payment_method', $paymentMethod);
        }

        if ($startDate) {
            $query->whereDate('sale_date', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('sale_date', '<=', $endDate);
        }

        $sales = $query->latest('sale_date')->latest('id')->paginate(10)->withQueryString();

        return view('sales.index', compact('sales', 'search', 'paymentMethod', 'startDate', 'endDate'));
    }

    public function create(): View
    {
        // Only load active products that have available stock > 0
        $products = Product::active()->orderBy('name')->get();
        $prefix = Setting::get('invoice_prefix', 'INV-');
        $suggestedInvoice = $prefix . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

        return view('sales.create', compact('products', 'suggestedInvoice'));
    }

    public function store(SaleRequest $request): RedirectResponse
    {
        DB::beginTransaction();

        try {
            $prefix = Setting::get('invoice_prefix', 'INV-');
            $invoiceNo = $prefix . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

            while (Sale::where('invoice_no', $invoiceNo)->exists()) {
                $invoiceNo = $prefix . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));
            }

            $totalQty = 0;
            $subtotal = 0;
            $itemsData = [];

            // Pre-validation and stock checking with pessimistic locking
            foreach ($request->items as $item) {
                $productId = $item['product_id'];
                $qty = (int) $item['quantity'];
                $price = (float) $item['unit_price'];

                // Pessimistic locking to prevent race conditions during concurrent sales
                $product = Product::where('id', $productId)->lockForUpdate()->firstOrFail();

                if ($product->current_stock < $qty) {
                    DB::rollBack();
                    return back()->withInput()->with('error', "Insufficient stock for product '{$product->name}' (SKU: {$product->sku}). Available: {$product->current_stock}, Requested: {$qty}.");
                }

                $lineTotal = $qty * $price;
                $totalQty += $qty;
                $subtotal += $lineTotal;

                $itemsData[] = [
                    'product' => $product,
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'unit_cost' => (float) $product->purchase_price,
                    'line_total' => $lineTotal,
                ];
            }

            $discount = max(0, (float) ($request->discount ?? 0));
            $grandTotal = max(0, $subtotal - $discount);
            $paidAmount = $request->filled('paid_amount') ? (float) $request->paid_amount : $grandTotal;

            $sale = Sale::create([
                'invoice_no' => $invoiceNo,
                'sale_date' => $request->sale_date,
                'customer_name' => $request->customer_name ?: 'Walk-in Customer',
                'customer_phone' => $request->customer_phone,
                'total_quantity' => $totalQty,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'grand_total' => $grandTotal,
                'paid_amount' => $paidAmount,
                'payment_method' => $request->payment_method,
                'notes' => $request->notes,
                'user_id' => Auth::id(),
            ]);

            foreach ($itemsData as $data) {
                $product = $data['product'];

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'quantity' => $data['quantity'],
                    'unit_price' => $data['unit_price'],
                    'unit_cost' => $data['unit_cost'],
                    'line_total' => $data['line_total'],
                ]);

                // Decrease stock
                $previousStock = $product->current_stock;
                $newStock = $previousStock - $data['quantity'];
                $product->current_stock = $newStock;
                $product->save();

                // Record stock movement
                StockMovement::create([
                    'product_id' => $product->id,
                    'type' => 'sale',
                    'reference_id' => $sale->id,
                    'reference_no' => $sale->invoice_no,
                    'previous_stock' => $previousStock,
                    'quantity_change' => -$data['quantity'],
                    'new_stock' => $newStock,
                    'user_id' => Auth::id(),
                    'notes' => "Stock deduction for sale invoice: {$sale->invoice_no}",
                ]);
            }

            DB::commit();

            return redirect()->route('sales.show', $sale)
                ->with('success', "Sale invoice '{$sale->invoice_no}' created and inventory reduced successfully.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error recording sale: ' . $e->getMessage());
        }
    }

    public function show(Sale $sale): View
    {
        $sale->load(['user', 'items.product.category']);
        $settings = Setting::getAllSettings();

        return view('sales.show', compact('sale', 'settings'));
    }

    public function print(Sale $sale): View
    {
        $sale->load(['user', 'items.product.category']);
        $settings = Setting::getAllSettings();

        return view('sales.print', compact('sale', 'settings'));
    }
}
