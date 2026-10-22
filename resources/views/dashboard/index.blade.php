@extends('layouts.app')

@section('title', 'Dashboard')
@section('page_title', 'Business Dashboard')
@section('page_subtitle', 'Live stock overview, sales insights, and operational metrics')

@section('content')
<div class="row g-3 mb-4">
    <!-- Total Products -->
    <div class="col-sm-6 col-xl-3">
        <div class="card h-100 p-3">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-semibold">Total Products</span>
                <div class="bg-primary bg-opacity-10 text-primary p-2 rounded-3">
                    <i class="bi bi-boxes fs-5"></i>
                </div>
            </div>
            <h3 class="fw-bold mb-1">{{ number_format($totalProducts) }}</h3>
            <div class="d-flex align-items-center text-muted small">
                <span>{{ number_format($totalStockQty) }} units across warehouse</span>
            </div>
        </div>
    </div>

    <!-- Today's Sales -->
    <div class="col-sm-6 col-xl-3">
        <div class="card h-100 p-3">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-semibold">Today's Sales</span>
                <div class="bg-success bg-opacity-10 text-success p-2 rounded-3">
                    <i class="bi bi-cash-stack fs-5"></i>
                </div>
            </div>
            <h3 class="fw-bold mb-1">{{ \App\Models\Setting::get('currency_symbol', '৳') }}{{ number_format($todaySalesAmount, 2) }}</h3>
            <div class="d-flex align-items-center text-success small">
                <i class="bi bi-cart-check me-1"></i>
                <span>{{ $todaySalesCount }} completed orders today</span>
            </div>
        </div>
    </div>

    <!-- Total Purchases -->
    <div class="col-sm-6 col-xl-3">
        <div class="card h-100 p-3">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-semibold">Total Purchases</span>
                <div class="bg-info bg-opacity-10 text-info p-2 rounded-3">
                    <i class="bi bi-truck fs-5"></i>
                </div>
            </div>
            <h3 class="fw-bold mb-1">{{ \App\Models\Setting::get('currency_symbol', '৳') }}{{ number_format($totalPurchaseValue, 2) }}</h3>
            <div class="d-flex align-items-center text-muted small">
                <span>{{ $totalSuppliers }} active registered suppliers</span>
            </div>
        </div>
    </div>

    <!-- Low Stock Alert -->
    <div class="col-sm-6 col-xl-3">
        <div class="card h-100 p-3">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-semibold">Low-Stock Warnings</span>
                <div class="bg-warning bg-opacity-10 text-warning p-2 rounded-3">
                    <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                </div>
            </div>
            <h3 class="fw-bold mb-1 {{ $lowStockCount > 0 ? 'text-danger' : 'text-success' }}">{{ $lowStockCount }}</h3>
            <div class="d-flex align-items-center small">
                @if($lowStockCount > 0)
                <span class="text-danger fw-semibold"><i class="bi bi-bell-fill me-1"></i>Items need immediate restock</span>
                @else
                <span class="text-success"><i class="bi bi-check2-all me-1"></i>Healthy inventory thresholds</span>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Secondary Statistics Row: Valuation & Categories -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card p-3 bg-white">
            <div class="d-flex align-items-center gap-3">
                <div class="p-3 rounded-circle bg-light text-primary">
                    <i class="bi bi-shield-check fs-4"></i>
                </div>
                <div>
                    <span class="text-muted small">Total Cost Valuation</span>
                    <h4 class="fw-bold mb-0 text-primary">{{ \App\Models\Setting::get('currency_symbol', '৳') }}{{ number_format($inventoryValuation, 2) }}</h4>
                    <small class="text-muted" style="font-size: 0.75rem;">Based on purchase cost basis</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card p-3 bg-white">
            <div class="d-flex align-items-center gap-3">
                <div class="p-3 rounded-circle bg-light text-success">
                    <i class="bi bi-tags fs-4"></i>
                </div>
                <div>
                    <span class="text-muted small">Product Categories</span>
                    <h4 class="fw-bold mb-0 text-dark">{{ $totalCategories }}</h4>
                    <small class="text-muted" style="font-size: 0.75rem;">Organized catalog taxonomy</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card p-3 bg-white">
            <div class="d-flex align-items-center gap-3">
                <div class="p-3 rounded-circle bg-light text-danger">
                    <i class="bi bi-x-octagon fs-4"></i>
                </div>
                <div>
                    <span class="text-muted small">Out-of-Stock Items</span>
                    <h4 class="fw-bold mb-0 {{ $outOfStockCount > 0 ? 'text-danger' : 'text-muted' }}">{{ $outOfStockCount }}</h4>
                    <small class="text-muted" style="font-size: 0.75rem;">Products with zero stock balance</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row g-3 mb-4">
    <!-- 7-Day Sales Trend Chart -->
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="fw-bold"><i class="bi bi-graph-up me-2 text-primary"></i>Sales Revenue Trend (Last 7 Days)</span>
                <span class="badge bg-light text-dark border">Daily Trend</span>
            </div>
            <div class="card-body">
                <div style="height: 280px; position: relative;">
                    <canvas id="salesTrendChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Stock by Category Doughnut Chart -->
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="fw-bold"><i class="bi bi-pie-chart-fill me-2 text-info"></i>Stock by Category</span>
                <span class="badge bg-light text-dark border">Units</span>
            </div>
            <div class="card-body d-flex flex-column justify-content-center">
                <div style="height: 250px; position: relative;">
                    <canvas id="categoryStockChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Low Stock Warnings & Recent Transactions Row -->
