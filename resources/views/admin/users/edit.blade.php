@extends('layouts.admin')

@section('title', 'Edit User')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Users</a></li>
    <li class="breadcrumb-item active">Edit User</li>
@endsection

@section('content')
<div class="page-header">
    <h1><i class="bi bi-pencil me-2"></i>Edit User</h1>
    <p class="text-muted mb-0">Update user information</p>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.users.update', $user) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <!-- Profile Image -->
                    <div class="mb-4 text-center">
                        <label class="form-label d-block">Profile Image</label>
                        <div class="mb-3">
                            <img src="{{ $user->profile_image_url }}" 
                                 alt="{{ $user->name }}" 
                                 class="rounded-circle border" 
                                 style="width: 120px; height: 120px; object-fit: cover;"
                                 id="profile-preview">
                        </div>
                        <input type="file" 
                               class="form-control d-none @error('profile_image') is-invalid @enderror" 
                               id="profile_image" 
                               name="profile_image"
                               accept="image/*"
                               onchange="previewImage(this)">
                        <label for="profile_image" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-upload me-1"></i>Change Image
                        </label>
                        @error('profile_image')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <hr class="my-4">

                    <!-- Name -->
                    <div class="mb-3">
                        <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control @error('name') is-invalid @enderror" 
                               id="name" 
                               name="name" 
                               value="{{ old('name', $user->name) }}" 
                               required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                        <input type="email" 
                               class="form-control @error('email') is-invalid @enderror" 
                               id="email" 
                               name="email" 
                               value="{{ old('email', $user->email) }}" 
                               required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Phone -->
                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone Number</label>
                        <input type="text" 
                               class="form-control @error('phone') is-invalid @enderror" 
                               id="phone" 
                               name="phone" 
                               value="{{ old('phone', $user->phone) }}" 
                               placeholder="+92 300 1234567">
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        Leave password fields empty if you don't want to change the password
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label">New Password</label>
                            <input type="password" 
                                   class="form-control @error('password') is-invalid @enderror" 
                                   id="password" 
                                   name="password">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @else
                                <small class="text-muted">Minimum 8 characters</small>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="password_confirmation" class="form-label">Confirm New Password</label>
                            <input type="password" 
                                   class="form-control" 
                                   id="password_confirmation" 
                                   name="password_confirmation">
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="mb-3">
                        <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                        <select class="form-select @error('status') is-invalid @enderror" 
                                id="status" 
                                name="status" 
                                required
                                {{ $user->id === Auth::id() ? 'disabled' : '' }}>
                            <option value="active" {{ old('status', $user->status) == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status', $user->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            <option value="suspended" {{ old('status', $user->status) == 'suspended' ? 'selected' : '' }}>Suspended</option>
                        </select>
                        @if($user->id === Auth::id())
                            <input type="hidden" name="status" value="{{ $user->status }}">
                            <small class="text-muted">You cannot change your own status</small>
                        @endif
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Roles -->
                    <div class="mb-4">
                        <label class="form-label">Roles</label>
                        <div class="border rounded p-3" style="max-height: 200px; overflow-y: auto;">
                            @foreach($roles as $role)
                                <div class="form-check">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           name="roles[]" 
                                           value="{{ $role->slug }}" 
                                           id="role-{{ $role->id }}"
                                           {{ (is_array(old('roles')) ? in_array($role->slug, old('roles')) : $user->hasRole($role->slug)) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="role-{{ $role->id }}">
                                        {{ $role->name }}
                                        @if($role->description)
                                            <br><small class="text-muted">{{ $role->description }}</small>
                                        @endif
                                    </label>
                                </div>
                            @endforeach
                        </div>
                        @error('roles')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Submit Buttons -->
                    <div class="border-top pt-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-2"></i>Update User
                        </button>
                        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle me-2"></i>Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Info Card -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-person-circle me-2"></i>User Information</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="text-muted small">User ID</label>
                    <div>#{{ $user->id }}</div>
                </div>

                <div class="mb-3">
                    <label class="text-muted small">Member Since</label>
                    <div>{{ $user->created_at->format('M d, Y') }}</div>
                </div>

                @if($user->last_login_at)
                <div class="mb-3">
                    <label class="text-muted small">Last Login</label>
                    <div>{{ $user->last_login_at->format('M d, Y h:i A') }}</div>
                    <small class="text-muted">{{ $user->last_login_at->diffForHumans() }}</small>
                </div>
                @endif

                @if($user->email_verified_at)
                <div class="mb-3">
                    <label class="text-muted small">Email Verified</label>
                    <div>
                        <i class="bi bi-check-circle text-success me-1"></i>
                        Verified
                    </div>
                </div>
                @endif

                <div class="mb-3">
                    <label class="text-muted small">Current Roles</label>
                    <div>
                        @forelse($user->roles as $role)
                            <span class="badge bg-primary me-1">{{ $role->name }}</span>
                        @empty
                            <span class="text-muted">No roles assigned</span>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        @can('users.delete')
        @if($user->id !== Auth::id())
        <div class="card border-danger">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0"><i class="bi bi-exclamation-triangle me-2"></i>Danger Zone</h5>
            </div>
            <div class="card-body">
                <p class="small mb-2">Permanently delete this user account.</p>
                <form action="{{ route('admin.users.destroy', $user) }}" method="POST" 
                      onsubmit="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm w-100">
                        <i class="bi bi-trash me-1"></i>Delete User
                    </button>
                </form>
            </div>
        </div>
        @endif
        @endcan
    </div>
</div>
@endsection

@push('scripts')
<script>
function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            document.getElementById('profile-preview').src = e.target.result;
        }
        
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endpush
