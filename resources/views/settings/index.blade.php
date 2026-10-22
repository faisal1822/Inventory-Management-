@extends('layouts.app')

@section('title', 'Business Settings')
@section('page_title', 'Application & Business Settings')
@section('page_subtitle', 'Configure store identity, receipts header, currency symbols, and default settings')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span class="fw-bold"><i class="bi bi-gear-fill me-2 text-primary"></i>Store Identity & Regional Settings</span>
                <span class="badge bg-light text-muted border">Admin Only</span>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('settings.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="business_name" class="form-label fw-semibold">Business / Shop Name <span class="text-danger">*</span></label>
                            <input type="text" name="business_name" id="business_name" class="form-control @error('business_name') is-invalid @enderror" value="{{ old('business_name', $settings['business_name'] ?? 'StockMaster Pro') }}" required>
                            @error('business_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Displayed on navigation bar, reports, and invoices.</small>
                        </div>

                        <div class="col-md-6">
                            <label for="currency_symbol" class="form-label fw-semibold">Currency Symbol <span class="text-danger">*</span></label>
                            <input type="text" name="currency_symbol" id="currency_symbol" class="form-control @error('currency_symbol') is-invalid @enderror" value="{{ old('currency_symbol', $settings['currency_symbol'] ?? '৳') }}" placeholder="৳, $, €, £" required>
                            @error('currency_symbol')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Default: BDT (৳)</small>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="phone" class="form-label fw-semibold">Business Phone Number</label>
                            <input type="text" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $settings['phone'] ?? '') }}" placeholder="+880 1700 000000">
                            @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="email" class="form-label fw-semibold">Official Business Email</label>
                            <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $settings['email'] ?? '') }}" placeholder="info@company.com">
                            @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="business_address" class="form-label fw-semibold">Business Physical Address</label>
                        <textarea name="business_address" id="business_address" rows="2" class="form-control @error('business_address') is-invalid @enderror" placeholder="Store address printed on receipts...">{{ old('business_address', $settings['business_address'] ?? '') }}</textarea>
                        @error('business_address')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label for="tax_percentage" class="form-label fw-semibold">Default VAT / Tax Percentage (%)</label>
                            <input type="number" step="0.01" min="0" max="100" name="tax_percentage" id="tax_percentage" class="form-control @error('tax_percentage') is-invalid @enderror" value="{{ old('tax_percentage', $settings['tax_percentage'] ?? '0') }}">
                            @error('tax_percentage')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Tax rate applied for invoice reporting.</small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Store Brand Logo</label>
                            <input type="file" name="logo" id="logo" class="form-control @error('logo') is-invalid @enderror" accept="image/png,image/jpeg,image/webp,image/svg+xml">
                            @error('logo')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            @if(!empty($settings['logo']) && file_exists(public_path('storage/' . $settings['logo'])))
                            <div class="mt-2 d-flex align-items-center gap-2">
                                <img src="{{ asset('storage/' . $settings['logo']) }}" alt="Logo" style="height: 40px;" class="border p-1 rounded bg-light">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="remove_logo" id="remove_logo" value="1">
                                    <label class="form-check-label text-danger small" for="remove_logo">Remove logo</label>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-check2-circle me-1"></i> Save Settings
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
