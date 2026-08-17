@extends('layouts.admin')

@section('title', 'Create User')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Users</a></li>
    <li class="breadcrumb-item active">Create User</li>
@endsection

@section('content')
<div class="page-header">
    <h1><i class="bi bi-person-plus me-2"></i>Create User</h1>
    <p class="text-muted mb-0">Add a new user to the system</p>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.users.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <!-- Profile Image -->
                    <div class="mb-4 text-center">
                        <label class="form-label d-block">Profile Image</label>
                        <div class="mb-3">
                            <img src="https://ui-avatars.com/api/?name=New+User&color=fff&background=3498db" 
                                 alt="Profile Preview" 
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
                            <i class="bi bi-upload me-1"></i>Choose Image
                        </label>
                        @error('profile_image')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                        <small class="text-muted d-block mt-2">JPG, PNG, GIF - Max 2MB</small>
                    </div>

                    <hr class="my-4">

                    <!-- Name -->
                    <div class="mb-3">
                        <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control @error('name') is-invalid @enderror" 
                               id="name" 
                               name="name" 
                               value="{{ old('name') }}" 
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
                               value="{{ old('email') }}" 
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
                               value="{{ old('phone') }}" 
                               placeholder="+92 300 1234567">
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                            <input type="password" 
                                   class="form-control @error('password') is-invalid @enderror" 
                                   id="password" 
                                   name="password" 
                                   required>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @else
                                <small class="text-muted">Minimum 8 characters</small>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="password_confirmation" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                            <input type="password" 
                                   class="form-control" 
                                   id="password_confirmation" 
                                   name="password_confirmation" 
                                   required>
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="mb-3">
                        <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                        <select class="form-select @error('status') is-invalid @enderror" 
                                id="status" 
                                name="status" 
                                required>
                            <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            <option value="suspended" {{ old('status') == 'suspended' ? 'selected' : '' }}>Suspended</option>
                        </select>
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
                                           {{ is_array(old('roles')) && in_array($role->slug, old('roles')) ? 'checked' : '' }}>
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
                        <small class="text-muted">Select one or more roles for this user</small>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="border-top pt-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-2"></i>Create User
                        </button>
                        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle me-2"></i>Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Help Card -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Information</h5>
            </div>
            <div class="card-body">
                <h6>User Roles</h6>
                <p class="small text-muted">
                    Roles determine what actions a user can perform in the system. You can assign multiple roles to a user.
                </p>

                <h6 class="mt-3">User Status</h6>
                <ul class="small text-muted">
                    <li><strong>Active:</strong> User can log in and use the system</li>
                    <li><strong>Inactive:</strong> User account is disabled</li>
                    <li><strong>Suspended:</strong> User is temporarily blocked</li>
                </ul>

                <h6 class="mt-3">Password Policy</h6>
                <p class="small text-muted">
                    Passwords must be at least 8 characters long. Consider using a combination of letters, numbers, and symbols.
                </p>
            </div>
        </div>
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
