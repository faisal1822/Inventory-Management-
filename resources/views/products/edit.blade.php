@extends('layouts.app')

@section('title', 'Edit Product - ' . $product->name)
@section('page_title', 'Edit Product')
@section('page_subtitle', 'Update details for product SKU: ' . $product->sku)

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span class="fw-bold">Edit Product</span>
                <a href="{{ route('products.index') }}" class="btn btn-sm btn-light border">Back to Catalog</a>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('products.update', $product) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label fw-semibold">Product Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $product->name) }}" required>
                            @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="sku" class="form-label fw-semibold">SKU / Product Code <span class="text-danger">*</span></label>
                            <input type="text" name="sku" id="sku" class="form-control text-uppercase @error('sku') is-invalid @enderror" value="{{ old('sku', $product->sku) }}" required>
                            @error('sku')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label for="barcode" class="form-label fw-semibold">Barcode (EAN/UPC)</label>
                            <input type="text" name="barcode" id="barcode" class="form-control @error('barcode') is-invalid @enderror" value="{{ old('barcode', $product->barcode) }}">
                            @error('barcode')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="category_id" class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
                            <select name="category_id" id="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                                @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
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
                                <option value="">-- None --</option>
                                @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" {{ old('supplier_id', $product->supplier_id) == $supplier->id ? 'selected' : '' }}>
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
                        <div class="col-md-4">
                            <label for="purchase_price" class="form-label fw-semibold">Cost / Purchase Price ({{ \App\Models\Setting::get('currency_symbol', '৳') }}) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0" name="purchase_price" id="purchase_price" class="form-control @error('purchase_price') is-invalid @enderror" value="{{ old('purchase_price', $product->purchase_price) }}" required>
                            @error('purchase_price')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="selling_price" class="form-label fw-semibold">Selling Price ({{ \App\Models\Setting::get('currency_symbol', '৳') }}) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0" name="selling_price" id="selling_price" class="form-control @error('selling_price') is-invalid @enderror" value="{{ old('selling_price', $product->selling_price) }}" required>
                            @error('selling_price')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="min_stock_threshold" class="form-label fw-semibold">Min Stock Alert Level <span class="text-danger">*</span></label>
                            <input type="number" min="0" name="min_stock_threshold" id="min_stock_threshold" class="form-control @error('min_stock_threshold') is-invalid @enderror" value="{{ old('min_stock_threshold', $product->min_stock_threshold) }}" required>
                            @error('min_stock_threshold')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="alert alert-light border d-flex align-items-center justify-content-between p-3 mb-3">
                        <div>
                            <span class="fw-semibold text-dark">Current Stock Quantity:</span>
                            <span class="badge badge-subtle-primary fs-6 ms-2">{{ $product->current_stock }} units</span>
                        </div>
                        <a href="{{ route('adjustments.create') }}" class="btn btn-sm btn-outline-warning">
                            <i class="bi bi-arrow-left-right me-1"></i> Make Stock Adjustment
                        </a>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-8">
                            <label for="description" class="form-label fw-semibold">Product Description</label>
                            <textarea name="description" id="description" rows="4" class="form-control @error('description') is-invalid @enderror">{{ old('description', $product->description) }}</textarea>
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

                            <div class="border rounded p-2 text-center bg-light mb-2 position-relative" style="min-height: 110px;">
                                <img id="image_preview" src="{{ $product->image_url }}" alt="{{ $product->name }}" style="max-height: 100px; max-width: 100%;" class="rounded object-fit-contain">
                            </div>

                            @if($product->hasCustomImage())
                            <div class="form-check p-2 bg-light rounded border border-danger-subtle">
                                <input class="form-check-input ms-1" type="checkbox" name="remove_image" id="remove_image" value="1" onchange="toggleRemoveImage(this)">
                                <label class="form-check-label text-danger fw-semibold small ms-2" for="remove_image">
                                    <i class="bi bi-trash3 me-1"></i> Remove current image
                                </label>
                            </div>
                            <small class="text-muted d-block mt-1" style="font-size: 0.72rem;">Uploading a new image will automatically replace the existing file.</small>
                            @else
                            <small class="text-muted d-block" style="font-size: 0.72rem;">No custom image uploaded. Showing neutral placeholder.</small>
                            @endif
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" name="is_active" id="is_active" value="1" {{ old('is_active', $product->is_active ? '1' : '0') == '1' ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold" for="is_active">Available for Sales</label>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('products.index') }}" class="btn btn-light border">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const originalImageUrl = '{{ $product->image_url }}';
    const placeholderUrl = '{{ asset("images/product-placeholder.svg") }}';

    function previewImage(input) {
        if (input.files && input.files[0]) {
            const removeCheckbox = document.getElementById('remove_image');
            if (removeCheckbox) removeCheckbox.checked = false;

            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('image_preview').src = e.target.result;
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    function toggleRemoveImage(checkbox) {
        const fileInput = document.getElementById('product_image');
        if (checkbox.checked) {
            fileInput.value = '';
            document.getElementById('image_preview').src = placeholderUrl;
        } else {
            document.getElementById('image_preview').src = originalImageUrl;
        }
    }
</script>
@endpush
