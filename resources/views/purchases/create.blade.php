@extends('layouts.app')

@section('title', 'Create Purchase Order')
@section('page_title', 'New Purchase Order')
@section('page_subtitle', 'Intake products into warehouse stock from suppliers')

@section('content')
<form action="{{ route('purchases.store') }}" method="POST" id="purchaseForm">
    @csrf

    <div class="row g-4 mb-4">
        <!-- Order Header Card -->
        <div class="col-lg-12">
            <div class="card p-4">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="supplier_id" class="form-label fw-semibold">Supplier <span class="text-danger">*</span></label>
                        <select name="supplier_id" id="supplier_id" class="form-select @error('supplier_id') is-invalid @enderror" required>
                            <option value="">-- Select Supplier --</option>
                            @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                {{ $supplier->name }} ({{ $supplier->company_name ?: 'Vendor' }})
                            </option>
                            @endforeach
                        </select>
                        @error('supplier_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="purchase_date" class="form-label fw-semibold">Purchase Date <span class="text-danger">*</span></label>
                        <input type="date" name="purchase_date" id="purchase_date" class="form-control @error('purchase_date') is-invalid @enderror" value="{{ old('purchase_date', date('Y-m-d')) }}" required>
                        @error('purchase_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Suggested Reference No</label>
                        <input type="text" class="form-control bg-light font-monospace" value="{{ $suggestedRef }}" readonly>
                        <small class="text-muted">Auto-generated upon confirmation</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Product Items Table -->
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <span class="fw-bold"><i class="bi bi-box-seam me-2 text-primary"></i>Purchased Items</span>
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="addRow()">
                        <i class="bi bi-plus-circle me-1"></i> Add Item Line
                    </button>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0" id="itemsTable">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 45%;">Product <span class="text-danger">*</span></th>
                                <th style="width: 15%;" class="text-center">Current Stock</th>
                                <th style="width: 15%;" class="text-center">Quantity <span class="text-danger">*</span></th>
                                <th style="width: 15%;" class="text-end">Unit Cost ({{ \App\Models\Setting::get('currency_symbol', '৳') }}) <span class="text-danger">*</span></th>
                                <th style="width: 15%;" class="text-end">Line Total ({{ \App\Models\Setting::get('currency_symbol', '৳') }})</th>
                                <th style="width: 50px;" class="text-center"></th>
                            </tr>
                        </thead>
                        <tbody id="itemsBody">
                            <!-- Dynamic Rows Injected by JS -->
                        </tbody>
                        <tfoot>
                            <tr class="table-light fw-bold">
                                <td colspan="2" class="text-end">Total Summary:</td>
                                <td class="text-center" id="totalQtyDisplay">0</td>
                                <td class="text-end">Grand Total:</td>
                                <td class="text-end text-primary fs-5" id="grandTotalDisplay">{{ \App\Models\Setting::get('currency_symbol', '৳') }}0.00</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Notes and Actions Card -->
        <div class="col-lg-12">
            <div class="card p-4">
                <div class="mb-3">
                    <label for="notes" class="form-label fw-semibold">Purchase Notes / Remarks</label>
                    <textarea name="notes" id="notes" rows="2" class="form-control" placeholder="Supplier invoice number, payment terms, delivery tracking..."></textarea>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('purchases.index') }}" class="btn btn-light border">Cancel</a>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-check2-circle me-1"></i> Save Purchase Order & Update Stock
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
    const productsData = @json($products);
    let rowIndex = 0;

    function addRow(selectedProductId = '', qty = 1, cost = 0) {
        const tbody = document.getElementById('itemsBody');
        const tr = document.createElement('tr');
        tr.id = `row_${rowIndex}`;

        let optionsHtml = '<option value="">-- Choose Product --</option>';
        productsData.forEach(p => {
            const isSel = (p.id == selectedProductId) ? 'selected' : '';
            optionsHtml += `<option value="${p.id}" data-cost="${p.purchase_price}" data-stock="${p.current_stock}" data-sku="${p.sku}" ${isSel}>${p.name} (${p.sku})</option>`;
        });

        tr.innerHTML = `
            <td>
                <select name="items[${rowIndex}][product_id]" class="form-select product-select" required onchange="onProductSelect(${rowIndex})">
                    ${optionsHtml}
                </select>
            </td>
            <td class="text-center text-muted stock-display font-monospace" id="stock_${rowIndex}">-</td>
            <td>
                <input type="number" min="1" name="items[${rowIndex}][quantity]" id="qty_${rowIndex}" class="form-control text-center qty-input" value="${qty}" required oninput="calculateRow(${rowIndex})">
            </td>
            <td>
                <input type="number" step="0.01" min="0" name="items[${rowIndex}][unit_cost]" id="cost_${rowIndex}" class="form-control text-end cost-input" value="${cost}" required oninput="calculateRow(${rowIndex})">
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
        calculateGrandTotal();
    }

    function removeRow(index) {
        const row = document.getElementById(`row_${index}`);
        if (row) {
            row.remove();
            calculateGrandTotal();
        }
    }

    function onProductSelect(index) {
        const select = document.querySelector(`#row_${index} .product-select`);
        const selectedOpt = select.options[select.selectedIndex];
        
        if (selectedOpt && selectedOpt.value) {
            const cost = parseFloat(selectedOpt.getAttribute('data-cost')) || 0;
            const stock = selectedOpt.getAttribute('data-stock');
            
            document.getElementById(`cost_${index}`).value = cost.toFixed(2);
            document.getElementById(`stock_${index}`).textContent = `${stock} in stock`;
        } else {
            document.getElementById(`stock_${index}`).textContent = '-';
        }

        calculateRow(index);
    }

    function calculateRow(index) {
        const qty = parseFloat(document.getElementById(`qty_${index}`)?.value) || 0;
        const cost = parseFloat(document.getElementById(`cost_${index}`)?.value) || 0;
        const lineTotal = qty * cost;

        const lineEl = document.getElementById(`line_total_${index}`);
        if (lineEl) {
            lineEl.textContent = lineTotal.toFixed(2);
        }

        calculateGrandTotal();
    }

    function calculateGrandTotal() {
        let totalQty = 0;
        let grandTotal = 0;

        document.querySelectorAll('.qty-input').forEach(input => {
            totalQty += parseFloat(input.value) || 0;
        });

        document.querySelectorAll('.line-total-display').forEach(el => {
            grandTotal += parseFloat(el.textContent) || 0;
        });

        document.getElementById('totalQtyDisplay').textContent = totalQty;
        document.getElementById('grandTotalDisplay').textContent = '{{ \App\Models\Setting::get("currency_symbol", "৳") }}' + grandTotal.toFixed(2);
    }

    // Add initial empty row on page load
    document.addEventListener('DOMContentLoaded', () => {
        addRow();
    });
</script>
@endpush
