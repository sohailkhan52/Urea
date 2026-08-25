@extends('layouts.admin')

@section('title', 'Users')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
    <li class="breadcrumb-item active">Users</li>
@endsection

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1><i class="bi bi-people me-2"></i>Users</h1>
            <p class="text-muted mb-0">Manage system users and their access</p>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('admin.users.index') }}" method="GET">
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="search" class="form-label">Search</label>
                    <input type="text" class="form-control" id="search" name="search" 
                           value="{{ request('search') }}" placeholder="Name, email, or phone...">
                </div>
                <div class="col-md-3">
                    <label for="role" class="form-label">Role</label>
                    <select class="form-select" id="role" name="role">
                        <option value="">All Roles</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->slug }}" {{ request('role') == $role->slug ? 'selected' : '' }}>
                                {{ $role->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>Suspended</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-filter me-1"></i>Filter
                    </button>
                </div>
            </div>
        </form>
        @if(request()->hasAny(['search', 'role', 'status']))
            <div class="mt-2">
                <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-x-circle me-1"></i>Clear Filters
                </a>
            </div>
        @endif
    </div>
</div>

<!-- Users Table -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive" style="overflow: visible;">
            <div style="overflow-x: auto;">
                <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Roles</th>
                        <th>Warehouses</th>
                        <th>Status</th>
                        <th>Last Login</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <img src="{{ $user->profile_image_url }}" 
                                         alt="{{ $user->name }}" 
                                         class="rounded-circle" 
                                         style="width: 40px; height: 40px; object-fit: cover;">
                                    <div>
                                        <div class="fw-semibold">{{ $user->name }}</div>
                                        @if($user->id === Auth::id())
                                            <span class="badge bg-primary">You</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->phone ?: '—' }}</td>
                            <td>
                                @foreach($user->roles as $role)
                                    <span class="badge bg-{{ $role->is_super_admin ? 'danger' : 'primary' }} me-1">
                                        {{ $role->name }}
                                    </span>
                                @endforeach
                                @if($user->roles->isEmpty())
                                    <span class="text-muted">No role</span>
                                @endif
                            </td>
                            <td>
                                @if($user->isSuperAdmin())
                                    <span class="badge bg-info">All Warehouses</span>
                                @else
                                    @php $warehouses = $user->warehouses; @endphp
                                    @if($warehouses->isNotEmpty())
                                        <div class="d-flex flex-wrap gap-1">
                                            @foreach($warehouses as $warehouse)
                                                <span class="badge bg-secondary">{{ $warehouse->name }}</span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                @endif
                            </td>
                            <td>
                                @if($user->status === 'active')
                                    <span class="badge bg-success">Active</span>
                                @elseif($user->status === 'inactive')
                                    <span class="badge bg-secondary">Inactive</span>
                                @else
                                    <span class="badge bg-danger">Suspended</span>
                                @endif
                            </td>
                            <td>
                                @if($user->last_login_at)
                                    <small>{{ $user->last_login_at->diffForHumans() }}</small>
                                @else
                                    <span class="text-muted">Never</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    @can('users.view')
                                    <a href="{{ route('admin.users.show', $user) }}" 
                                       class="btn btn-info" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @endcan

                                    @can('users.update')
                                    <a href="{{ route('admin.users.edit', $user) }}" 
                                       class="btn btn-primary" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    @endcan

                                    @can('users.delete')
                                    @if($user->id !== Auth::id())
                                    <button type="button" class="btn btn-danger" title="Delete"
                                            onclick="deleteUser({{ $user->id }})">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    @endif
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="bi bi-inbox display-4 d-block mb-2" style="opacity: 0.3;"></i>
                                No users found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            </div>
        </div>

        <!-- Pagination -->
        <div class="mt-3">
            {{ $users->links() }}
        </div>
    </div>
</div>

@can('users.delete')
<!-- Delete Form (hidden) -->
<form id="deleteForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>
@endcan
@endsection

@push('scripts')
@can('users.delete')
<script>
function deleteUser(userId) {
    if (confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
        const form = document.getElementById('deleteForm');
        form.action = `/admin/users/${userId}`;
        form.submit();
    }
}
</script>
@endcan
@endpush
