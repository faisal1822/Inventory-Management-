@extends('layouts.app')

@section('title', 'Invoice ' . $sale->invoice_no)
@section('page_title', 'Sales Invoice Details')
@section('page_subtitle', 'Invoice: ' . $sale->invoice_no)

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card p-4 mb-4">
            <!-- Header Section -->
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center border-bottom pb-4 mb-4 gap-3">
                <div>
                    <h4 class="fw-bold text-success mb-1">
                        <i class="bi bi-receipt me-2"></i>Sales Receipt
                    </h4>
                    <span class="font-monospace fs-6 text-dark">{{ $sale->invoice_no }}</span>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('sales.print', $sale) }}" target="_blank" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-printer me-1"></i> Print Invoice
                    </a>
                    <a href="{{ route('sales.index') }}" class="btn btn-light border btn-sm">Back</a>
                </div>
            </div>

            <!-- Meta Information -->
            <div class="row g-4 mb-4">
                <div class="col-md-6">
                    <h6 class="fw-bold text-muted text-uppercase" style="font-size: 0.75rem;">Customer Details</h6>
                    <h5 class="fw-bold mb-1">{{ $sale->customer_name }}</h5>
                    @if($sale->customer_phone)
                    <div class="text-muted small"><i class="bi bi-telephone me-1"></i>{{ $sale->customer_phone }}</div>
                    @endif
                    <div class="mt-2">
                        <span class="badge bg-light text-dark border">
                            Payment: {{ ucfirst(str_replace('_', ' ', $sale->payment_method)) }}
                        </span>
                    </div>
                </div>

                <div class="col-md-6 text-md-end">
                    <h6 class="fw-bold text-muted text-uppercase" style="font-size: 0.75rem;">Invoice Information</h6>
                    <div><span class="text-muted small">Sale Date:</span> <strong class="text-dark">{{ $sale->sale_date->format('F d, Y') }}</strong></div>
                    <div><span class="text-muted small">Billed By:</span> <strong class="text-dark">{{ $sale->user ? $sale->user->name : 'Staff' }}</strong></div>
                    <div><span class="text-muted small">Order Status:</span> <span class="badge badge-subtle-success">Completed & Dispatched</span></div>
                </div>
            </div>

            <!-- Items Table -->
            <div class="table-responsive mb-4">
                <table class="table table-bordered align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 45%;">Item Description</th>
                            <th class="text-center" style="width: 15%;">Quantity</th>
                            <th class="text-end" style="width: 20%;">Unit Price</th>
                            <th class="text-end" style="width: 20%;">Line Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sale->items as $item)
                        <tr>
                            <td>
                                <div class="fw-semibold text-dark">{{ $item->product ? $item->product->name : 'N/A' }}</div>
                                <small class="text-muted font-monospace">{{ $item->product ? $item->product->sku : '' }}</small>
                            </td>
                            <td class="text-center fw-bold">{{ $item->quantity }} units</td>
                            <td class="text-end font-monospace">{{ \App\Models\Setting::get('currency_symbol', '৳') }}{{ number_format($item->unit_price, 2) }}</td>
                            <td class="text-end fw-bold font-monospace">{{ \App\Models\Setting::get('currency_symbol', '৳') }}{{ number_format($item->line_total, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <td colspan="3" class="text-end fw-semibold">Subtotal:</td>
                            <td class="text-end font-monospace">{{ \App\Models\Setting::get('currency_symbol', '৳') }}{{ number_format($sale->subtotal, 2) }}</td>
                        </tr>
                        @if($sale->discount > 0)
                        <tr>
                            <td colspan="3" class="text-end fw-semibold text-danger">Discount:</td>
                            <td class="text-end font-monospace text-danger">-{{ \App\Models\Setting::get('currency_symbol', '৳') }}{{ number_format($sale->discount, 2) }}</td>
                        </tr>
                        @endif
                        <tr>
                            <td colspan="3" class="text-end fw-bold fs-5">Grand Total:</td>
                            <td class="text-end fw-bold text-success fs-5 font-monospace">
                                {{ \App\Models\Setting::get('currency_symbol', '৳') }}{{ number_format($sale->grand_total, 2) }}
                            </td>
                        </tr>
                        <tr>
                            <td colspan="3" class="text-end fw-semibold text-muted">Paid Amount:</td>
                            <td class="text-end font-monospace">{{ \App\Models\Setting::get('currency_symbol', '৳') }}{{ number_format($sale->paid_amount, 2) }}</td>
                        </tr>
                        @if(Auth::user()->isAdmin())
                        <tr class="table-info">
                            <td colspan="3" class="text-end fw-semibold text-primary">Gross Profit on this Sale (Cost Basis):</td>
                            <td class="text-end fw-bold text-primary font-monospace">{{ \App\Models\Setting::get('currency_symbol', '৳') }}{{ number_format($sale->profit, 2) }}</td>
                        </tr>
                        @endif
                    </tfoot>
                </table>
            </div>

            @if($sale->notes)
            <div class="bg-light p-3 rounded mb-3">
                <span class="fw-semibold small d-block mb-1">Notes:</span>
                <span class="text-muted small">{{ $sale->notes }}</span>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
