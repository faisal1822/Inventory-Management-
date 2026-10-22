@extends('layouts.app')

@section('title', 'Best-Selling Products')
@section('page_title', 'Top Performing Products')
@section('page_subtitle', 'Identify high-demand stock items, revenue drivers, and top profit generators')

@section('content')
<div class="card mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <span class="fw-bold"><i class="bi bi-trophy-fill text-warning me-2"></i>Top 10 Products by Sales Volume</span>
        <a href="{{ route('reports.sales') }}" class="btn btn-sm btn-light border">View Sales Report</a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th style="width: 50px;" class="text-center">Rank</th>
                    <th>Product & SKU</th>
                    <th>Category</th>
                    <th class="text-center">Total Quantity Sold</th>
                    <th class="text-end">Total Revenue Generated</th>
                    <th class="text-end">Calculated Profit (Margin)</th>
                    <th class="text-center">Current Stock</th>
                </tr>
            </thead>
            <tbody>
                @forelse($topProducts as $index => $item)
                <tr>
                    <td class="text-center">
                        @if($index === 0)
                        <span class="badge rounded-pill bg-warning text-dark fs-6 px-2"><i class="bi bi-trophy-fill"></i> #1</span>
                        @elseif($index === 1)
                        <span class="badge rounded-pill bg-secondary text-white fs-6 px-2">#2</span>
                        @elseif($index === 2)
                        <span class="badge rounded-pill bg-danger text-white fs-6 px-2">#3</span>
                        @else
                        <span class="fw-bold text-muted">#{{ $index + 1 }}</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('products.show', $item->product) }}" class="fw-semibold text-dark text-decoration-none">
                            {{ $item->product ? $item->product->name : 'Deleted Product' }}
                        </a>
                        <div class="font-monospace text-muted small">{{ $item->product ? $item->product->sku : '' }}</div>
                    </td>
                    <td>
                        <span class="badge bg-light text-secondary border">
                            {{ $item->product && $item->product->category ? $item->product->category->name : 'N/A' }}
                        </span>
                    </td>
                    <td class="text-center font-monospace fw-bold fs-6 text-primary">
                        {{ number_format($item->total_sold_qty) }} units
                    </td>
                    <td class="text-end font-monospace fw-bold text-dark">
                        {{ \App\Models\Setting::get('currency_symbol', '৳') }}{{ number_format($item->total_revenue, 2) }}
                    </td>
                    <td class="text-end font-monospace fw-bold text-success">
                        {{ \App\Models\Setting::get('currency_symbol', '৳') }}{{ number_format($item->total_profit, 2) }}
                    </td>
                    <td class="text-center">
                        @if($item->product)
                        <span class="badge {{ $item->product->isLowStock() ? 'badge-subtle-warning' : ($item->product->isOutOfStock() ? 'badge-subtle-danger' : 'badge-subtle-success') }}">
                            {{ $item->product->current_stock }} in stock
                        </span>
                        @else
                        -
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-5 text-muted">
                        <i class="bi bi-cart-x fs-1 text-secondary d-block mb-2"></i>
                        No sales transaction records available yet to rank best-selling items.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
