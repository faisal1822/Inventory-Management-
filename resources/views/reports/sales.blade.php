@extends('layouts.app')

@section('title', 'Sales Performance Reports')
@section('page_title', 'Sales & Revenue Analytics')
@section('page_subtitle', 'Daily, weekly, and monthly sales performance and gross margin metrics')

@section('content')
<!-- Filter & Export Toolbar -->
<div class="card mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('reports.sales') }}" class="row g-2 align-items-center">
            <div class="col-sm-6 col-md-2">
                <select name="period" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="daily" {{ $period === 'daily' ? 'selected' : '' }}>Today (Daily)</option>
                    <option value="weekly" {{ $period === 'weekly' ? 'selected' : '' }}>This Week</option>
                    <option value="monthly" {{ $period === 'monthly' ? 'selected' : '' }}>This Month</option>
                    <option value="custom" {{ $period === 'custom' ? 'selected' : '' }}>Custom Date Range</option>
                </select>
            </div>

            <div class="col-sm-6 col-md-2">
                <input type="date" name="start_date" class="form-control form-control-sm" value="{{ $startDate }}" placeholder="From date">
            </div>

            <div class="col-sm-6 col-md-2">
                <input type="date" name="end_date" class="form-control form-control-sm" value="{{ $endDate }}" placeholder="To date">
            </div>

            <div class="col-sm-6 col-md-2">
                <select name="payment_method" class="form-select form-select-sm">
                    <option value="">All Payment Types</option>
                    <option value="cash" {{ $paymentMethod === 'cash' ? 'selected' : '' }}>Cash</option>
                    <option value="card" {{ $paymentMethod === 'card' ? 'selected' : '' }}>Card</option>
                    <option value="mobile_banking" {{ $paymentMethod === 'mobile_banking' ? 'selected' : '' }}>Mobile Banking</option>
                    <option value="bank_transfer" {{ $paymentMethod === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                </select>
            </div>

            <div class="col-md-2">
                <button type="submit" class="btn btn-sm btn-primary w-100">Apply Filter</button>
            </div>

            <div class="col-md-2">
                <a href="{{ request()->fullUrlWithQuery(['export' => 'csv']) }}" class="btn btn-sm btn-outline-success w-100">
                    <i class="bi bi-file-earmark-spreadsheet me-1"></i> Export CSV
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Aggregated Metrics Row -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card p-3">
            <span class="text-muted small fw-semibold">Total Revenue (Gross Sales)</span>
            <h3 class="fw-bold my-1 text-primary">{{ \App\Models\Setting::get('currency_symbol', '৳') }}{{ number_format($totalRevenue, 2) }}</h3>
            <small class="text-muted">{{ $totalSalesCount }} completed invoice(s)</small>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card p-3">
            <span class="text-muted small fw-semibold">Cost of Goods Sold (COGS)</span>
            <h3 class="fw-bold my-1 text-secondary">{{ \App\Models\Setting::get('currency_symbol', '৳') }}{{ number_format($totalCostOfGoods, 2) }}</h3>
            <small class="text-muted">Calculated on unit purchase cost basis</small>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card p-3">
            <span class="text-muted small fw-semibold">Calculated Gross Profit</span>
            <h3 class="fw-bold my-1 text-success">{{ \App\Models\Setting::get('currency_symbol', '৳') }}{{ number_format($grossProfit, 2) }}</h3>
            <small class="text-success"><i class="bi bi-info-circle me-1"></i>Revenue minus product cost basis</small>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card p-3">
            <span class="text-muted small fw-semibold">Discounts Given</span>
            <h3 class="fw-bold my-1 text-danger">{{ \App\Models\Setting::get('currency_symbol', '৳') }}{{ number_format($totalDiscount, 2) }}</h3>
            <small class="text-muted">Customer concessions deducted</small>
        </div>
    </div>
</div>

<!-- Costing Method Transparency Alert -->
<div class="alert alert-light border d-flex align-items-center gap-2 mb-4 p-3 small">
    <i class="bi bi-shield-check text-primary fs-5"></i>
    <div>
        <strong>Transparent Financial Policy:</strong> Sales revenue is never misrepresented as profit. Gross profit displayed above is calculated strictly as <code>Gross Sales Revenue - Cost of Goods Sold (Purchase Price Basis)</code>.
    </div>
</div>

<!-- Sales Table -->
<div class="card mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <span class="fw-bold">Filtered Sales Invoices</span>
        <span class="badge bg-light text-dark border">{{ $sales->total() }} records</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Invoice No</th>
                    <th>Date</th>
                    <th>Customer</th>
                    <th class="text-center">Items Qty</th>
                    <th class="text-end">Subtotal</th>
                    <th class="text-end">Discount</th>
                    <th class="text-end">Grand Total</th>
                    <th>Payment Method</th>
                    <th>Billed By</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sales as $sale)
                <tr>
                    <td>
                        <a href="{{ route('sales.show', $sale) }}" class="fw-semibold text-primary font-monospace text-decoration-none">
                            {{ $sale->invoice_no }}
                        </a>
                    </td>
                    <td>{{ $sale->sale_date->format('M d, Y') }}</td>
                    <td>{{ $sale->customer_name }}</td>
                    <td class="text-center font-monospace">{{ $sale->total_quantity }}</td>
                    <td class="text-end font-monospace text-muted">{{ \App\Models\Setting::get('currency_symbol', '৳') }}{{ number_format($sale->subtotal, 2) }}</td>
                    <td class="text-end font-monospace text-danger">{{ $sale->discount > 0 ? '-' . number_format($sale->discount, 2) : '-' }}</td>
                    <td class="text-end font-monospace fw-bold text-success">{{ \App\Models\Setting::get('currency_symbol', '৳') }}{{ number_format($sale->grand_total, 2) }}</td>
                    <td><span class="badge bg-light text-dark border">{{ ucfirst(str_replace('_', ' ', $sale->payment_method)) }}</span></td>
                    <td><small class="text-muted">{{ $sale->user ? $sale->user->name : 'N/A' }}</small></td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center py-5 text-muted">
                        No sales records found for this timeframe.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($sales->hasPages())
    <div class="card-footer bg-white border-top py-3">
        {{ $sales->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>
@endsection
