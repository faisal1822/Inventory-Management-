@extends('layouts.app')

@section('title', 'Create Stock Adjustment')
@section('page_title', 'New Stock Adjustment')
@section('page_subtitle', 'Correct product physical counts and audit discrepancies')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span class="fw-bold">Adjustment Details</span>
                <a href="{{ route('adjustments.index') }}" class="btn btn-sm btn-light border">Back to Adjustments</a>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('adjustments.store') }}" method="POST" id="adjForm">
                    @csrf

                    <div class="mb-3">
                        <label for="product_id" class="form-label fw-semibold">Select Product <span class="text-danger">*</span></label>
                        <select name="product_id" id="product_id" class="form-select @error('product_id') is-invalid @enderror" required onchange="updateStockDisplay()">
                            <option value="">-- Choose Product --</option>
                            @foreach($products as $product)
                            <option value="{{ $product->id }}" data-stock="{{ $product->current_stock }}" {{ old('product_id') == $product->id ? 'selected' : '' }}>
                                {{ $product->name }} (SKU: {{ $product->sku }}) - Current Stock: {{ $product->current_stock }}
                            </option>
                            @endforeach
                        </select>
                        @error('product_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Adjustment Type <span class="text-danger">*</span></label>
                            <div class="d-flex gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="type" id="type_sub" value="subtraction" {{ old('type', 'subtraction') === 'subtraction' ? 'checked' : '' }} onchange="calculateNewStock()">
                                    <label class="form-check-label text-danger fw-semibold" for="type_sub">
                                        <i class="bi bi-dash-circle me-1"></i> Deduction (-)
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="type" id="type_add" value="addition" {{ old('type') === 'addition' ? 'checked' : '' }} onchange="calculateNewStock()">
                                    <label class="form-check-label text-success fw-semibold" for="type_add">
                                        <i class="bi bi-plus-circle me-1"></i> Addition (+)
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="quantity_change" class="form-label fw-semibold">Quantity to Adjust <span class="text-danger">*</span></label>
                            <input type="number" min="1" name="quantity_change" id="quantity_change" class="form-control @error('quantity_change') is-invalid @enderror" value="{{ old('quantity_change', '1') }}" required oninput="calculateNewStock()">
                            @error('quantity_change')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Live Calculation Indicator Card -->
                    <div class="card bg-light border p-3 mb-3">
                        <div class="row text-center">
                            <div class="col-4">
                                <span class="text-muted small d-block">Current Stock</span>
                                <h4 class="fw-bold mb-0 text-dark" id="displayCurrent">0</h4>
                            </div>
                            <div class="col-4">
                                <span class="text-muted small d-block">Change</span>
                                <h4 class="fw-bold mb-0 text-danger" id="displayChange">-1</h4>
                            </div>
                            <div class="col-4">
                                <span class="text-muted small d-block">New Projected Stock</span>
                                <h4 class="fw-bold mb-0 text-primary" id="displayNew">0</h4>
                            </div>
                        </div>
                        <div class="alert alert-danger py-1 px-2 small mt-2 mb-0 d-none" id="negativeStockAlert">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i> Stock cannot become negative! Please reduce deduction quantity.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="reason" class="form-label fw-semibold">Reason for Adjustment <span class="text-danger">*</span></label>
                        <select name="reason" id="reason" class="form-select @error('reason') is-invalid @enderror" required>
                            <option value="">-- Select Reason --</option>
                            @foreach($reasons as $key => $label)
                            <option value="{{ $key }}" {{ old('reason') === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('reason')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="notes" class="form-label fw-semibold">Notes / Explanation</label>
                        <textarea name="notes" id="notes" rows="3" class="form-control" placeholder="Provide context regarding the damage, audit log, or vendor return...">{{ old('notes') }}</textarea>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('adjustments.index') }}" class="btn btn-light border">Cancel</a>
                        <button type="submit" class="btn btn-primary" id="submitBtn">Apply Stock Adjustment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function updateStockDisplay() {
        const select = document.getElementById('product_id');
        const selected = select.options[select.selectedIndex];
        const stock = selected && selected.value ? parseInt(selected.getAttribute('data-stock')) || 0 : 0;
        
        document.getElementById('displayCurrent').textContent = stock;
        calculateNewStock();
    }

    function calculateNewStock() {
        const currentStock = parseInt(document.getElementById('displayCurrent').textContent) || 0;
        const change = parseInt(document.getElementById('quantity_change').value) || 0;
        const isAddition = document.getElementById('type_add').checked;

        const changeEl = document.getElementById('displayChange');
        const newEl = document.getElementById('displayNew');
        const alertEl = document.getElementById('negativeStockAlert');
        const submitBtn = document.getElementById('submitBtn');

        let newStock = 0;
        if (isAddition) {
            newStock = currentStock + change;
            changeEl.textContent = `+${change}`;
            changeEl.className = 'fw-bold mb-0 text-success';
        } else {
            newStock = currentStock - change;
            changeEl.textContent = `-${change}`;
            changeEl.className = 'fw-bold mb-0 text-danger';
        }

        newEl.textContent = newStock;

        if (newStock < 0) {
            alertEl.classList.remove('d-none');
            newEl.className = 'fw-bold mb-0 text-danger';
            submitBtn.disabled = true;
        } else {
            alertEl.classList.add('d-none');
            newEl.className = 'fw-bold mb-0 text-primary';
            submitBtn.disabled = false;
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        updateStockDisplay();
    });
</script>
@endpush
