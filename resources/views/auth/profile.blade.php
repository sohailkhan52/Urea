@extends('layouts.admin')

@section('title', 'Profile Settings')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
    <li class="breadcrumb-item active">Profile Settings</li>
@endsection

@section('content')
<div class="page-header">
    <h1><i class="bi bi-person-circle me-2"></i>Profile Settings</h1>
    <p class="text-muted mb-0">Manage your account information and security</p>
</div>

<div class="row">
    <!-- Profile Information -->
    <div class="col-lg-8 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-person me-2"></i>Profile Information</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <!-- Profile Image -->
                    <div class="mb-4 text-center">
                        <div class="mb-3">
                            <img src="{{ $user->profile_image_url }}" 
                                 alt="{{ $user->name }}" 
                                 class="rounded-circle border" 
                                 style="width: 120px; height: 120px; object-fit: cover;"
                                 id="profile-preview">
                        </div>
                        <div>
                            <input type="file" 
                                   class="form-control d-none @error('profile_image') is-invalid @enderror" 
                                   id="profile_image" 
                                   name="profile_image"
                                   accept="image/*"
                                   onchange="previewImage(this)">
                            <label for="profile_image" class="btn btn-sm btn-outline-primary me-2">
                                <i class="bi bi-upload me-1"></i>Change Image
                            </label>
                            @if($user->profile_image)
                                <form action="{{ route('profile.image.delete') }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" 
                                            onclick="return confirm('Remove profile image?')">
                                        <i class="bi bi-trash me-1"></i>Remove
                                    </button>
                                </form>
                            @endif
                        </div>
                        @error('profile_image')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                        <small class="text-muted d-block mt-2">JPG, PNG, GIF - Max 2MB</small>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
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

                        <div class="col-md-6 mb-3">
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
                    </div>

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

                    <div class="border-top pt-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-2"></i>Update Profile
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Account Status & Info -->
    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Account Status</h5>
            </div>
            <div class="card-body">
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
                </div>

                @if($user->last_login_at)
                    <div class="mb-3">
                        <label class="text-muted small">Last Login</label>
                        <div>{{ $user->last_login_at->format('M d, Y h:i A') }}</div>
                    </div>
                @endif

                @if($user->email_verified_at)
                    <div>
                        <label class="text-muted small">Email Verified</label>
                        <div>
                            <i class="bi bi-check-circle text-success me-1"></i>
                            Verified
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Change Password -->
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-shield-lock me-2"></i>Change Password</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('profile.password.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="current_password" class="form-label">Current Password <span class="text-danger">*</span></label>
                        <input type="password" 
                               class="form-control @error('current_password') is-invalid @enderror" 
                               id="current_password" 
                               name="current_password" 
                               required>
                        @error('current_password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label">New Password <span class="text-danger">*</span></label>
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

                    <div class="border-top pt-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-shield-check me-2"></i>Change Password
                        </button>
                    </div>
                </form>
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
