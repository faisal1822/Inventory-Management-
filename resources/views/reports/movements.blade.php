@extends('layouts.app')

@section('title', 'Stock Movement History')
@section('page_title', 'Stock Movement Audit Trail')
@section('page_subtitle', 'Traceable chronological history of every stock increase and decrease')

@section('content')
<!-- Filter Toolbar -->
<div class="card mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('reports.movements') }}" class="row g-2 align-items-center">
            <div class="col-sm-6 col-md-3">
                <select name="product_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Products</option>
                    @foreach($products as $p)
                    <option value="{{ $p->id }}" {{ $productId == $p->id ? 'selected' : '' }}>
                        {{ $p->name }} ({{ $p->sku }})
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="col-sm-6 col-md-2">
                <select name="type" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Movement Types</option>
                    <option value="purchase" {{ $type === 'purchase' ? 'selected' : '' }}>Purchase Intake (+)</option>
                    <option value="sale" {{ $type === 'sale' ? 'selected' : '' }}>Sales Invoice (-)</option>
                    <option value="adjustment" {{ $type === 'adjustment' ? 'selected' : '' }}>Stock Adjustment (+/-)</option>
                </select>
            </div>

            <div class="col-sm-6 col-md-2">
                <input type="date" name="start_date" class="form-control form-control-sm" value="{{ $startDate }}" placeholder="From date">
            </div>

            <div class="col-sm-6 col-md-2">
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

<!-- Movements Table -->
<div class="card mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <span class="fw-bold"><i class="bi bi-clock-history me-2 text-primary"></i>Movement Audit Trail</span>
        <span class="badge bg-light text-dark border">{{ $movements->total() }} records</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Date & Time</th>
                    <th>Product & SKU</th>
                    <th>Movement Type</th>
                    <th>Reference</th>
                    <th class="text-center">Previous Stock</th>
                    <th class="text-center">Quantity Change</th>
                    <th class="text-center">New Balance</th>
                    <th>User</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody>
                @forelse($movements as $m)
                <tr>
                    <td>
                        <div class="small fw-semibold text-dark">{{ $m->created_at->format('M d, Y') }}</div>
                        <small class="text-muted" style="font-size: 0.72rem;">{{ $m->created_at->format('h:i:s A') }}</small>
                    </td>
                    <td>
                        <div class="fw-semibold">{{ $m->product ? $m->product->name : 'N/A' }}</div>
                        <small class="font-monospace text-muted">{{ $m->product ? $m->product->sku : '' }}</small>
                    </td>
                    <td>
                        @if($m->type === 'purchase')
                        <span class="badge badge-subtle-primary"><i class="bi bi-truck me-1"></i>Purchase Intake</span>
                        @elseif($m->type === 'sale')
                        <span class="badge badge-subtle-success"><i class="bi bi-receipt me-1"></i>Sales Invoice</span>
                        @else
                        <span class="badge badge-subtle-warning"><i class="bi bi-arrow-left-right me-1"></i>Adjustment</span>
                        @endif
                    </td>
                    <td>
                        <span class="font-monospace text-dark small">{{ $m->reference_no ?: '-' }}</span>
                    </td>
                    <td class="text-center font-monospace text-muted">{{ $m->previous_stock }}</td>
                    <td class="text-center font-monospace fw-bold {{ $m->quantity_change > 0 ? 'text-success' : 'text-danger' }}">
                        {{ $m->quantity_change > 0 ? '+' : '' }}{{ $m->quantity_change }}
                    </td>
                    <td class="text-center font-monospace fw-bold">{{ $m->new_stock }}</td>
                    <td>
                        <small class="text-muted">{{ $m->user ? $m->user->name : 'System' }}</small>
                    </td>
                    <td>
                        <small class="text-muted">{{ Str::limit($m->notes, 30) ?: '-' }}</small>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center py-5 text-muted">
                        No movement audit records found matching your filters.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($movements->hasPages())
    <div class="card-footer bg-white border-top py-3">
        {{ $movements->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>
@endsection
