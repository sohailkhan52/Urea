@extends('layouts.admin')

@section('title', 'Category Management')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-0">Category Management</h1>
            </div>
            <a href="{{ route('admin.categories.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i> Add Category
            </a>
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
                                    @if($category->isActive())
                                        <form action="{{ route('admin.categories.deactivate', $category) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm btn-outline-secondary" 
                                                    title="Deactivate"
                                                    onclick="return confirm('Deactivate this category?');">
                                                <i class="bi bi-toggle-on"></i>
                                            </button>
                                        </form>
                                    @else
                                        <form action="{{ route('admin.categories.activate', $category) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm btn-outline-success" 
                                                    title="Activate">
                                                <i class="bi bi-toggle-off"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                                <button type="button" class="btn btn-sm btn-danger" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#deleteModal{{ $category->id }}"
                                        @if($category->products()->exists()) disabled title="Cannot delete category with products" @endif>
                                    <i class="bi bi-trash"></i>
                                </button>

                                {{-- Delete Modal --}}
                                @if(!$category->products()->exists())
                                <div class="modal fade" id="deleteModal{{ $category->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Delete Category</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p>Are you sure you want to delete this category?</p>
                                                <p><strong>{{ $category->name }}</strong></p>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger">Delete</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endif
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
@endsection
