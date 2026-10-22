<?php

namespace App\Http\Controllers;

use App\Http\Requests\StockAdjustmentRequest;
use App\Models\Product;
use App\Models\StockAdjustment;
use App\Models\StockMovement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StockAdjustmentController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $reason = $request->query('reason');
        $type = $request->query('type');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $query = StockAdjustment::with(['product', 'user']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('adjustment_no', 'like', "%{$search}%")
                  ->orWhereHas('product', function ($pq) use ($search) {
                      $pq->where('name', 'like', "%{$search}%")
                         ->orWhere('sku', 'like', "%{$search}%");
                  });
            });
        }

        if ($reason) {
            $query->where('reason', $reason);
        }

        if ($type) {
            $query->where('type', $type);
        }

        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        $adjustments = $query->latest('id')->paginate(10)->withQueryString();
        $reasons = StockAdjustment::getReasons();

        return view('adjustments.index', compact('adjustments', 'reasons', 'search', 'reason', 'type', 'startDate', 'endDate'));
    }

    public function create(): View
    {
        $products = Product::active()->orderBy('name')->get();
        $reasons = StockAdjustment::getReasons();

        return view('adjustments.create', compact('products', 'reasons'));
    }

    public function store(StockAdjustmentRequest $request): RedirectResponse
    {
        DB::beginTransaction();

        try {
            // Lock product to ensure accurate balance
            $product = Product::where('id', $request->product_id)->lockForUpdate()->firstOrFail();

            $previousQuantity = $product->current_stock;
            $qtyChange = (int) $request->quantity_change;
            $type = $request->type; // 'addition' or 'subtraction'

            if ($type === 'subtraction') {
                if ($previousQuantity < $qtyChange) {
                    DB::rollBack();
                    return back()->withInput()->with('error', "Cannot subtract {$qtyChange} units. Current stock is only {$previousQuantity}. Negative stock is prohibited.");
                }
                $newQuantity = $previousQuantity - $qtyChange;
                $signedChange = -$qtyChange;
            } else {
                $newQuantity = $previousQuantity + $qtyChange;
                $signedChange = $qtyChange;
            }

            $adjNo = 'ADJ-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

            $adjustment = StockAdjustment::create([
                'adjustment_no' => $adjNo,
                'product_id' => $product->id,
                'type' => $type,
                'previous_quantity' => $previousQuantity,
                'quantity_change' => $qtyChange,
                'new_quantity' => $newQuantity,
                'reason' => $request->reason,
                'notes' => $request->notes,
                'user_id' => Auth::id(),
            ]);

            // Update product current stock
            $product->current_stock = $newQuantity;
            $product->save();

            // Trace in stock_movements
            StockMovement::create([
                'product_id' => $product->id,
                'type' => 'adjustment',
                'reference_id' => $adjustment->id,
                'reference_no' => $adjustment->adjustment_no,
                'previous_stock' => $previousQuantity,
                'quantity_change' => $signedChange,
                'new_stock' => $newQuantity,
                'user_id' => Auth::id(),
                'notes' => "Adjustment [{$request->reason}]: " . ($request->notes ?: 'Manual correction'),
            ]);

            DB::commit();

            return redirect()->route('adjustments.index')
                ->with('success', "Stock adjustment '{$adjustment->adjustment_no}' applied successfully.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error applying adjustment: ' . $e->getMessage());
        }
    }

    public function show(StockAdjustment $adjustment): View
    {
        $adjustment->load(['product.category', 'user']);

        return view('adjustments.show', compact('adjustment'));
    }
}
