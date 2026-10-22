@extends('layouts.app')

@section('title', 'Products & Stock')
@section('page_title', 'Product Catalog & Inventory')
@section('page_subtitle', 'Monitor current stock, pricing, SKU codes, and reorder levels')

@section('content')
<!-- Filter & Search Toolbar -->
<div class="card mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('products.index') }}" class="row g-2 align-items-center">
            <div class="col-md-4">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control border-start-0" placeholder="Search by name, SKU, or barcode..." value="{{ $search }}">
                </div>
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
                <select name="stock_status" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Stock Statuses</option>
                    <option value="in_stock" {{ $stockStatus == 'in_stock' ? 'selected' : '' }}>In Stock (>0)</option>
                    <option value="low_stock" {{ $stockStatus == 'low_stock' ? 'selected' : '' }}>Low Stock Alert</option>
                    <option value="out_of_stock" {{ $stockStatus == 'out_of_stock' ? 'selected' : '' }}>Out of Stock (0)</option>
                </select>
            </div>

            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary w-100">Filter</button>
                @if($search || $categoryId || $stockStatus)
                <a href="{{ route('products.index') }}" class="btn btn-sm btn-light border" title="Reset Filters"><i class="bi bi-x-lg"></i></a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Products Table -->
<div class="card mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <span class="fw-bold">Product Listing ({{ $products->total() }} items)</span>
        @if(Auth::user()->isAdmin())
        <a href="{{ route('products.create') }}" class="btn btn-sm btn-primary d-flex align-items-center gap-1">
            <i class="bi bi-plus-lg"></i>
            <span>Add New Product</span>
        </a>
        @endif
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th style="width: 60px;">Image</th>
                    <th>Product & SKU</th>
                    <th>Category</th>
                    <th class="text-end">Cost Price</th>
                    <th class="text-end">Selling Price</th>
                    <th class="text-center">Current Stock</th>
                    <th class="text-center">Status</th>
                    <th class="text-end" style="width: 150px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                <tr>
                    <td>
                        <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="rounded border object-fit-cover" style="width: 44px; height: 44px;">
                    </td>
                    <td>
                        <a href="{{ route('products.show', $product) }}" class="fw-semibold text-decoration-none text-dark d-block">
                            {{ $product->name }}
                        </a>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-light text-muted border font-monospace" style="font-size: 0.72rem;">{{ $product->sku }}</span>
                            @if($product->barcode)
                            <small class="text-muted" style="font-size: 0.72rem;"><i class="bi bi-upc-scan me-1"></i>{{ $product->barcode }}</small>
                            @endif
                        </div>
                    </td>
                    <td>
                        <span class="badge bg-light text-secondary border">{{ $product->category ? $product->category->name : 'Uncategorized' }}</span>
                    </td>
                    <td class="text-end text-muted font-monospace">
                        {{ \App\Models\Setting::get('currency_symbol', '৳') }}{{ number_format($product->purchase_price, 2) }}
                    </td>
                    <td class="text-end fw-bold text-dark font-monospace">
                        {{ \App\Models\Setting::get('currency_symbol', '৳') }}{{ number_format($product->selling_price, 2) }}
                    </td>
                    <td class="text-center">
                        <span class="fw-bold fs-6 {{ $product->isOutOfStock() ? 'text-danger' : ($product->isLowStock() ? 'text-warning' : 'text-success') }}">
                            {{ $product->current_stock }}
                        </span>
                        <small class="text-muted d-block" style="font-size: 0.7rem;">Min: {{ $product->min_stock_threshold }}</small>
                    </td>
                    <td class="text-center">
                        @if($product->isOutOfStock())
                        <span class="badge badge-subtle-danger">Out of Stock</span>
                        @elseif($product->isLowStock())
                        <span class="badge badge-subtle-warning"><i class="bi bi-exclamation-circle me-1"></i>Low Stock</span>
                        @else
                        <span class="badge badge-subtle-success">In Stock</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <a href="{{ route('products.show', $product) }}" class="btn btn-sm btn-light border" title="View Details">
                            <i class="bi bi-eye"></i>
                        </a>
                        @if(Auth::user()->isAdmin())
                        <a href="{{ route('products.edit', $product) }}" class="btn btn-sm btn-light border" title="Edit Product">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <button type="button" class="btn btn-sm btn-light border text-danger" title="Delete Product"
                            onclick="confirmDelete('{{ route('products.destroy', $product) }}', 'Are you sure you want to delete product \'{{ $product->name }}\'? Products with sales/purchases cannot be deleted.')">
                            <i class="bi bi-trash"></i>
                        </button>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-5 text-muted">
                        <i class="bi bi-boxes fs-1 text-secondary d-block mb-2"></i>
                        No products found matching your search criteria.
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
