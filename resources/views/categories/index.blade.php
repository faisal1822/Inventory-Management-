@extends('layouts.app')

@section('title', 'Product Categories')
@section('page_title', 'Category Management')
@section('page_subtitle', 'Organize and structure your inventory taxonomy')

@section('content')
<div class="card mb-4">
    <div class="card-header bg-white d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">
        <form method="GET" action="{{ route('categories.index') }}" class="d-flex align-items-center gap-2">
            <div class="input-group input-group-sm" style="max-width: 280px;">
                <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                <input type="text" name="search" class="form-control border-start-0" placeholder="Search categories..." value="{{ $search }}">
            </div>
            @if($search)
            <a href="{{ route('categories.index') }}" class="btn btn-sm btn-light border">Clear</a>
            @endif
        </form>
        <div>
            <a href="{{ route('categories.create') }}" class="btn btn-sm btn-primary d-flex align-items-center gap-1">
                <i class="bi bi-plus-lg"></i>
                <span>Add Category</span>
            </a>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th style="width: 50px;">#</th>
                    <th>Category Name</th>
                    <th>Description</th>
                    <th class="text-center">Products Count</th>
                    <th class="text-center">Status</th>
                    <th class="text-end" style="width: 140px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $category)
                <tr>
                    <td class="text-muted small">{{ $category->id }}</td>
                    <td>
                        <span class="fw-semibold text-dark">{{ $category->name }}</span>
                    </td>
                    <td>
                        <span class="text-muted small">{{ $category->description ?: 'No description provided' }}</span>
                    </td>
                    <td class="text-center">
                        <span class="badge badge-subtle-primary px-2 py-1">{{ $category->products_count }} products</span>
                    </td>
                    <td class="text-center">
                        @if($category->is_active)
                        <span class="badge badge-subtle-success">Active</span>
                        @else
                        <span class="badge badge-subtle-secondary">Inactive</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <a href="{{ route('categories.edit', $category) }}" class="btn btn-sm btn-light border" title="Edit Category">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <button type="button" class="btn btn-sm btn-light border text-danger" title="Delete Category"
                            onclick="confirmDelete('{{ route('categories.destroy', $category) }}', 'Are you sure you want to delete category \'{{ $category->name }}\'? Categories with products cannot be deleted.')">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-5 text-muted">
                        <i class="bi bi-tags fs-1 text-secondary d-block mb-2"></i>
                        No categories found matching your query.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($categories->hasPages())
    <div class="card-footer bg-white border-top py-3">
        {{ $categories->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>
@endsection
