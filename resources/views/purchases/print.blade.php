<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Order - {{ $purchase->reference_no }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background: #fff;
            color: #000;
            padding: 20px;
        }
        .receipt-container {
            max-width: 800px;
            margin: auto;
            border: 1px solid #ddd;
            padding: 30px;
            border-radius: 8px;
        }
        @media print {
            body { padding: 0; }
            .receipt-container { border: none; padding: 0; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    <div class="receipt-container">
        <!-- Action Buttons -->
        <div class="no-print d-flex justify-content-between mb-4">
            <a href="{{ route('purchases.show', $purchase) }}" class="btn btn-sm btn-outline-secondary">&larr; Back to Order</a>
            <button onclick="window.print()" class="btn btn-sm btn-primary">Print Order Receipt</button>
        </div>

        <!-- Business Header -->
        <div class="d-flex justify-content-between align-items-start border-bottom pb-3 mb-4">
            <div>
                <h3 class="fw-bold mb-1">{{ $settings['business_name'] ?? 'StockMaster Pro' }}</h3>
                <div class="text-muted small">{{ $settings['business_address'] ?? 'Dhaka, Bangladesh' }}</div>
                <div class="text-muted small">Phone: {{ $settings['phone'] ?? '+880 1700 000000' }} | Email: {{ $settings['email'] ?? 'info@stockmaster.com' }}</div>
            </div>
            <div class="text-end">
                <h4 class="fw-bold text-uppercase text-secondary mb-1">Purchase Order</h4>
                <div class="font-monospace fw-bold">{{ $purchase->reference_no }}</div>
                <div class="small text-muted">Date: {{ $purchase->purchase_date->format('d/m/Y') }}</div>
            </div>
        </div>

        <!-- Supplier Details -->
        <div class="row mb-4">
            <div class="col-6">
                <h6 class="fw-bold text-uppercase text-muted small">Vendor / Supplier:</h6>
                <div class="fw-bold">{{ $purchase->supplier ? $purchase->supplier->name : 'N/A' }}</div>
                <div>{{ $purchase->supplier ? $purchase->supplier->company_name : '' }}</div>
                <div class="small text-muted">{{ $purchase->supplier ? $purchase->supplier->phone : '' }}</div>
                <div class="small text-muted">{{ $purchase->supplier ? $purchase->supplier->address : '' }}</div>
            </div>
            <div class="col-6 text-end">
                <h6 class="fw-bold text-uppercase text-muted small">Order Status:</h6>
                <div class="fw-semibold text-success">Goods Received & Stock Added</div>
                <div class="small text-muted">Received By: {{ $purchase->user ? $purchase->user->name : 'Admin' }}</div>
            </div>
        </div>

        <!-- Items Table -->
        <table class="table table-bordered mb-4">
            <thead class="table-light">
                <tr>
                    <th style="width: 5%;">#</th>
                    <th>Item Description</th>
                    <th class="text-center" style="width: 15%;">Quantity</th>
                    <th class="text-end" style="width: 20%;">Unit Cost</th>
                    <th class="text-end" style="width: 25%;">Line Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($purchase->items as $idx => $item)
                <tr>
                    <td>{{ $idx + 1 }}</td>
                    <td>
                        <div class="fw-bold">{{ $item->product ? $item->product->name : 'N/A' }}</div>
                        <small class="text-muted font-monospace">{{ $item->product ? $item->product->sku : '' }}</small>
                    </td>
                    <td class="text-center">{{ $item->quantity }}</td>
                    <td class="text-end">{{ $settings['currency_symbol'] ?? '৳' }}{{ number_format($item->unit_cost, 2) }}</td>
                    <td class="text-end">{{ $settings['currency_symbol'] ?? '৳' }}{{ number_format($item->line_total, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="2" class="text-end">Total Quantity:</th>
                    <th class="text-center">{{ $purchase->total_quantity }}</th>
                    <th class="text-end">Grand Total:</th>
                    <th class="text-end fs-5">{{ $settings['currency_symbol'] ?? '৳' }}{{ number_format($purchase->grand_total, 2) }}</th>
                </tr>
            </tfoot>
        </table>

        @if($purchase->notes)
        <div class="border p-2 rounded small text-muted mb-4">
            <strong>Notes:</strong> {{ $purchase->notes }}
        </div>
        @endif

        <div class="row pt-5 mt-4 text-center">
            <div class="col-6">
                <div class="border-top pt-2 mx-4 small text-muted">Supplier Signature</div>
            </div>
            <div class="col-6">
                <div class="border-top pt-2 mx-4 small text-muted">Authorized Receiver Signature</div>
            </div>
        </div>
    </div>
</body>
</html>
