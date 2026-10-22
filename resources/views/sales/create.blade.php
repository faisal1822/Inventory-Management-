@extends('layouts.app')

@section('title', 'Point of Sale (POS) / New Sale')
@section('page_title', 'Point of Sale / New Sale')
@section('page_subtitle', 'Generate customer invoice, reduce stock, and record payment')

@section('content')
<form action="{{ route('sales.store') }}" method="POST" id="saleForm">
    @csrf

    <div class="row g-4">
        <!-- Main Form Column (Items) -->
        <div class="col-lg-8">
            <!-- Customer & Invoice Meta -->
            <div class="card p-4 mb-4">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="sale_date" class="form-label fw-semibold">Sale Date <span class="text-danger">*</span></label>
                        <input type="date" name="sale_date" id="sale_date" class="form-control @error('sale_date') is-invalid @enderror" value="{{ old('sale_date', date('Y-m-d')) }}" required>
                        @error('sale_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="customer_name" class="form-label fw-semibold">Customer Name</label>
                        <input type="text" name="customer_name" id="customer_name" class="form-control" value="{{ old('customer_name', 'Walk-in Customer') }}" placeholder="e.g. Walk-in Customer">
                    </div>

                    <div class="col-md-4">
                        <label for="customer_phone" class="form-label fw-semibold">Customer Phone</label>
                        <input type="text" name="customer_phone" id="customer_phone" class="form-control" value="{{ old('customer_phone') }}" placeholder="+880 1700 000000">
                    </div>
                </div>
            </div>

            <!-- Product Items Table -->
            <div class="card mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <span class="fw-bold"><i class="bi bi-cart-check me-2 text-success"></i>Invoice Items</span>
                    <button type="button" class="btn btn-sm btn-outline-success" onclick="addRow()">
                        <i class="bi bi-plus-circle me-1"></i> Add Product Line
                    </button>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0" id="saleItemsTable">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 45%;">Product <span class="text-danger">*</span></th>
                                <th style="width: 15%;" class="text-center">Avail. Stock</th>
                                <th style="width: 15%;" class="text-center">Quantity <span class="text-danger">*</span></th>
                                <th style="width: 15%;" class="text-end">Unit Price ({{ \App\Models\Setting::get('currency_symbol', '৳') }}) <span class="text-danger">*</span></th>
                                <th style="width: 15%;" class="text-end">Line Total ({{ \App\Models\Setting::get('currency_symbol', '৳') }})</th>
                                <th style="width: 50px;" class="text-center"></th>
                            </tr>
                        </thead>
                        <tbody id="saleItemsBody">
                            <!-- Injected by JS -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Notes -->
            <div class="card p-3">
                <label for="notes" class="form-label fw-semibold small">Invoice Notes / Payment Terms</label>
                <textarea name="notes" id="notes" rows="2" class="form-control form-control-sm" placeholder="Warranty details, payment reference number, customer special requests...">{{ old('notes') }}</textarea>
            </div>
        </div>

        <!-- Sidebar Summary Column (Payment & Checkout) -->
        <div class="col-lg-4">
            <div class="card p-4 sticky-top" style="top: 85px;">
                <h5 class="fw-bold text-dark border-bottom pb-3 mb-3 d-flex align-items-center gap-2">
                    <i class="bi bi-cash-coin text-success"></i> Billing Summary
                </h5>

                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Total Quantity:</span>
                    <strong id="summaryQty">0 items</strong>
                </div>

                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Subtotal:</span>
                    <strong class="font-monospace" id="summarySubtotal">{{ \App\Models\Setting::get('currency_symbol', '৳') }}0.00</strong>
                </div>

                <div class="mb-3">
                    <label for="discount" class="form-label text-muted small fw-semibold mb-1">Discount Amount ({{ \App\Models\Setting::get('currency_symbol', '৳') }})</label>
                    <input type="number" step="0.01" min="0" name="discount" id="discount" class="form-control text-end font-monospace" value="{{ old('discount', '0.00') }}" oninput="calculateTotals()">
                </div>

                <div class="bg-light p-3 rounded-3 mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fw-bold text-dark fs-6">Grand Total:</span>
                        <span class="fw-bold text-success fs-4 font-monospace" id="summaryGrandTotal">{{ \App\Models\Setting::get('currency_symbol', '৳') }}0.00</span>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="payment_method" class="form-label fw-semibold small">Payment Method <span class="text-danger">*</span></label>
                    <select name="payment_method" id="payment_method" class="form-select" required>
                        <option value="cash" selected>Cash</option>
                        <option value="card">Card (POS Terminal)</option>
                        <option value="mobile_banking">Mobile Banking (bKash / Nagad / Rocket)</option>
                        <option value="bank_transfer">Direct Bank Transfer</option>
                        <option value="other">Other Method</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="paid_amount" class="form-label fw-semibold small">Paid Amount ({{ \App\Models\Setting::get('currency_symbol', '৳') }})</label>
                    <input type="number" step="0.01" min="0" name="paid_amount" id="paid_amount" class="form-control text-end font-monospace" value="0.00" oninput="calculateTotals()">
                </div>

                <div class="d-flex justify-content-between mb-4 small text-muted">
                    <span>Change / Due:</span>
                    <strong id="summaryChange" class="text-dark font-monospace">{{ \App\Models\Setting::get('currency_symbol', '৳') }}0.00</strong>
                </div>

                <button type="submit" class="btn btn-success btn-lg w-100 shadow-sm" id="submitBtn">
                    <i class="bi bi-printer me-1"></i> Complete & Create Invoice
                </button>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
    const productsData = @json($products);
    let rowIndex = 0;

    function addRow(selectedProductId = '', qty = 1) {
        const tbody = document.getElementById('saleItemsBody');
        const tr = document.createElement('tr');
        tr.id = `row_${rowIndex}`;

        let optionsHtml = '<option value="">-- Choose Product --</option>';
        productsData.forEach(p => {
            const isSel = (p.id == selectedProductId) ? 'selected' : '';
            const isOut = p.current_stock <= 0 ? ' (Out of stock)' : '';
            optionsHtml += `<option value="${p.id}" data-price="${p.selling_price}" data-stock="${p.current_stock}" data-sku="${p.sku}" ${isSel} ${p.current_stock <= 0 ? 'disabled' : ''}>${p.name} - ${p.sku} [Stock: ${p.current_stock}]${isOut}</option>`;
        });

        tr.innerHTML = `
            <td>
                <select name="items[${rowIndex}][product_id]" class="form-select product-select" required onchange="onProductSelect(${rowIndex})">
                    ${optionsHtml}
                </select>
                <div class="text-danger small mt-1 d-none" id="stock_warning_${rowIndex}">
                    <i class="bi bi-exclamation-triangle"></i> Exceeds available stock!
                </div>
            </td>
            <td class="text-center font-monospace" id="stock_display_${rowIndex}">-</td>
            <td>
                <input type="number" min="1" name="items[${rowIndex}][quantity]" id="qty_${rowIndex}" class="form-control text-center qty-input" value="${qty}" required oninput="calculateRow(${rowIndex})">
            </td>
            <td>
                <input type="number" step="0.01" min="0" name="items[${rowIndex}][unit_price]" id="price_${rowIndex}" class="form-control text-end price-input" value="0.00" required oninput="calculateRow(${rowIndex})">
            </td>
            <td class="text-end fw-bold font-monospace line-total-display" id="line_total_${rowIndex}">0.00</td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-outline-danger border-0" onclick="removeRow(${rowIndex})" title="Remove item">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        `;

        tbody.appendChild(tr);

        if (selectedProductId) {
            onProductSelect(rowIndex);
        }

        rowIndex++;
        calculateTotals();
    }

    function removeRow(index) {
        const row = document.getElementById(`row_${index}`);
        if (row) {
            row.remove();
            calculateTotals();
        }
    }

    function onProductSelect(index) {
        const select = document.querySelector(`#row_${index} .product-select`);
        const selectedOpt = select.options[select.selectedIndex];
        
        if (selectedOpt && selectedOpt.value) {
            const price = parseFloat(selectedOpt.getAttribute('data-price')) || 0;
            const stock = parseInt(selectedOpt.getAttribute('data-stock')) || 0;
            
            document.getElementById(`price_${index}`).value = price.toFixed(2);
            document.getElementById(`stock_display_${index}`).textContent = `${stock} pcs`;
            
            // Set max on input
            const qtyInput = document.getElementById(`qty_${index}`);
            qtyInput.max = stock;
        } else {
            document.getElementById(`stock_display_${index}`).textContent = '-';
        }

        calculateRow(index);
    }

    function calculateRow(index) {
        const select = document.querySelector(`#row_${index} .product-select`);
        const selectedOpt = select ? select.options[select.selectedIndex] : null;
        const availableStock = selectedOpt && selectedOpt.value ? parseInt(selectedOpt.getAttribute('data-stock')) || 0 : 999999;

        const qtyInput = document.getElementById(`qty_${index}`);
        const qty = parseFloat(qtyInput?.value) || 0;
        const price = parseFloat(document.getElementById(`price_${index}`)?.value) || 0;
        const lineTotal = qty * price;

        const warningEl = document.getElementById(`stock_warning_${index}`);
        if (qty > availableStock) {
            warningEl.classList.remove('d-none');
            qtyInput.classList.add('is-invalid');
        } else {
            warningEl.classList.add('d-none');
            qtyInput.classList.remove('is-invalid');
        }

        const lineEl = document.getElementById(`line_total_${index}`);
        if (lineEl) {
            lineEl.textContent = lineTotal.toFixed(2);
        }

        calculateTotals();
    }

    function calculateTotals() {
        let totalQty = 0;
        let subtotal = 0;

        document.querySelectorAll('.qty-input').forEach(input => {
            totalQty += parseFloat(input.value) || 0;
        });

        document.querySelectorAll('.line-total-display').forEach(el => {
            subtotal += parseFloat(el.textContent) || 0;
        });

        const discount = parseFloat(document.getElementById('discount').value) || 0;
        const grandTotal = Math.max(0, subtotal - discount);

        document.getElementById('summaryQty').textContent = `${totalQty} items`;
        document.getElementById('summarySubtotal').textContent = '{{ \App\Models\Setting::get("currency_symbol", "৳") }}' + subtotal.toFixed(2);
        document.getElementById('summaryGrandTotal').textContent = '{{ \App\Models\Setting::get("currency_symbol", "৳") }}' + grandTotal.toFixed(2);

        // Auto update paid amount default if not yet changed or equal to previous total
        const paidInput = document.getElementById('paid_amount');
        if (paidInput.dataset.manual !== 'true') {
            paidInput.value = grandTotal.toFixed(2);
        }

        const paid = parseFloat(paidInput.value) || 0;
        const diff = paid - grandTotal;
        const changeEl = document.getElementById('summaryChange');
        
        if (diff >= 0) {
            changeEl.textContent = 'Change: {{ \App\Models\Setting::get("currency_symbol", "৳") }}' + diff.toFixed(2);
            changeEl.className = 'text-success font-monospace';
        } else {
            changeEl.textContent = 'Due: {{ \App\Models\Setting::get("currency_symbol", "৳") }}' + Math.abs(diff).toFixed(2);
            changeEl.className = 'text-danger font-monospace';
        }
    }

    document.getElementById('paid_amount').addEventListener('input', function() {
        this.dataset.manual = 'true';
        calculateTotals();
    });

    // Add first row on load
    document.addEventListener('DOMContentLoaded', () => {
        addRow();
    });
</script>
@endpush
