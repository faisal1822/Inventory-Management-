<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $today = Carbon::today()->toDateString();
        $thisMonth = Carbon::now()->startOfMonth()->toDateString();

        // 1. Core Summary Metrics
        $totalProducts = Product::count();
        $totalCategories = Category::count();
        $totalSuppliers = Supplier::count();
        $totalStockQty = (int) Product::sum('current_stock');

        // Today's Sales
        $todaySalesAmount = (float) Sale::whereDate('sale_date', $today)->sum('grand_total');
        $todaySalesCount = Sale::whereDate('sale_date', $today)->count();

        // Total Purchase Value
        $totalPurchaseValue = (float) Purchase::sum('grand_total');

        // Low stock products
        $lowStockProducts = Product::with('category')
            ->lowStock()
            ->orderBy('current_stock', 'asc')
            ->get();
        $lowStockCount = $lowStockProducts->count();

        // Out of stock count
        $outOfStockCount = Product::outOfStock()->count();

        // Inventory valuation based on purchase cost
        $inventoryValuation = (float) Product::select(DB::raw('SUM(purchase_price * current_stock) as valuation'))->value('valuation') ?? 0.00;

        // Recent purchases and sales
        $recentPurchases = Purchase::with(['supplier', 'user'])
            ->latest('purchase_date')
            ->latest('id')
            ->take(5)
            ->get();

        $recentSales = Sale::with(['user', 'items.product'])
            ->latest('sale_date')
            ->latest('id')
            ->take(5)
            ->get();

        // 2. Chart Data: Last 7 Days Sales Trend
        $salesTrendLabels = [];
        $salesTrendValues = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $dateStr = $date->toDateString();
            $salesTrendLabels[] = $date->format('M d');
            $salesTrendValues[] = (float) Sale::whereDate('sale_date', $dateStr)->sum('grand_total');
        }

        // 3. Chart Data: Stock Distribution by Category
        $categoriesWithStock = Category::withSum('products as total_stock', 'current_stock')
            ->get()
            ->filter(fn($c) => ($c->total_stock ?? 0) > 0);

        $categoryLabels = $categoriesWithStock->pluck('name')->toArray();
        $categoryStockValues = $categoriesWithStock->pluck('total_stock')->map(fn($v) => (int) $v)->toArray();

        return view('dashboard.index', compact(
            'totalProducts',
            'totalCategories',
            'totalSuppliers',
            'totalStockQty',
            'todaySalesAmount',
            'todaySalesCount',
            'totalPurchaseValue',
            'lowStockCount',
            'lowStockProducts',
            'outOfStockCount',
            'inventoryValuation',
            'recentPurchases',
            'recentSales',
            'salesTrendLabels',
            'salesTrendValues',
            'categoryLabels',
            'categoryStockValues'
        ));
    }
}
