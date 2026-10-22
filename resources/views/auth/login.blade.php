@extends('layouts.auth')

@section('title', 'Sign In')

@section('content')
<div class="auth-card">
    <div class="auth-header">
        <div class="auth-logo">
            <i class="bi bi-box-seam-fill"></i>
        </div>
        <h4 class="fw-bold mb-1">{{ \App\Models\Setting::get('business_name', 'StockMaster Pro') }}</h4>
        <p class="text-muted small mb-0">Sign in to manage inventory & stock</p>
    </div>

    <div class="auth-body">
        @if(session('info'))
        <div class="alert alert-info py-2 px-3 small border-0 mb-3">
            {{ session('info') }}
        </div>
        @endif

        @if ($errors->any())
        <div class="alert alert-danger py-2 px-3 small border-0 mb-3">
            {{ $errors->first() }}
        </div>
        @endif

        <form action="{{ route('login.submit') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label for="email" class="form-label fw-semibold small text-secondary">Email Address</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-envelope"></i></span>
                    <input type="email" name="email" id="email" class="form-control border-start-0 @error('email') is-invalid @enderror" value="{{ old('email', 'admin@stockmaster.com') }}" placeholder="user@example.com" required autofocus>
                </div>
            </div>

            <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <label for="password" class="form-label fw-semibold small text-secondary mb-0">Password</label>
                </div>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-lock"></i></span>
                    <input type="password" name="password" id="password" class="form-control border-start-0 @error('password') is-invalid @enderror" value="password123" placeholder="••••••••" required>
                </div>
            </div>

            <div class="d-flex align-items-center justify-content-between mb-4">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember" checked>
                    <label class="form-check-label small text-muted" for="remember">
                        Remember me
                    </label>
                </div>
            </div>

            <button type="submit" class="btn btn-primary-custom">
                <i class="bi bi-box-arrow-in-right me-1"></i> Sign In to Dashboard
            </button>
        </form>

        <!-- Quick Demo Fillers -->
        <div class="mt-4 pt-3 border-top">
            <div class="text-center text-muted small mb-2 fw-semibold">Quick Demo Login:</div>
            <div class="d-grid gap-2 d-flex justify-content-center">
                <button type="button" class="btn btn-sm btn-outline-primary" onclick="fillCredentials('admin@stockmaster.com', 'password123')">
                    <i class="bi bi-shield-lock me-1"></i> Admin Demo
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="fillCredentials('staff@stockmaster.com', 'password123')">
                    <i class="bi bi-person-badge me-1"></i> Staff Demo
                </button>
            </div>
            <p class="text-center text-muted mt-3 mb-0" style="font-size: 0.72rem;">
                <i class="bi bi-shield-check text-success"></i> Public registration is restricted. Accounts are provisioned by system administrators.
            </p>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function fillCredentials(email, password) {
        document.getElementById('email').value = email;
        document.getElementById('password').value = password;
    }
</script>
@endpush
