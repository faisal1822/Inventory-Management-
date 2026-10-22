@extends('layouts.app')

@section('title', 'My Profile')
@section('page_title', 'User Profile')
@section('page_subtitle', 'Manage your account credentials and personal preferences')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card p-4">
            <div class="d-flex align-items-center gap-3 border-bottom pb-4 mb-4">
                <div class="rounded-circle {{ $user->isAdmin() ? 'bg-primary' : 'bg-secondary' }} text-white d-flex align-items-center justify-content-center fw-bold fs-3" style="width: 64px; height: 64px;">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <div>
                    <h5 class="fw-bold mb-0 text-dark">{{ $user->name }}</h5>
                    <div class="text-muted small">{{ $user->email }}</div>
                    <span class="badge {{ $user->isAdmin() ? 'bg-primary' : 'bg-secondary' }} mt-1">
                        {{ strtoupper($user->role) }}
                    </span>
                </div>
            </div>

            <form action="{{ route('profile.update') }}" method="POST">
                @csrf
                @method('PUT')

                <h6 class="fw-bold text-dark mb-3">Personal Details</h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="name" class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
                        @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="email" class="form-label fw-semibold">Email Address <span class="text-danger">*</span></label>
                        <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
                        @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="phone" class="form-label fw-semibold">Phone Number</label>
                        <input type="text" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $user->phone) }}">
                        @error('phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <h6 class="fw-bold text-dark border-top pt-4 mb-3">Security & Password</h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-12">
                        <label for="current_password" class="form-label fw-semibold">Current Password</label>
                        <input type="password" name="current_password" id="current_password" class="form-control @error('current_password') is-invalid @enderror" placeholder="Enter current password to authorize changes">
                        @error('current_password')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="new_password" class="form-label fw-semibold">New Password</label>
                        <input type="password" name="new_password" id="new_password" class="form-control @error('new_password') is-invalid @enderror" placeholder="Leave blank to keep current">
                        @error('new_password')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="new_password_confirmation" class="form-label fw-semibold">Confirm New Password</label>
                        <input type="password" name="new_password_confirmation" id="new_password_confirmation" class="form-control" placeholder="Re-type new password">
                    </div>
                </div>

                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-check2-circle me-1"></i> Update Profile
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
