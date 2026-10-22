@extends('layouts.app')

@section('title', 'Purchase Order ' . $purchase->reference_no)
@section('page_title', 'Purchase Order Details')
@section('page_subtitle', 'Reference: ' . $purchase->reference_no)

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card p-4 mb-4">
            <!-- Header Section -->
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center border-bottom pb-4 mb-4 gap-3">
                <div>
                    <h4 class="fw-bold text-primary mb-1">
                        <i class="bi bi-cart-check-fill me-2"></i>Purchase Order
                    </h4>
                    <span class="font-monospace fs-6 text-dark">{{ $purchase->reference_no }}</span>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('purchases.print', $purchase) }}" target="_blank" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-printer me-1"></i> Print PO Receipt
                    </a>
                    <a href="{{ route('purchases.index') }}" class="btn btn-light border btn-sm">Back</a>
                </div>
            </div>

            <!-- Supplier & Order Meta -->
            <div class="row g-4 mb-4">
                <div class="col-md-6">
                    <h6 class="fw-bold text-muted text-uppercase" style="font-size: 0.75rem;">Supplier Details</h6>
                    <h5 class="fw-bold mb-1">{{ $purchase->supplier ? $purchase->supplier->name : 'N/A' }}</h5>
                    <div class="text-primary fw-semibold">{{ $purchase->supplier ? $purchase->supplier->company_name : '' }}</div>
                    <div class="text-muted small"><i class="bi bi-telephone me-1"></i>{{ $purchase->supplier ? $purchase->supplier->phone : 'N/A' }}</div>
                    <div class="text-muted small"><i class="bi bi-geo-alt me-1"></i>{{ $purchase->supplier ? $purchase->supplier->address : 'N/A' }}</div>
                </div>

                <div class="col-md-6 text-md-end">
                    <h6 class="fw-bold text-muted text-uppercase" style="font-size: 0.75rem;">Order Metadata</h6>
                    <div><span class="text-muted small">Purchase Date:</span> <strong class="text-dark">{{ $purchase->purchase_date->format('F d, Y') }}</strong></div>
                    <div><span class="text-muted small">Recorded By:</span> <strong class="text-dark">{{ $purchase->user ? $purchase->user->name : 'System' }}</strong></div>
                    <div><span class="text-muted small">Status:</span> <span class="badge badge-subtle-success">Received & Stock Added</span></div>
                </div>
            </div>

            <!-- Items Table -->
            <div class="table-responsive mb-4">
                <table class="table table-bordered align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 45%;">Item Description</th>
                            <th class="text-center" style="width: 15%;">Quantity</th>
                            <th class="text-end" style="width: 20%;">Unit Cost</th>
                            <th class="text-end" style="width: 20%;">Line Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($purchase->items as $item)
                        <tr>
                            <td>
                                <div class="fw-semibold text-dark">{{ $item->product ? $item->product->name : 'N/A' }}</div>
                                <small class="text-muted font-monospace">{{ $item->product ? $item->product->sku : '' }}</small>
                            </td>
                            <td class="text-center fw-bold">{{ $item->quantity }} units</td>
                            <td class="text-end font-monospace">{{ \App\Models\Setting::get('currency_symbol', '৳') }}{{ number_format($item->unit_cost, 2) }}</td>
                            <td class="text-end fw-bold font-monospace">{{ \App\Models\Setting::get('currency_symbol', '৳') }}{{ number_format($item->line_total, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <td class="text-end fw-bold">Total Quantity:</td>
                            <td class="text-center fw-bold">{{ $purchase->total_quantity }} units</td>
                            <td class="text-end fw-bold">Grand Total:</td>
                            <td class="text-end fw-bold text-primary fs-5 font-monospace">
                                {{ \App\Models\Setting::get('currency_symbol', '৳') }}{{ number_format($purchase->grand_total, 2) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            @if($purchase->notes)
            <div class="bg-light p-3 rounded mb-3">
                <span class="fw-semibold small d-block mb-1">Notes / Remarks:</span>
                <span class="text-muted small">{{ $purchase->notes }}</span>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