<div class="row g-3">
    <!-- Low Stock Alert Table -->
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="fw-bold text-danger">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>Low-Stock Alert Items
                </span>
                <a href="{{ route('products.index', ['stock_status' => 'low_stock']) }}" class="btn btn-sm btn-outline-danger">View All</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Category</th>
                            <th class="text-center">Current</th>
                            <th class="text-center">Threshold</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($lowStockProducts as $lowProduct)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $lowProduct->name }}</div>
                                <small class="text-muted font-monospace">{{ $lowProduct->sku }}</small>
                            </td>
                            <td><span class="badge bg-light text-secondary border">{{ $lowProduct->category ? $lowProduct->category->name : 'N/A' }}</span></td>
                            <td class="text-center">
                                <span class="badge badge-subtle-danger px-2 py-1">{{ $lowProduct->current_stock }} units</span>
                            </td>
                            <td class="text-center text-muted small">{{ $lowProduct->min_stock_threshold }} units</td>
                            <td class="text-end">
                                @if(Auth::user()->isAdmin())
                                <a href="{{ route('purchases.create') }}" class="btn btn-sm btn-outline-primary" title="Create Purchase Order">
                                    <i class="bi bi-cart-plus"></i> Reorder
                                </a>
                                @else
                                <span class="badge bg-secondary">Reported</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">
                                <i class="bi bi-check-circle fs-3 text-success d-block mb-1"></i>
                                All products are above minimum inventory thresholds.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Recent Sales Table -->
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="fw-bold text-dark">
                    <i class="bi bi-receipt-cutoff me-2 text-success"></i>Recent Sales Invoices
                </span>
                <a href="{{ route('sales.index') }}" class="btn btn-sm btn-outline-secondary">View All</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Invoice</th>
                            <th>Customer</th>
                            <th>Total</th>
                            <th>Payment</th>
                            <th class="text-end">Receipt</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentSales as $sale)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $sale->invoice_no }}</div>
                                <small class="text-muted">{{ $sale->sale_date->format('M d, Y') }}</small>
                            </td>
                            <td>
                                <div>{{ $sale->customer_name }}</div>
                                <small class="text-muted">{{ $sale->user ? $sale->user->name : '' }}</small>
                            </td>
                            <td class="fw-bold text-success">
                                {{ \App\Models\Setting::get('currency_symbol', '৳') }}{{ number_format($sale->grand_total, 2) }}
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    {{ ucfirst(str_replace('_', ' ', $sale->payment_method)) }}
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('sales.show', $sale) }}" class="btn btn-sm btn-light border" title="View Invoice">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('sales.print', $sale) }}" target="_blank" class="btn btn-sm btn-light border" title="Print Invoice">
                                    <i class="bi bi-printer"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">
                                No sales transactions recorded yet.
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

@push('scripts')
<script>
    // 1. Sales Trend Line Chart
    const salesCtx = document.getElementById('salesTrendChart').getContext('2d');
    new Chart(salesCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($salesTrendLabels) !!},
            datasets: [{
                label: 'Sales Revenue ({{ \App\Models\Setting::get("currency_symbol", "৳") }})',
                data: {!! json_encode($salesTrendValues) !!},
                borderColor: '#2563EB',
                backgroundColor: 'rgba(37, 99, 235, 0.08)',
                fill: true,
                tension: 0.35,
                pointBackgroundColor: '#2563EB',
                pointRadius: 4,
                pointHoverRadius: 6,
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return ' Sales: {{ \App\Models\Setting::get("currency_symbol", "৳") }}' + context.parsed.y.toLocaleString();
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: '#F1F5F9'
                    },
                    ticks: {
                        callback: function(value) {
                            return '{{ \App\Models\Setting::get("currency_symbol", "৳") }}' + value;
                        }
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });

    // 2. Category Stock Doughnut Chart
    const categoryCtx = document.getElementById('categoryStockChart').getContext('2d');
    new Chart(categoryCtx, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($categoryLabels) !!},
            datasets: [{
                data: {!! json_encode($categoryStockValues) !!},
                backgroundColor: [
                    '#3B82F6', '#10B981', '#F59E0B', '#8B5CF6', '#EC4899', '#06B6D4', '#64748B'
                ],
                borderWidth: 2,
                borderColor: '#FFFFFF'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        boxWidth: 12,
                        padding: 12,
                        font: {
                            size: 11
                        }
                    }
                }
            },
            cutout: '65%'
        }
    });
</script>
@endpush
