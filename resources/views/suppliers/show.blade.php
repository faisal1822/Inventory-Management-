@extends('layouts.app')

@section('title', 'Supplier: ' . $supplier->name)
@section('page_title', 'Supplier Profile')
@section('page_subtitle', $supplier->company_name ? $supplier->company_name : $supplier->name)

@section('content')
<div class="row g-4 mb-4">
    <div class="col-lg-4">
        <div class="card h-100 p-4">
            <div class="d-flex align-items-center gap-3 mb-4">
                <div class="p-3 rounded-circle bg-primary bg-opacity-10 text-primary fs-3">
                    <i class="bi bi-truck"></i>
                </div>
                <div>
                    <h5 class="fw-bold mb-0">{{ $supplier->name }}</h5>
                    <small class="text-primary">{{ $supplier->company_name ?: 'Individual Vendor' }}</small>
                </div>
            </div>

            <div class="mb-3">
                <label class="text-muted small fw-semibold d-block">Status</label>
                @if($supplier->is_active)
                <span class="badge badge-subtle-success">Active Supplier</span>
                @else
                <span class="badge badge-subtle-secondary">Inactive</span>
                @endif
            </div>

            <div class="mb-3">
                <label class="text-muted small fw-semibold d-block">Phone Number</label>
                <div>{{ $supplier->phone ?: 'N/A' }}</div>
            </div>

            <div class="mb-3">
                <label class="text-muted small fw-semibold d-block">Email Address</label>
                <div>{{ $supplier->email ?: 'N/A' }}</div>
            </div>

            <div class="mb-3">
                <label class="text-muted small fw-semibold d-block">Address</label>
                <div class="text-muted small">{{ $supplier->address ?: 'No address specified' }}</div>
            </div>

            @if($supplier->notes)
            <div class="mb-4">
                <label class="text-muted small fw-semibold d-block">Internal Notes</label>
                <div class="bg-light p-2 rounded text-muted small">{{ $supplier->notes }}</div>
            </div>
            @endif

            <div class="mt-auto pt-3 border-top d-flex gap-2">
                <a href="{{ route('suppliers.edit', $supplier) }}" class="btn btn-sm btn-outline-primary w-100">Edit Profile</a>
                <a href="{{ route('suppliers.index') }}" class="btn btn-sm btn-light border w-100">Back</a>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span class="fw-bold">Purchase Order History with {{ $supplier->name }}</span>
                <a href="{{ route('purchases.create') }}" class="btn btn-sm btn-primary">
                    <i class="bi bi-plus-lg me-1"></i> New Purchase Order
                </a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Reference No</th>
                            <th>Date</th>
                            <th class="text-center">Items</th>
                            <th>Grand Total</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($supplier->purchases as $purchase)
                        <tr>
                            <td>
                                <span class="fw-semibold text-primary font-monospace">{{ $purchase->reference_no }}</span>
                            </td>
                            <td>{{ $purchase->purchase_date->format('M d, Y') }}</td>
                            <td class="text-center">
                                <span class="badge bg-light text-dark border">{{ $purchase->items->count() }} items ({{ $purchase->total_quantity }} units)</span>
                            </td>
                            <td class="fw-bold text-dark">
                                {{ \App\Models\Setting::get('currency_symbol', '৳') }}{{ number_format($purchase->grand_total, 2) }}
                            </td>
                            <td class="text-end">
                                <a href="{{ route('purchases.show', $purchase) }}" class="btn btn-sm btn-light border">View</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">
                                No purchases recorded from this supplier yet.
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
