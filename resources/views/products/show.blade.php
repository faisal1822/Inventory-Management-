@extends('layouts.app')

@section('title', 'Product: ' . $product->name)
@section('page_title', 'Product Details')
@section('page_subtitle', 'SKU: ' . $product->sku)

@section('content')
<div class="row g-4 mb-4">
    <!-- Left Column: Product Info Card -->
    <div class="col-lg-4">
        <div class="card h-100 p-4">
            <div class="text-center mb-4">
                <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="rounded-3 border shadow-sm object-fit-cover mb-3" style="width: 140px; height: 140px;">
                <h5 class="fw-bold mb-1">{{ $product->name }}</h5>
                <span class="badge bg-light text-primary border font-monospace">{{ $product->sku }}</span>
            </div>

            <div class="border-top pt-3 mb-3">
                <div class="d-flex justify-content-between py-1 border-bottom">
                    <span class="text-muted small">Category</span>
                    <span class="fw-semibold text-dark">{{ $product->category ? $product->category->name : 'N/A' }}</span>
                </div>
                <div class="d-flex justify-content-between py-1 border-bottom">
                    <span class="text-muted small">Preferred Supplier</span>
                    <span class="fw-semibold text-dark">{{ $product->supplier ? $product->supplier->name : 'None' }}</span>
                </div>
                <div class="d-flex justify-content-between py-1 border-bottom">
                    <span class="text-muted small">Barcode</span>
                    <span class="font-monospace text-dark">{{ $product->barcode ?: 'None' }}</span>
                </div>
                <div class="d-flex justify-content-between py-1 border-bottom">
                    <span class="text-muted small">Inventory Status</span>
                    <span>
                        @if($product->isOutOfStock())
                        <span class="badge badge-subtle-danger">Out of Stock</span>
                        @elseif($product->isLowStock())
                        <span class="badge badge-subtle-warning">Low Stock Warning</span>
                        @else
                        <span class="badge badge-subtle-success">In Stock</span>
                        @endif
                    </span>
                </div>
            </div>

            @if($product->description)
            <div class="mb-4">
                <span class="text-muted small fw-semibold d-block mb-1">Description</span>
                <p class="text-muted small mb-0">{{ $product->description }}</p>
            </div>
            @endif

            <div class="mt-auto pt-3 border-top d-flex gap-2">
                @if(Auth::user()->isAdmin())
                <a href="{{ route('products.edit', $product) }}" class="btn btn-sm btn-outline-primary w-100">
                    <i class="bi bi-pencil me-1"></i> Edit
                </a>
                <a href="{{ route('adjustments.create') }}" class="btn btn-sm btn-outline-warning w-100">
                    <i class="bi bi-arrow-left-right me-1"></i> Adjust
                </a>
                @endif
                <a href="{{ route('products.index') }}" class="btn btn-sm btn-light border w-100">Back</a>
            </div>
        </div>
    </div>

    <!-- Right Column: Metrics & Movement History -->
    <div class="col-lg-8">
        <!-- Stock & Valuation Cards -->
        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-md-3">
                <div class="card p-3 text-center h-100">
                    <span class="text-muted small fw-semibold">Current Stock</span>
                    <h3 class="fw-bold my-1 {{ $product->isOutOfStock() ? 'text-danger' : 'text-primary' }}">{{ $product->current_stock }}</h3>
                    <small class="text-muted">Threshold: {{ $product->min_stock_threshold }}</small>
                </div>
            </div>
            <div class="col-sm-6 col-md-3">
                <div class="card p-3 text-center h-100">
                    <span class="text-muted small fw-semibold">Cost Price</span>
                    <h4 class="fw-bold my-1 text-dark">{{ \App\Models\Setting::get('currency_symbol', '৳') }}{{ number_format($product->purchase_price, 2) }}</h4>
                    <small class="text-muted">Unit purchase cost</small>
                </div>
            </div>
            <div class="col-sm-6 col-md-3">
                <div class="card p-3 text-center h-100">
                    <span class="text-muted small fw-semibold">Selling Price</span>
                    <h4 class="fw-bold my-1 text-success">{{ \App\Models\Setting::get('currency_symbol', '৳') }}{{ number_format($product->selling_price, 2) }}</h4>
                    <small class="text-success">Margin: {{ \App\Models\Setting::get('currency_symbol', '৳') }}{{ number_format($product->selling_price - $product->purchase_price, 2) }}</small>
                </div>
            </div>
            <div class="col-sm-6 col-md-3">
                <div class="card p-3 text-center h-100">
                    <span class="text-muted small fw-semibold">Cost Valuation</span>
                    <h4 class="fw-bold my-1 text-primary">{{ \App\Models\Setting::get('currency_symbol', '৳') }}{{ number_format($product->purchase_price * $product->current_stock, 2) }}</h4>
                    <small class="text-muted">Stock × Unit Cost</small>
                </div>
            </div>
        </div>

        <!-- Stock Movements History Table -->
        <div class="card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span class="fw-bold"><i class="bi bi-clock-history me-2 text-primary"></i>Stock Movement History (Latest Activity)</span>
                <span class="badge bg-light text-dark border">Audit Trail</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Date & Time</th>
                            <th>Type</th>
                            <th>Reference</th>
                            <th class="text-center">Prev</th>
                            <th class="text-center">Change</th>
                            <th class="text-center">Balance</th>
                            <th>Logged By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($product->stockMovements as $movement)
                        <tr>
                            <td>
                                <small class="text-muted">{{ $movement->created_at->format('M d, Y h:i A') }}</small>
                            </td>
                            <td>
                                @if($movement->type === 'purchase')
                                <span class="badge badge-subtle-primary">Purchase Intake</span>
                                @elseif($movement->type === 'sale')
                                <span class="badge badge-subtle-success">Sold (Invoice)</span>
                                @else
                                <span class="badge badge-subtle-warning">Adjustment</span>
                                @endif
                            </td>
                            <td>
                                <span class="font-monospace text-dark small">{{ $movement->reference_no ?: '-' }}</span>
                            </td>
                            <td class="text-center text-muted small">{{ $movement->previous_stock }}</td>
                            <td class="text-center fw-bold {{ $movement->quantity_change > 0 ? 'text-success' : 'text-danger' }}">
                                {{ $movement->quantity_change > 0 ? '+' : '' }}{{ $movement->quantity_change }}
                            </td>
                            <td class="text-center fw-bold">{{ $movement->new_stock }}</td>
                            <td>
                                <small class="text-muted">{{ $movement->user ? $movement->user->name : 'System' }}</small>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                No stock movements recorded for this product yet.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
