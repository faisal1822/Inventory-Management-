@extends('layouts.app')

@section('title', 'Sales & Invoices')
@section('page_title', 'Sales & Billing')
@section('page_subtitle', 'Manage customer invoices, POS transactions, and daily cash flow')

@section('content')
<!-- Filter Toolbar -->
<div class="card mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('sales.index') }}" class="row g-2 align-items-center">
            <div class="col-md-3">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control border-start-0" placeholder="Search invoice or customer..." value="{{ $search }}">
                </div>
            </div>

            <div class="col-sm-6 col-md-3">
                <select name="payment_method" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Payment Methods</option>
                    <option value="cash" {{ $paymentMethod === 'cash' ? 'selected' : '' }}>Cash</option>
                    <option value="card" {{ $paymentMethod === 'card' ? 'selected' : '' }}>Card (Debit/Credit)</option>
                    <option value="mobile_banking" {{ $paymentMethod === 'mobile_banking' ? 'selected' : '' }}>Mobile Banking (bKash/Nagad)</option>
                    <option value="bank_transfer" {{ $paymentMethod === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                    <option value="other" {{ $paymentMethod === 'other' ? 'selected' : '' }}>Other</option>
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
                @if($search || $paymentMethod || $startDate || $endDate)
                <a href="{{ route('sales.index') }}" class="btn btn-sm btn-light border" title="Reset Filters"><i class="bi bi-x-lg"></i></a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Sales Table -->
<div class="card mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <span class="fw-bold">Sales Invoices ({{ $sales->total() }})</span>
        <a href="{{ route('sales.create') }}" class="btn btn-sm btn-success d-flex align-items-center gap-1 shadow-sm">
            <i class="bi bi-cart-plus-fill"></i>
            <span>New Sale / POS</span>
        </a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Invoice No</th>
                    <th>Date</th>
                    <th>Customer</th>
                    <th class="text-center">Items</th>
                    <th class="text-end">Subtotal</th>
                    <th class="text-end">Discount</th>
                    <th class="text-end">Grand Total</th>
                    <th>Payment</th>
                    <th>Billed By</th>
                    <th class="text-end" style="width: 140px;">Actions</th>
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
                    <td>
                        <div class="fw-semibold text-dark">{{ $sale->customer_name }}</div>
                        @if($sale->customer_phone)
                        <small class="text-muted"><i class="bi bi-telephone me-1"></i>{{ $sale->customer_phone }}</small>
                        @endif
                    </td>
                    <td class="text-center">
                        <span class="badge bg-light text-dark border">{{ $sale->total_quantity }} units</span>
                    </td>
                    <td class="text-end text-muted font-monospace">
                        {{ \App\Models\Setting::get('currency_symbol', '৳') }}{{ number_format($sale->subtotal, 2) }}
                    </td>
                    <td class="text-end text-danger font-monospace">
                        {{ $sale->discount > 0 ? '-' . \App\Models\Setting::get('currency_symbol', '৳') . number_format($sale->discount, 2) : '-' }}
                    </td>
                    <td class="text-end fw-bold text-success font-monospace">
                        {{ \App\Models\Setting::get('currency_symbol', '৳') }}{{ number_format($sale->grand_total, 2) }}
                    </td>
                    <td>
                        <span class="badge bg-light text-dark border">
                            {{ ucfirst(str_replace('_', ' ', $sale->payment_method)) }}
                        </span>
                    </td>
                    <td>
                        <small class="text-muted">{{ $sale->user ? $sale->user->name : 'N/A' }}</small>
                    </td>
                    <td class="text-end">
                        <a href="{{ route('sales.show', $sale) }}" class="btn btn-sm btn-light border" title="View Details">
                            <i class="bi bi-eye"></i>
                        </a>
                        <a href="{{ route('sales.print', $sale) }}" target="_blank" class="btn btn-sm btn-light border" title="Print Invoice">
                            <i class="bi bi-printer"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="text-center py-5 text-muted">
                        <i class="bi bi-receipt fs-1 text-secondary d-block mb-2"></i>
                        No sales invoices found matching your query.
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
