@extends('layouts.app')

@section('title', 'Purchase Orders')
@section('page_title', 'Purchase Orders (Stock In)')
@section('page_subtitle', 'Record supplier goods intake and restock inventory')

@section('content')
<!-- Filter Toolbar -->
<div class="card mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('purchases.index') }}" class="row g-2 align-items-center">
            <div class="col-md-3">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control border-start-0" placeholder="Search reference no..." value="{{ $search }}">
                </div>
            </div>

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

            <div class="col-sm-6 col-md-2">
                <input type="date" name="start_date" class="form-control form-control-sm" value="{{ $startDate }}" placeholder="From date">
            </div>

            <div class="col-sm-6 col-md-2">
                <input type="date" name="end_date" class="form-control form-control-sm" value="{{ $endDate }}" placeholder="To date">
            </div>

            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary w-100">Filter</button>
                @if($search || $supplierId || $startDate || $endDate)
                <a href="{{ route('purchases.index') }}" class="btn btn-sm btn-light border" title="Reset Filters"><i class="bi bi-x-lg"></i></a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Purchases Table -->
<div class="card mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <span class="fw-bold">Purchase Orders List ({{ $purchases->total() }})</span>
        <a href="{{ route('purchases.create') }}" class="btn btn-sm btn-primary d-flex align-items-center gap-1">
            <i class="bi bi-cart-plus-fill"></i>
            <span>Create Purchase Order</span>
        </a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Reference No</th>
                    <th>Supplier</th>
                    <th>Purchase Date</th>
                    <th class="text-center">Items / Qty</th>
                    <th class="text-end">Grand Total</th>
                    <th>Created By</th>
                    <th class="text-end" style="width: 140px;">Actions</th>
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
                    <td>
                        <div class="fw-semibold text-dark">{{ $purchase->supplier ? $purchase->supplier->name : 'N/A' }}</div>
                        <small class="text-muted">{{ $purchase->supplier ? $purchase->supplier->company_name : '' }}</small>
                    </td>
                    <td>{{ $purchase->purchase_date->format('M d, Y') }}</td>
                    <td class="text-center">
                        <span class="badge bg-light text-dark border">
                            {{ $purchase->items->count() }} items ({{ $purchase->total_quantity }} units)
                        </span>
                    </td>
                    <td class="text-end fw-bold text-dark font-monospace">
                        {{ \App\Models\Setting::get('currency_symbol', '৳') }}{{ number_format($purchase->grand_total, 2) }}
                    </td>
                    <td>
                        <small class="text-muted">{{ $purchase->user ? $purchase->user->name : 'System' }}</small>
                    </td>
                    <td class="text-end">
                        <a href="{{ route('purchases.show', $purchase) }}" class="btn btn-sm btn-light border" title="View Details">
                            <i class="bi bi-eye"></i>
                        </a>
                        <a href="{{ route('purchases.print', $purchase) }}" target="_blank" class="btn btn-sm btn-light border" title="Print Receipt">
                            <i class="bi bi-printer"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-5 text-muted">
                        <i class="bi bi-cart-x fs-1 text-secondary d-block mb-2"></i>
                        No purchase orders found matching your search criteria.
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
