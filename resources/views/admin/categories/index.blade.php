@extends('layouts.admin')

@section('title', 'Category Management')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-0">Category Management</h1>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-light">
            <h5 class="mb-0">All Categories</h5>
        </div>
        <div class="card-body">
            @if($categories->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Slug</th>
                            <th>Description</th>
                            <th>Products</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($categories as $category)
                        <tr>
                            <td>
                                <strong>{{ $category->name }}</strong>
                            </td>
                            <td>
                                <code class="text-muted">{{ $category->slug }}</code>
                            </td>
                            <td>
                                <small class="text-muted">
                                    {{ \Illuminate\Support\Str::limit($category->description, 50) ?? '-' }}
                                </small>
                            </td>
                            <td>
                                <span class="badge bg-info">{{ $category->products()->count() }}</span>
                            </td>
                            <td>
                                @if($category->isActive())
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('admin.categories.edit', $category) }}" 
                                       class="btn btn-warning" 
                                       title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-danger" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#deleteModal{{ $category->id }}"
                                            @if($category->products()->exists()) disabled title="Cannot delete category with products" @endif>
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="mt-3">
                {{ $categories->links() }}
            </div>
            @else
            <div class="text-center py-5">
                <i class="bi bi-inbox" style="font-size: 2rem; color: #ccc;"></i>
                <p class="text-muted mt-2">No categories found.</p>
                <a href="{{ route('admin.categories.create') }}" class="btn btn-primary mt-3">
                    <i class="bi bi-plus-circle me-1"></i> Create First Category
                </a>
            </div>
            @endif
        </div>
    </div>
</div>

{{-- Delete Modals --}}
@foreach($categories as $category)
    @if(!$category->products()->exists())
    <div class="modal fade" id="deleteModal{{ $category->id }}" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title">Delete Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this category?</p>
                    <p class="mb-0"><strong class="text-danger">{{ $category->name }}</strong></p>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form id="deleteForm{{ $category->id }}" action="{{ route('admin.categories.destroy', $category) }}" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif
@endforeach

@endsection
