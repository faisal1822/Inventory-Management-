<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - {{ $sale->invoice_no }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background: #fff;
            color: #000;
            padding: 20px;
        }
        .invoice-container {
            max-width: 800px;
            margin: auto;
            border: 1px solid #ddd;
            padding: 30px;
            border-radius: 8px;
        }
        @media print {
            body { padding: 0; }
            .invoice-container { border: none; padding: 0; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <!-- Action Buttons -->
        <div class="no-print d-flex justify-content-between mb-4">
            <a href="{{ route('sales.show', $sale) }}" class="btn btn-sm btn-outline-secondary">&larr; Back to Sale</a>
            <button onclick="window.print()" class="btn btn-sm btn-success">Print Customer Receipt</button>
        </div>

        <!-- Business Header -->
        <div class="d-flex justify-content-between align-items-start border-bottom pb-3 mb-4">
            <div>
                <h3 class="fw-bold mb-1">{{ $settings['business_name'] ?? 'StockMaster Pro' }}</h3>
                <div class="text-muted small">{{ $settings['business_address'] ?? 'Dhaka, Bangladesh' }}</div>
                <div class="text-muted small">Phone: {{ $settings['phone'] ?? '+880 1700 000000' }} | Email: {{ $settings['email'] ?? 'info@stockmaster.com' }}</div>
            </div>
            <div class="text-end">
                <h4 class="fw-bold text-uppercase text-success mb-1">Sales Invoice</h4>
                <div class="font-monospace fw-bold">{{ $sale->invoice_no }}</div>
                <div class="small text-muted">Date: {{ $sale->sale_date->format('d/m/Y') }}</div>
            </div>
        </div>

        <!-- Customer & Billing Meta -->
        <div class="row mb-4">
            <div class="col-6">
                <h6 class="fw-bold text-uppercase text-muted small">Billed To (Customer):</h6>
                <div class="fw-bold">{{ $sale->customer_name }}</div>
                @if($sale->customer_phone)
                <div class="small text-muted">Phone: {{ $sale->customer_phone }}</div>
                @endif
            </div>
            <div class="col-6 text-end">
                <h6 class="fw-bold text-uppercase text-muted small">Payment Details:</h6>
                <div>Method: <strong>{{ ucfirst(str_replace('_', ' ', $sale->payment_method)) }}</strong></div>
                <div>Status: <span class="badge bg-success">PAID</span></div>
                <div class="small text-muted">Cashier: {{ $sale->user ? $sale->user->name : 'Staff' }}</div>
            </div>
        </div>

        <!-- Items Table -->
        <table class="table table-bordered mb-4">
            <thead class="table-light">
                <tr>
                    <th style="width: 5%;">#</th>
                    <th>Item Description</th>
                    <th class="text-center" style="width: 15%;">Qty</th>
                    <th class="text-end" style="width: 20%;">Price</th>
                    <th class="text-end" style="width: 25%;">Line Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sale->items as $idx => $item)
                <tr>
                    <td>{{ $idx + 1 }}</td>
                    <td>
                        <div class="fw-bold">{{ $item->product ? $item->product->name : 'N/A' }}</div>
                        <small class="text-muted font-monospace">{{ $item->product ? $item->product->sku : '' }}</small>
                    </td>
                    <td class="text-center">{{ $item->quantity }}</td>
                    <td class="text-end">{{ $settings['currency_symbol'] ?? '৳' }}{{ number_format($item->unit_price, 2) }}</td>
                    <td class="text-end">{{ $settings['currency_symbol'] ?? '৳' }}{{ number_format($item->line_total, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4" class="text-end">Subtotal:</td>
                    <td class="text-end">{{ $settings['currency_symbol'] ?? '৳' }}{{ number_format($sale->subtotal, 2) }}</td>
                </tr>
                @if($sale->discount > 0)
                <tr>
                    <td colspan="4" class="text-end text-danger">Discount:</td>
                    <td class="text-end text-danger">-{{ $settings['currency_symbol'] ?? '৳' }}{{ number_format($sale->discount, 2) }}</td>
                </tr>
                @endif
                <tr class="table-light fw-bold">
                    <td colspan="4" class="text-end fs-6">Grand Total:</td>
                    <td class="text-end fs-5 text-success">{{ $settings['currency_symbol'] ?? '৳' }}{{ number_format($sale->grand_total, 2) }}</td>
                </tr>
                <tr>
                    <td colspan="4" class="text-end text-muted">Paid Amount:</td>
                    <td class="text-end">{{ $settings['currency_symbol'] ?? '৳' }}{{ number_format($sale->paid_amount, 2) }}</td>
                </tr>
            </tfoot>
        </table>

        @if($sale->notes)
        <div class="border p-2 rounded small text-muted mb-4">
            <strong>Notes:</strong> {{ $sale->notes }}
        </div>
        @endif

        <div class="text-center pt-4 border-top">
            <p class="mb-1 fw-semibold text-muted">Thank you for your business!</p>
            <small class="text-muted">Please retain this receipt for any warranty claims or exchange queries within 7 days.</small>
        </div>
    </div>
</body>
</html>
