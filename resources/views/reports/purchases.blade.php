@extends('layouts.app')

@section('title', 'Purchase Analytics & Reports')
@section('page_title', 'Purchase & Inflow Reports')
@section('page_subtitle', 'Analyze supplier procurement expenditures and stock intake history')

@section('content')
<!-- Filter Toolbar -->
<div class="card mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('reports.purchases') }}" class="row g-2 align-items-center">
            <div class="col-sm-6 col-md-3">
                <select name="supplier_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Suppliers</option>
                    @foreach($suppliers as $supplier)
                    <option value="{{ $supplier->id }}" {{ $supplierId == $supplier->id ? 'selected' : '' }}>
                        {{ $supplier->name }} ({{ $supplier->company_name ?: 'Vendor' }})
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="col-sm-6 col-md-3">
                <input type="date" name="start_date" class="form-control form-control-sm" value="{{ $startDate }}" placeholder="From date">
            </div>

            <div class="col-sm-6 col-md-3">
                <input type="date" name="end_date" class="form-control form-control-sm" value="{{ $endDate }}" placeholder="To date">
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

<!-- Aggregated Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card p-3">
            <span class="text-muted small fw-semibold">Total Procurement Expenditure</span>
            <h3 class="fw-bold my-1 text-primary">{{ \App\Models\Setting::get('currency_symbol', '৳') }}{{ number_format($totalSpent, 2) }}</h3>
            <small class="text-muted">Total financial outflow</small>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card p-3">
            <span class="text-muted small fw-semibold">Purchase Orders Placed</span>
            <h3 class="fw-bold my-1 text-dark">{{ $totalOrders }}</h3>
            <small class="text-muted">Completed purchase shipments</small>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card p-3">
            <span class="text-muted small fw-semibold">Total Stock Units Received</span>
            <h3 class="fw-bold my-1 text-success">{{ number_format($totalQuantityPurchased) }}</h3>
            <small class="text-muted">Units physically added to stock</small>
        </div>
    </div>
</div>

<!-- Purchases Table -->
<div class="card mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <span class="fw-bold">Procurement Orders</span>
        <span class="badge bg-light text-dark border">{{ $purchases->total() }} records</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Reference No</th>
                    <th>Date</th>
                    <th>Supplier</th>
                    <th class="text-center">Items Count</th>
                    <th class="text-center">Total Quantity</th>
                    <th class="text-end">Grand Total</th>
                    <th>Recorded By</th>
                    <th class="text-end">Details</th>
                </tr>
            </thead>
            <tbody>
                @forelse($purchases as $purchase)
                <tr>
                    <td>
                        <a href="{{ route('purchases.show', $purchase) }}" class="fw-semibold text-primary font-monospace text-decoration-none">
                            {{ $purchase->reference_no }}
                        </a>
                    </td>
                    <td>{{ $purchase->purchase_date->format('M d, Y') }}</td>
                    <td>
                        <div class="fw-semibold">{{ $purchase->supplier ? $purchase->supplier->name : 'N/A' }}</div>
                        <small class="text-muted">{{ $purchase->supplier ? $purchase->supplier->company_name : '' }}</small>
                    </td>
                    <td class="text-center font-monospace">{{ $purchase->items->count() }}</td>
                    <td class="text-center font-monospace fw-bold">{{ $purchase->total_quantity }} pcs</td>
                    <td class="text-end font-monospace fw-bold text-dark">
                        {{ \App\Models\Setting::get('currency_symbol', '৳') }}{{ number_format($purchase->grand_total, 2) }}
                    </td>
                    <td><small class="text-muted">{{ $purchase->user ? $purchase->user->name : 'N/A' }}</small></td>
                    <td class="text-end">
                        <a href="{{ route('purchases.show', $purchase) }}" class="btn btn-sm btn-light border">View</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-5 text-muted">
                        No purchase records found for this criteria.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($purchases->hasPages())
    <div class="card-footer bg-white border-top py-3">
        {{ $purchases->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>
@endsection
