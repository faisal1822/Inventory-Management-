@extends('layouts.app')

@section('title', 'Add New Product')
@section('page_title', 'Create Product')
@section('page_subtitle', 'Register a new stock item into your inventory database')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span class="fw-bold">Product Information</span>
                <a href="{{ route('products.index') }}" class="btn btn-sm btn-light border">Back to Catalog</a>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label fw-semibold">Product Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="e.g. Wireless Mouse M185" required autofocus>
                            @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="sku" class="form-label fw-semibold">SKU / Product Code <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="text" name="sku" id="sku" class="form-control text-uppercase @error('sku') is-invalid @enderror" value="{{ old('sku') }}" placeholder="e.g. SKU-ELEC-009" required>
                                <button type="button" class="btn btn-outline-secondary" onclick="generateSku()">Generate</button>
                                @error('sku')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <small class="text-muted">Must be globally unique.</small>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label for="barcode" class="form-label fw-semibold">Barcode (EAN/UPC)</label>
                            <input type="text" name="barcode" id="barcode" class="form-control @error('barcode') is-invalid @enderror" value="{{ old('barcode') }}" placeholder="Optional barcode digits">
                            @error('barcode')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="category_id" class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
                            <select name="category_id" id="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                                <option value="">-- Select Category --</option>
                                @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                                @endforeach
                            </select>
                            @error('category_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="supplier_id" class="form-label fw-semibold">Preferred Supplier</label>
                            <select name="supplier_id" id="supplier_id" class="form-select @error('supplier_id') is-invalid @enderror">
                                <option value="">-- Optional Supplier --</option>
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
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-3">
                            <label for="purchase_price" class="form-label fw-semibold">Purchase Price ({{ \App\Models\Setting::get('currency_symbol', '৳') }}) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0" name="purchase_price" id="purchase_price" class="form-control @error('purchase_price') is-invalid @enderror" value="{{ old('purchase_price', '0.00') }}" required>
                            @error('purchase_price')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3">
                            <label for="selling_price" class="form-label fw-semibold">Selling Price ({{ \App\Models\Setting::get('currency_symbol', '৳') }}) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0" name="selling_price" id="selling_price" class="form-control @error('selling_price') is-invalid @enderror" value="{{ old('selling_price', '0.00') }}" required>
                            @error('selling_price')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3">
                            <label for="current_stock" class="form-label fw-semibold">Opening Stock Quantity <span class="text-danger">*</span></label>
                            <input type="number" min="0" name="current_stock" id="current_stock" class="form-control @error('current_stock') is-invalid @enderror" value="{{ old('current_stock', '0') }}" required>
                            @error('current_stock')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3">
                            <label for="min_stock_threshold" class="form-label fw-semibold">Min Stock Alert Level <span class="text-danger">*</span></label>
                            <input type="number" min="0" name="min_stock_threshold" id="min_stock_threshold" class="form-control @error('min_stock_threshold') is-invalid @enderror" value="{{ old('min_stock_threshold', '5') }}" required>
                            @error('min_stock_threshold')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-8">
                            <label for="description" class="form-label fw-semibold">Product Description</label>
                            <textarea name="description" id="description" rows="4" class="form-control @error('description') is-invalid @enderror" placeholder="Technical specifications, dimensions, features...">{{ old('description') }}</textarea>
                            @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Product Image</label>
                            <input type="file" name="image" id="product_image" class="form-control mb-2 @error('image') is-invalid @enderror" accept="image/jpeg,image/png,image/webp" onchange="previewImage(this)">
                            @error('image')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="border rounded p-2 text-center bg-light" style="min-height: 90px;">
                                <img id="image_preview" src="{{ asset('images/product-placeholder.svg') }}" alt="Preview" style="max-height: 80px; max-width: 100%;" class="rounded">
                            </div>
                            <small class="text-muted d-block mt-1" style="font-size: 0.72rem;">Formats: JPG, PNG, WEBP (Max 2MB)</small>
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" name="is_active" id="is_active" value="1" {{ old('is_active', '1') == '1' ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold" for="is_active">Available for Sales</label>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('products.index') }}" class="btn btn-light border">Cancel</a>
                        <button type="submit" class="btn btn-primary">Save Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function generateSku() {
        const rand = Math.floor(1000 + Math.random() * 9000);
        document.getElementById('sku').value = 'SKU-PRD-' + rand;
    }

    function previewImage(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('image_preview').src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endpush
