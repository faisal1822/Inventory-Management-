@extends('layouts.app')

@section('title', 'Stock Adjustments')
@section('page_title', 'Stock Adjustments')
@section('page_subtitle', 'Correct inventory counts for damaged, lost, or audited items')

@section('content')
<!-- Filter Toolbar -->
<div class="card mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('adjustments.index') }}" class="row g-2 align-items-center">
            <div class="col-md-3">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control border-start-0" placeholder="Search adjustment or product..." value="{{ $search }}">
                </div>
            </div>

            <div class="col-sm-6 col-md-3">
                <select name="reason" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Reasons</option>
                    @foreach($reasons as $key => $label)
                    <option value="{{ $key }}" {{ $reason === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-sm-6 col-md-2">
                <select name="type" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Types</option>
                    <option value="addition" {{ $type === 'addition' ? 'selected' : '' }}>Stock In (+)</option>
                    <option value="subtraction" {{ $type === 'subtraction' ? 'selected' : '' }}>Stock Out (-)</option>
                </select>
            </div>

            <div class="col-sm-6 col-md-2">
                <input type="date" name="start_date" class="form-control form-control-sm" value="{{ $startDate }}" placeholder="From date">
            </div>

            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary w-100">Filter</button>
                @if($search || $reason || $type || $startDate)
                <a href="{{ route('adjustments.index') }}" class="btn btn-sm btn-light border" title="Reset Filters"><i class="bi bi-x-lg"></i></a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Adjustments Table -->
<div class="card mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <span class="fw-bold">Adjustment Records ({{ $adjustments->total() }})</span>
        <a href="{{ route('adjustments.create') }}" class="btn btn-sm btn-primary d-flex align-items-center gap-1">
            <i class="bi bi-plus-lg"></i>
            <span>New Stock Adjustment</span>
        </a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Adjustment No</th>
                    <th>Date</th>
                    <th>Product</th>
                    <th>Reason</th>
                    <th class="text-center">Previous</th>
                    <th class="text-center">Adjustment</th>
                    <th class="text-center">New Balance</th>
                    <th>Adjusted By</th>
                    <th class="text-end" style="width: 100px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($adjustments as $adj)
                <tr>
                    <td>
                        <a href="{{ route('adjustments.show', $adj) }}" class="fw-semibold text-primary font-monospace text-decoration-none">
                            {{ $adj->adjustment_no }}
                        </a>
                    </td>
                    <td>{{ $adj->created_at->format('M d, Y') }}</td>
                    <td>
                        <div class="fw-semibold text-dark">{{ $adj->product ? $adj->product->name : 'N/A' }}</div>
                        <small class="text-muted font-monospace">{{ $adj->product ? $adj->product->sku : '' }}</small>
                    </td>
                    <td>
                        <span class="badge bg-light text-dark border">
                            {{ $reasons[$adj->reason] ?? ucfirst(str_replace('_', ' ', $adj->reason)) }}
                        </span>
                    </td>
                    <td class="text-center text-muted font-monospace">{{ $adj->previous_quantity }}</td>
                    <td class="text-center fw-bold font-monospace {{ $adj->type === 'addition' ? 'text-success' : 'text-danger' }}">
                        {{ $adj->type === 'addition' ? '+' : '-' }}{{ $adj->quantity_change }}
                    </td>
                    <td class="text-center fw-bold font-monospace">{{ $adj->new_quantity }}</td>
                    <td>
                        <small class="text-muted">{{ $adj->user ? $adj->user->name : 'N/A' }}</small>
                    </td>
                    <td class="text-end">
                        <a href="{{ route('adjustments.show', $adj) }}" class="btn btn-sm btn-light border" title="View Details">
                            <i class="bi bi-eye"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center py-5 text-muted">
                        <i class="bi bi-arrow-left-right fs-1 text-secondary d-block mb-2"></i>
                        No stock adjustment records found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($adjustments->hasPages())
    <div class="card-footer bg-white border-top py-3">
        {{ $adjustments->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>
@endsection
