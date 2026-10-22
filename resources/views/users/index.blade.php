@extends('layouts.app')

@section('title', 'Staff & User Management')
@section('page_title', 'Staff & User Accounts')
@section('page_subtitle', 'Provision accounts and manage administrative and staff permissions')

@section('content')
<div class="card mb-4">
    <div class="card-header bg-white d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">
        <form method="GET" action="{{ route('users.index') }}" class="d-flex align-items-center gap-2">
            <div class="input-group input-group-sm" style="max-width: 280px;">
                <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                <input type="text" name="search" class="form-control border-start-0" placeholder="Search name, email, phone..." value="{{ $search }}">
            </div>

            <select name="role" class="form-select form-select-sm" style="width: auto;" onchange="this.form.submit()">
                <option value="">All Roles</option>
                <option value="admin" {{ $role === 'admin' ? 'selected' : '' }}>Admin</option>
                <option value="staff" {{ $role === 'staff' ? 'selected' : '' }}>Staff</option>
            </select>

            @if($search || $role)
            <a href="{{ route('users.index') }}" class="btn btn-sm btn-light border">Clear</a>
            @endif
        </form>
        <div>
            <a href="{{ route('users.create') }}" class="btn btn-sm btn-primary d-flex align-items-center gap-1">
                <i class="bi bi-person-plus-fill"></i>
                <span>Add Staff User</span>
            </a>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Email Address</th>
                    <th>Phone</th>
                    <th class="text-center">Role</th>
                    <th class="text-center">Account Status</th>
                    <th class="text-center">Created</th>
                    <th class="text-end" style="width: 150px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="rounded-circle {{ $user->isAdmin() ? 'bg-primary' : 'bg-secondary' }} text-white d-flex align-items-center justify-content-center fw-bold" style="width: 34px; height: 34px; font-size: 0.85rem;">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                            <div>
                                <span class="fw-semibold text-dark">{{ $user->name }}</span>
                                @if($user->id === Auth::id())
                                <span class="badge bg-light text-primary border ms-1">You</span>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->phone ?: 'N/A' }}</td>
                    <td class="text-center">
                        @if($user->isAdmin())
                        <span class="badge badge-subtle-primary px-2 py-1"><i class="bi bi-shield-lock me-1"></i>Administrator</span>
                        @else
                        <span class="badge badge-subtle-secondary px-2 py-1"><i class="bi bi-person me-1"></i>Staff Member</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if($user->is_active)
                        <span class="badge badge-subtle-success">Active</span>
                        @else
                        <span class="badge badge-subtle-danger">Deactivated</span>
                        @endif
                    </td>
                    <td class="text-center text-muted small">
                        {{ $user->created_at->format('M d, Y') }}
                    </td>
                    <td class="text-end">
                        @if($user->id !== Auth::id())
                        <form action="{{ route('users.toggle-status', $user) }}" method="POST" class="d-inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-sm btn-light border {{ $user->is_active ? 'text-warning' : 'text-success' }}" title="{{ $user->is_active ? 'Deactivate Account' : 'Activate Account' }}">
                                <i class="bi {{ $user->is_active ? 'bi-person-slash' : 'bi-person-check' }}"></i>
                            </button>
                        </form>
                        @endif

                        <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-light border" title="Edit User">
                            <i class="bi bi-pencil"></i>
                        </a>

                        @if($user->id !== Auth::id())
                        <button type="button" class="btn btn-sm btn-light border text-danger" title="Delete User"
                            onclick="confirmDelete('{{ route('users.destroy', $user) }}', 'Are you sure you want to delete user \'{{ $user->name }}\'?')">
                            <i class="bi bi-trash"></i>
                        </button>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-5 text-muted">
                        No users found matching your search.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($users->hasPages())
    <div class="card-footer bg-white border-top py-3">
        {{ $users->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>
@endsection
