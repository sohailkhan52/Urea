@extends('layouts.admin')

@section('title', 'View User')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Users</a></li>
    <li class="breadcrumb-item active">{{ $user->name }}</li>
@endsection

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1><i class="bi bi-person-circle me-2"></i>User Details</h1>
            <p class="text-muted mb-0">View user information and permissions</p>
        </div>
        @can('users.update')
        <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-primary">
            <i class="bi bi-pencil me-2"></i>Edit User
        </a>
        @endcan
    </div>
</div>

<div class="row">
    <!-- User Profile -->
    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-body text-center">
                <img src="{{ $user->profile_image_url }}" 
                     alt="{{ $user->name }}" 
                     class="rounded-circle border mb-3" 
                     style="width: 150px; height: 150px; object-fit: cover;">
                
                <h4 class="mb-1">{{ $user->name }}</h4>
                <p class="text-muted mb-2">{{ $user->email }}</p>

                <!-- Status Badge -->
                @if($user->status === 'active')
                    <span class="badge bg-success mb-3">Active</span>
                @elseif($user->status === 'inactive')
                    <span class="badge bg-secondary mb-3">Inactive</span>
                @else
                    <span class="badge bg-danger mb-3">Suspended</span>
                @endif

                @if($user->id === Auth::id())
                    <span class="badge bg-primary mb-3">You</span>
                @endif

                <!-- Roles -->
                <div class="mb-3">
                    @forelse($user->roles as $role)
                        <span class="badge bg-{{ $role->is_super_admin ? 'danger' : 'primary' }} me-1">
                            {{ $role->name }}
                        </span>
                    @empty
                        <span class="text-muted">No role assigned</span>
                    @endforelse
                </div>

                <!-- Contact Info -->
                @if($user->phone)
                <div class="text-start">
                    <small class="text-muted d-block">Phone</small>
                    <div class="mb-2">
                        <i class="bi bi-telephone me-1"></i>{{ $user->phone }}
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Account Information -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Account Information</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="text-muted small">User ID</label>
                    <div>#{{ $user->id }}</div>
                </div>

                <div class="mb-3">
                    <label class="text-muted small">Status</label>
                    <div>
                        @if($user->status === 'active')
                            <span class="badge bg-success">Active</span>
                        @elseif($user->status === 'inactive')
                            <span class="badge bg-secondary">Inactive</span>
                        @else
                            <span class="badge bg-danger">Suspended</span>
                        @endif
                    </div>
                </div>

                <div class="mb-3">
                    <label class="text-muted small">Member Since</label>
                    <div>{{ $user->created_at->format('M d, Y') }}</div>
                    <small class="text-muted">{{ $user->created_at->diffForHumans() }}</small>
                </div>

                @if($user->last_login_at)
                <div class="mb-3">
                    <label class="text-muted small">Last Login</label>
                    <div>{{ $user->last_login_at->format('M d, Y h:i A') }}</div>
                    <small class="text-muted">{{ $user->last_login_at->diffForHumans() }}</small>
                </div>
                @else
                <div class="mb-3">
                    <label class="text-muted small">Last Login</label>
                    <div class="text-muted">Never logged in</div>
                </div>
                @endif

                <div class="mb-3">
                    <label class="text-muted small">Email Verified</label>
                    <div>
                        @if($user->email_verified_at)
                            <i class="bi bi-check-circle text-success me-1"></i>
                            Verified on {{ $user->email_verified_at->format('M d, Y') }}
                        @else
                            <i class="bi bi-x-circle text-danger me-1"></i>
                            Not verified
                        @endif
                    </div>
                </div>

                <div>
                    <label class="text-muted small">Last Updated</label>
                    <div>{{ $user->updated_at->format('M d, Y h:i A') }}</div>
                    <small class="text-muted">{{ $user->updated_at->diffForHumans() }}</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Roles and Permissions -->
    <div class="col-lg-8 mb-4">
        <!-- Roles Card -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-person-badge me-2"></i>Assigned Roles</h5>
            </div>
            <div class="card-body">
                @forelse($user->roles as $role)
                    <div class="border rounded p-3 mb-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-1">
                                    {{ $role->name }}
                                    @if($role->is_super_admin)
                                        <span class="badge bg-danger ms-2">Super Admin</span>
                                    @endif
                                </h6>
                                @if($role->description)
                                    <p class="text-muted small mb-0">{{ $role->description }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="alert alert-info mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        No roles assigned to this user
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Permissions Card -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-shield-check me-2"></i>Permissions</h5>
            </div>
            <div class="card-body">
                @if($user->isSuperAdmin())
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>Super Admin</strong> has access to all permissions
                    </div>
                @else
                    @php
                        $permissions = $user->getAllPermissions()->groupBy('group');
                    @endphp

                    @forelse($permissions as $group => $groupPermissions)
                        <div class="mb-3">
                            <h6 class="text-uppercase text-muted small mb-2">
                                {{ ucfirst($group) }}
                            </h6>
                            <div class="d-flex flex-wrap gap-1">
                                @foreach($groupPermissions as $permission)
                                    <span class="badge bg-primary">{{ $permission->name }}</span>
                                @endforeach
                            </div>
                        </div>
                    @empty
                        <div class="alert alert-info mb-0">
                            <i class="bi bi-info-circle me-2"></i>
                            No permissions assigned to this user's roles
                        </div>
                    @endforelse
                @endif
            </div>
        </div>

        <!-- Actions Card -->
        @can('users.update')
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-lightning me-2"></i>Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="row g-2">
                    @if($user->status !== 'active')
                    <div class="col-md-4">
                        <form action="{{ route('admin.users.activate', $user) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-success w-100">
                                <i class="bi bi-check-circle me-1"></i>Activate
                            </button>
                        </form>
                    </div>
                    @endif

                    @if($user->id !== Auth::id() && $user->status !== 'inactive')
                    <div class="col-md-4">
                        <form action="{{ route('admin.users.deactivate', $user) }}" method="POST"
                              onsubmit="return confirm('Deactivate this user?')">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-warning w-100">
                                <i class="bi bi-x-circle me-1"></i>Deactivate
                            </button>
                        </form>
                    </div>
                    @endif

                    @if($user->id !== Auth::id() && $user->status !== 'suspended')
                    <div class="col-md-4">
                        <form action="{{ route('admin.users.suspend', $user) }}" method="POST"
                              onsubmit="return confirm('Suspend this user?')">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-danger w-100">
                                <i class="bi bi-ban me-1"></i>Suspend
                            </button>
                        </form>
                    </div>
                    @endif

                    <div class="col-md-4">
                        <form action="{{ route('admin.users.reset-password', $user) }}" method="POST"
                              onsubmit="return confirm('Reset password for this user? A new temporary password will be generated.')">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-info w-100">
                                <i class="bi bi-key me-1"></i>Reset Password
                            </button>
                        </form>
                    </div>

                    <div class="col-md-4">
                        <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-primary w-100">
                            <i class="bi bi-pencil me-1"></i>Edit User
                        </a>
                    </div>

                    @can('users.delete')
                    @if($user->id !== Auth::id())
                    <div class="col-md-4">
                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST"
                              onsubmit="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger w-100">
                                <i class="bi bi-trash me-1"></i>Delete User
                            </button>
                        </form>
                    </div>
                    @endif
                    @endcan
                </div>
            </div>
        </div>
        @endcan
    </div>
</div>
@endsection
