@extends('layouts.app')

@section('title', 'Suppliers')
@section('page_title', 'Supplier Directory')
@section('page_subtitle', 'Manage trusted vendors, contracts, and contact information')

@section('content')
<div class="card mb-4">
    <div class="card-header bg-white d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">
        <form method="GET" action="{{ route('suppliers.index') }}" class="d-flex align-items-center gap-2">
            <div class="input-group input-group-sm" style="max-width: 320px;">
                <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                <input type="text" name="search" class="form-control border-start-0" placeholder="Search supplier or company..." value="{{ $search }}">
            </div>
            @if($search)
            <a href="{{ route('suppliers.index') }}" class="btn btn-sm btn-light border">Clear</a>
            @endif
        </form>
        <div>
            <a href="{{ route('suppliers.create') }}" class="btn btn-sm btn-primary d-flex align-items-center gap-1">
                <i class="bi bi-person-plus-fill"></i>
                <span>Add Supplier</span>
            </a>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Supplier / Company</th>
                    <th>Contact Info</th>
                    <th>Address</th>
                    <th class="text-center">Purchases</th>
                    <th class="text-center">Status</th>
                    <th class="text-end" style="width: 150px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($suppliers as $supplier)
                <tr>
                    <td>
                        <div class="fw-semibold text-dark">{{ $supplier->name }}</div>
                        <small class="text-primary">{{ $supplier->company_name ?: 'Individual Supplier' }}</small>
                    </td>
                    <td>
                        <div><i class="bi bi-telephone text-muted me-1"></i>{{ $supplier->phone ?: 'N/A' }}</div>
                        <small class="text-muted"><i class="bi bi-envelope text-muted me-1"></i>{{ $supplier->email ?: 'N/A' }}</small>
                    </td>
                    <td>
                        <small class="text-muted">{{ Str::limit($supplier->address, 40) ?: 'N/A' }}</small>
                    </td>
                    <td class="text-center">
                        <span class="badge badge-subtle-info px-2 py-1">{{ $supplier->purchases_count }} orders</span>
                    </td>
                    <td class="text-center">
                        @if($supplier->is_active)
                        <span class="badge badge-subtle-success">Active</span>
                        @else
                        <span class="badge badge-subtle-secondary">Inactive</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <a href="{{ route('suppliers.show', $supplier) }}" class="btn btn-sm btn-light border" title="View Supplier Profile">
                            <i class="bi bi-eye"></i>
                        </a>
                        <a href="{{ route('suppliers.edit', $supplier) }}" class="btn btn-sm btn-light border" title="Edit Supplier">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <button type="button" class="btn btn-sm btn-light border text-danger" title="Delete Supplier"
                            onclick="confirmDelete('{{ route('suppliers.destroy', $supplier) }}', 'Are you sure you want to delete supplier \'{{ $supplier->name }}\'? Suppliers with recorded purchases cannot be deleted.')">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-5 text-muted">
                        <i class="bi bi-truck fs-1 text-secondary d-block mb-2"></i>
                        No suppliers found matching your query.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($suppliers->hasPages())
    <div class="card-footer bg-white border-top py-3">
        {{ $suppliers->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>
@endsection
