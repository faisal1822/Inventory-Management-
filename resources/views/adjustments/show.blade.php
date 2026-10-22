@extends('layouts.app')

@section('title', 'Adjustment: ' . $adjustment->adjustment_no)
@section('page_title', 'Stock Adjustment Details')
@section('page_subtitle', 'Reference: ' . $adjustment->adjustment_no)

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card p-4">
            <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-4">
                <div>
                    <h5 class="fw-bold text-dark mb-1">Adjustment Reference: {{ $adjustment->adjustment_no }}</h5>
                    <small class="text-muted">Recorded on {{ $adjustment->created_at->format('F d, Y h:i A') }}</small>
                </div>
                <a href="{{ route('adjustments.index') }}" class="btn btn-sm btn-light border">Back</a>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-md-6">
                    <h6 class="fw-bold text-muted text-uppercase" style="font-size: 0.75rem;">Adjusted Product</h6>
                    <h5 class="fw-bold text-dark mb-1">{{ $adjustment->product ? $adjustment->product->name : 'N/A' }}</h5>
                    <div class="font-monospace text-primary small">{{ $adjustment->product ? $adjustment->product->sku : '' }}</div>
                    <div class="text-muted small">Category: {{ $adjustment->product && $adjustment->product->category ? $adjustment->product->category->name : 'N/A' }}</div>
                </div>

                <div class="col-md-6 text-md-end">
                    <h6 class="fw-bold text-muted text-uppercase" style="font-size: 0.75rem;">Reason & Action</h6>
                    <div><span class="badge bg-light text-dark border fs-6">{{ ucfirst(str_replace('_', ' ', $adjustment->reason)) }}</span></div>
                    <div class="text-muted small mt-1">Adjusted by: <strong>{{ $adjustment->user ? $adjustment->user->name : 'N/A' }}</strong></div>
                </div>
            </div>

            <div class="card bg-light border p-4 mb-4">
                <div class="row text-center">
                    <div class="col-4">
                        <span class="text-muted small d-block">Previous Stock</span>
                        <h3 class="fw-bold mb-0 text-secondary">{{ $adjustment->previous_quantity }}</h3>
                    </div>
                    <div class="col-4">
                        <span class="text-muted small d-block">Quantity Change</span>
                        <h3 class="fw-bold mb-0 {{ $adjustment->type === 'addition' ? 'text-success' : 'text-danger' }}">
                            {{ $adjustment->type === 'addition' ? '+' : '-' }}{{ $adjustment->quantity_change }}
                        </h3>
                    </div>
                    <div class="col-4">
                        <span class="text-muted small d-block">New Resulting Stock</span>
                        <h3 class="fw-bold mb-0 text-primary">{{ $adjustment->new_quantity }}</h3>
                    </div>
                </div>
            </div>

            @if($adjustment->notes)
            <div class="bg-white border p-3 rounded mb-3">
                <span class="fw-semibold small d-block mb-1">Notes & Explanation:</span>
                <span class="text-muted small">{{ $adjustment->notes }}</span>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
