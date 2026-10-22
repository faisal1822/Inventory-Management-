<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Setting;
use App\Models\StockMovement;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    /**
     * Sales Report (Daily, Weekly, Monthly, Custom Range)
     */
    public function sales(Request $request): View|StreamedResponse
    {
        $period = $request->query('period', 'monthly');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $paymentMethod = $request->query('payment_method');
        $export = $request->query('export');

        $now = Carbon::now();

        if ($period === 'daily') {
            $startDate = $startDate ?: $now->toDateString();
            $endDate = $endDate ?: $now->toDateString();
        } elseif ($period === 'weekly') {
            $startDate = $startDate ?: $now->startOfWeek()->toDateString();
            $endDate = $endDate ?: $now->endOfWeek()->toDateString();
        } elseif ($period === 'monthly') {
            $startDate = $startDate ?: $now->startOfMonth()->toDateString();
            $endDate = $endDate ?: $now->endOfMonth()->toDateString();
        }

        $query = Sale::with(['user', 'items.product']);

        if ($startDate) {
            $query->whereDate('sale_date', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('sale_date', '<=', $endDate);
        }
        if ($paymentMethod) {
            $query->where('payment_method', $paymentMethod);
        }

        // CSV Export Handler
        if ($export === 'csv') {
            $salesForExport = (clone $query)->orderBy('sale_date', 'desc')->get();
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="sales_report_' . date('Ymd_His') . '.csv"',
            ];

            return response()->stream(function () use ($salesForExport) {
                $file = fopen('php://output', 'w');
                fputcsv($file, ['Invoice No', 'Sale Date', 'Customer', 'Items Count', 'Subtotal', 'Discount', 'Grand Total', 'Paid Amount', 'Payment Method', 'Sold By']);

                foreach ($salesForExport as $s) {
                    fputcsv($file, [
                        $s->invoice_no,
                        $s->sale_date->format('Y-m-d'),
                        $s->customer_name,
                        $s->items->sum('quantity'),
                        number_format($s->subtotal, 2, '.', ''),
                        number_format($s->discount, 2, '.', ''),
                        number_format($s->grand_total, 2, '.', ''),
                        number_format($s->paid_amount, 2, '.', ''),
                        ucfirst(str_replace('_', ' ', $s->payment_method)),
                        $s->user ? $s->user->name : 'N/A',
                    ]);
                }
                fclose($file);
            }, 200, $headers);
        }

        $sales = (clone $query)->latest('sale_date')->latest('id')->paginate(15)->withQueryString();

        // Calculate aggregates for current filtered set
        $totalSalesCount = (clone $query)->count();
        $totalRevenue = (clone $query)->sum('grand_total');
        $totalDiscount = (clone $query)->sum('discount');
        
        // Accurate COGS and gross profit calculation
        $salesList = (clone $query)->with('items')->get();
        $totalCostOfGoods = 0;
        foreach ($salesList as $saleItem) {
            foreach ($saleItem->items as $item) {
                $totalCostOfGoods += ($item->unit_cost * $item->quantity);
            }
        }
        $grossProfit = max(0, $totalRevenue - $totalCostOfGoods);

        // Sales by payment method breakdown
        $paymentBreakdown = (clone $query)
            ->select('payment_method', DB::raw('COUNT(*) as count'), DB::raw('SUM(grand_total) as total'))
            ->groupBy('payment_method')
            ->get();

        return view('reports.sales', compact(
            'sales',
            'period',
            'startDate',
            'endDate',
            'paymentMethod',
            'totalSalesCount',
            'totalRevenue',
            'totalDiscount',
            'totalCostOfGoods',
            'grossProfit',
            'paymentBreakdown'
        ));
    }

    /**
     * Purchases Report
     */
    public function purchases(Request $request): View|StreamedResponse
    {
        $supplierId = $request->query('supplier_id');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $export = $request->query('export');

        $query = Purchase::with(['supplier', 'user', 'items.product']);

        if ($supplierId) {
            $query->where('supplier_id', $supplierId);
        }
        if ($startDate) {
            $query->whereDate('purchase_date', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('purchase_date', '<=', $endDate);
        }

        if ($export === 'csv') {
            $data = (clone $query)->orderBy('purchase_date', 'desc')->get();
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="purchases_report_' . date('Ymd_His') . '.csv"',
            ];

            return response()->stream(function () use ($data) {
                $file = fopen('php://output', 'w');
                fputcsv($file, ['Reference No', 'Purchase Date', 'Supplier', 'Items Count', 'Total Quantity', 'Grand Total', 'Created By']);

                foreach ($data as $p) {
                    fputcsv($file, [
                        $p->reference_no,
                        $p->purchase_date->format('Y-m-d'),
                        $p->supplier ? $p->supplier->name : 'N/A',
                        $p->items->count(),
                        $p->total_quantity,
                        number_format($p->grand_total, 2, '.', ''),
                        $p->user ? $p->user->name : 'N/A',
                    ]);
                }
                fclose($file);
            }, 200, $headers);
        }

        $purchases = (clone $query)->latest('purchase_date')->latest('id')->paginate(15)->withQueryString();
        $totalOrders = (clone $query)->count();
        $totalSpent = (clone $query)->sum('grand_total');
        $totalQuantityPurchased = (clone $query)->sum('total_quantity');
        $suppliers = Supplier::active()->orderBy('name')->get();

        return view('reports.purchases', compact(
            'purchases',
            'suppliers',
            'supplierId',
            'startDate',
            'endDate',
            'totalOrders',
            'totalSpent',
            'totalQuantityPurchased'
        ));
    }

    /**
     * Product Stock & Inventory Valuation Report
     */
    public function inventory(Request $request): View|StreamedResponse
    {
        $categoryId = $request->query('category_id');
        $stockFilter = $request->query('stock_filter'); // 'all', 'low', 'out'
        $search = $request->query('search');
        $export = $request->query('export');

        $query = Product::with(['category', 'supplier'])->search($search);

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        if ($stockFilter === 'low') {
            $query->lowStock();
        } elseif ($stockFilter === 'out') {
            $query->outOfStock();
        }

        if ($export === 'csv') {
            $items = (clone $query)->orderBy('name')->get();
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="inventory_valuation_' . date('Ymd_His') . '.csv"',
            ];

            return response()->stream(function () use ($items) {
                $file = fopen('php://output', 'w');
                fputcsv($file, ['SKU', 'Product Name', 'Category', 'Current Stock', 'Purchase Cost (৳)', 'Selling Price (৳)', 'Cost Valuation (৳)', 'Retail Valuation (৳)', 'Potential Margin (৳)', 'Status']);

                foreach ($items as $item) {
                    $costValuation = $item->purchase_price * $item->current_stock;
                    $retailValuation = $item->selling_price * $item->current_stock;
                    $margin = $retailValuation - $costValuation;

                    fputcsv($file, [
                        $item->sku,
                        $item->name,
                        $item->category ? $item->category->name : 'N/A',
                        $item->current_stock,
                        number_format($item->purchase_price, 2, '.', ''),
                        number_format($item->selling_price, 2, '.', ''),
                        number_format($costValuation, 2, '.', ''),
                        number_format($retailValuation, 2, '.', ''),
                        number_format($margin, 2, '.', ''),
                        ucfirst(str_replace('_', ' ', $item->stock_status)),
                    ]);
                }
                fclose($file);
            }, 200, $headers);
        }

        $products = (clone $query)->orderBy('name')->paginate(15)->withQueryString();

        // Overall stats
        $allProducts = Product::all();
        $totalStockUnits = $allProducts->sum('current_stock');
        $totalCostValuation = $allProducts->sum(fn($p) => $p->purchase_price * $p->current_stock);
        $totalRetailValuation = $allProducts->sum(fn($p) => $p->selling_price * $p->current_stock);
        $potentialProfit = max(0, $totalRetailValuation - $totalCostValuation);
        $lowStockCount = Product::lowStock()->count();
        $outOfStockCount = Product::outOfStock()->count();

        $categories = Category::active()->orderBy('name')->get();

        return view('reports.inventory', compact(
            'products',
            'categories',
            'categoryId',
            'stockFilter',
            'search',
            'totalStockUnits',
            'totalCostValuation',
            'totalRetailValuation',
            'potentialProfit',
            'lowStockCount',
            'outOfStockCount'
        ));
    }

    /**
     * Stock Movement Audit Trail Report
     */
    public function movements(Request $request): View|StreamedResponse
    {
        $productId = $request->query('product_id');
        $type = $request->query('type');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $export = $request->query('export');

        $query = StockMovement::with(['product', 'user']);

        if ($productId) {
            $query->where('product_id', $productId);
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

        if ($export === 'csv') {
            $data = (clone $query)->latest('id')->get();
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="stock_movements_' . date('Ymd_His') . '.csv"',
            ];

            return response()->stream(function () use ($data) {
                $file = fopen('php://output', 'w');
                fputcsv($file, ['Date & Time', 'Product', 'SKU', 'Movement Type', 'Reference No', 'Previous Stock', 'Change', 'New Balance', 'Logged By', 'Notes']);

                foreach ($data as $m) {
                    fputcsv($file, [
                        $m->created_at->format('Y-m-d H:i:s'),
                        $m->product ? $m->product->name : 'N/A',
                        $m->product ? $m->product->sku : 'N/A',
                        strtoupper($m->type),
                        $m->reference_no ?: '-',
                        $m->previous_stock,
                        ($m->quantity_change > 0 ? '+' : '') . $m->quantity_change,
                        $m->new_stock,
                        $m->user ? $m->user->name : 'System',
                        $m->notes ?: '-',
                    ]);
                }
                fclose($file);
            }, 200, $headers);
        }

        $movements = (clone $query)->latest('id')->paginate(15)->withQueryString();
        $products = Product::orderBy('name')->get();

        return view('reports.movements', compact('movements', 'products', 'productId', 'type', 'startDate', 'endDate'));
    }

    /**
     * Best Selling Products Report
     */
    public function bestSelling(): View
    {
        $topProducts = SaleItem::select(
            'product_id',
            DB::raw('SUM(quantity) as total_sold_qty'),
            DB::raw('SUM(line_total) as total_revenue'),
            DB::raw('SUM((unit_price - unit_cost) * quantity) as total_profit')
        )
        ->with('product.category')
        ->groupBy('product_id')
        ->orderByDesc('total_sold_qty')
        ->take(10)
        ->get();

        return view('reports.best_selling', compact('topProducts'));
    }
}
