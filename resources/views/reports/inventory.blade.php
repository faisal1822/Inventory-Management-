@extends('layouts.app')

@section('title', 'Stock Valuation & Inventory Report')
@section('page_title', 'Inventory Valuation & Stock Status')
@section('page_subtitle', 'Cost basis inventory appraisal, retail valuation, and reorder levels')

@section('content')
<!-- Filter Toolbar -->
<div class="card mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('reports.inventory') }}" class="row g-2 align-items-center">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Search by name, SKU..." value="{{ $search }}">
            </div>

            <div class="col-sm-6 col-md-3">
                <select name="category_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Categories</option>
                    @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ $categoryId == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="col-sm-6 col-md-3">
                <select name="stock_filter" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Stock Levels</option>
                    <option value="low" {{ $stockFilter === 'low' ? 'selected' : '' }}>Low Stock Items Only</option>
                    <option value="out" {{ $stockFilter === 'out' ? 'selected' : '' }}>Out of Stock Only</option>
                </select>
            </div>

            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary w-100">Filter</button>
                <a href="{{ request()->fullUrlWithQuery(['export' => 'csv']) }}" class="btn btn-sm btn-outline-success w-100">
                    <i class="bi bi-file-earmark-spreadsheet me-1"></i> Export CSV
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Valuation Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card p-3">
            <span class="text-muted small fw-semibold">Total Stock Units</span>
            <h3 class="fw-bold my-1 text-dark">{{ number_format($totalStockUnits) }}</h3>
            <small class="text-muted">Total inventory on hand</small>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card p-3">
            <span class="text-muted small fw-semibold">Inventory Valuation (Cost Basis)</span>
            <h3 class="fw-bold my-1 text-primary">{{ \App\Models\Setting::get('currency_symbol', '৳') }}{{ number_format($totalCostValuation, 2) }}</h3>
            <small class="text-muted">Total capital tied in stock</small>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card p-3">
            <span class="text-muted small fw-semibold">Total Potential Retail Value</span>
            <h3 class="fw-bold my-1 text-success">{{ \App\Models\Setting::get('currency_symbol', '৳') }}{{ number_format($totalRetailValuation, 2) }}</h3>
            <small class="text-muted">Expected retail turnover</small>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card p-3">
            <span class="text-muted small fw-semibold">Potential Gross Profit</span>
            <h3 class="fw-bold my-1 text-info">{{ \App\Models\Setting::get('currency_symbol', '৳') }}{{ number_format($potentialProfit, 2) }}</h3>
            <small class="text-muted">Retail Value - Cost Value</small>
        </div>
    </div>
</div>

<!-- Costing Method Note -->
<div class="alert alert-light border p-3 mb-4 small">
    <div class="d-flex align-items-center gap-2 mb-1">
        <i class="bi bi-info-circle-fill text-primary"></i>
        <strong>Documented Costing Basis:</strong>
    </div>
    <span>
        This inventory valuation calculates inventory asset value using the <strong>Standard Purchase Cost Basis</strong> (<code>Product Current Stock × Product Unit Purchase Price</code>). Potential Gross Profit represents the unrealized margin if all currently stocked units are sold at their catalog retail price.
    </span>
</div>

<!-- Valuation Table -->
<div class="card mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <span class="fw-bold">Itemized Inventory Valuation</span>
        <span class="badge bg-light text-dark border">{{ $products->total() }} products</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Product & SKU</th>
                    <th>Category</th>
                    <th class="text-center">Stock</th>
                    <th class="text-end">Unit Cost</th>
                    <th class="text-end">Unit Price</th>
                    <th class="text-end">Cost Valuation</th>
                    <th class="text-end">Retail Valuation</th>
                    <th class="text-end">Potential Margin</th>
                    <th class="text-center">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                @php
                    $costVal = $product->purchase_price * $product->current_stock;
                    $retailVal = $product->selling_price * $product->current_stock;
                    $margin = $retailVal - $costVal;
                @endphp
                <tr>
                    <td>
                        <a href="{{ route('products.show', $product) }}" class="fw-semibold text-dark text-decoration-none">
                            {{ $product->name }}
                        </a>
                        <div class="font-monospace text-muted small">{{ $product->sku }}</div>
                    </td>
                    <td>
                        <span class="badge bg-light text-secondary border">{{ $product->category ? $product->category->name : 'N/A' }}</span>
                    </td>
                    <td class="text-center font-monospace fw-bold {{ $product->isOutOfStock() ? 'text-danger' : ($product->isLowStock() ? 'text-warning' : 'text-success') }}">
                        {{ $product->current_stock }}
                    </td>
                    <td class="text-end font-monospace text-muted">
                        {{ \App\Models\Setting::get('currency_symbol', '৳') }}{{ number_format($product->purchase_price, 2) }}
                    </td>
                    <td class="text-end font-monospace">
                        {{ \App\Models\Setting::get('currency_symbol', '৳') }}{{ number_format($product->selling_price, 2) }}
                    </td>
                    <td class="text-end font-monospace fw-bold text-primary">
                        {{ \App\Models\Setting::get('currency_symbol', '৳') }}{{ number_format($costVal, 2) }}
                    </td>
                    <td class="text-end font-monospace fw-bold text-dark">
                        {{ \App\Models\Setting::get('currency_symbol', '৳') }}{{ number_format($retailVal, 2) }}
                    </td>
                    <td class="text-end font-monospace text-success fw-bold">
                        {{ \App\Models\Setting::get('currency_symbol', '৳') }}{{ number_format($margin, 2) }}
                    </td>
                    <td class="text-center">
                        @if($product->isOutOfStock())
                        <span class="badge badge-subtle-danger">Out</span>
                        @elseif($product->isLowStock())
                        <span class="badge badge-subtle-warning">Low</span>
                        @else
                        <span class="badge badge-subtle-success">OK</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center py-5 text-muted">
                        No products match the selected inventory criteria.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($products->hasPages())
    <div class="card-footer bg-white border-top py-3">
        {{ $products->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>
@endsection
