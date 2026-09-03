@extends('layouts.admin')

@section('title', 'Profile Settings')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
    <li class="breadcrumb-item active">Profile Settings</li>
@endsection

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1><i class="bi bi-person-circle me-2"></i>Profile Settings</h1>
            <p class="text-muted mb-0">Manage your account information and security</p>
        </div>
        @if(auth()->user()->isSuperAdmin())
        <div>
            <span class="badge bg-danger">
                <i class="bi bi-shield-fill me-1"></i>Super Admin
            </span>
        </div>
        @endif
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i><strong>Error!</strong>
        <ul class="mb-0 mt-2">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row">
    <!-- Profile Information -->
    <div class="col-lg-8 mb-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-light border-bottom">
                <h5 class="mb-0"><i class="bi bi-person me-2"></i>Profile Information</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" id="profile-form">
                    @csrf
                    @method('PUT')

                    <!-- Profile Image -->
                    <div class="mb-4 text-center pb-4 border-bottom">
                        <div class="mb-3">
                            @php
                                if ($user->profile_image) {
                                    $imageUrl = asset('storage/' . $user->profile_image);
                                } else {
                                    $imageUrl = 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&color=fff&background=3498db&size=150';
                                }
                            @endphp
                            <img src="{{ $imageUrl }}" 
                                 alt="{{ $user->name }}" 
                                 class="rounded-circle border border-3 border-primary" 
                                 style="width: 140px; height: 140px; object-fit: cover;"
                                 id="profile-preview">
                        </div>
                        <div>
                            <input type="file" 
                                   class="form-control d-none @error('profile_image') is-invalid @enderror" 
                                   id="profile_image" 
                                   name="profile_image"
                                   accept="image/*"
                                   onchange="previewImage(this)">
                            <label for="profile_image" class="btn btn-sm btn-primary me-2">
                                <i class="bi bi-upload me-1"></i>Change Image
                            </label>
                            @if($user->profile_image)
                                <button type="button" class="btn btn-sm btn-outline-danger" 
                                        onclick="deleteProfileImage()">
                                    <i class="bi bi-trash me-1"></i>Remove
                                </button>
                            @endif
                        </div>
                        @error('profile_image')
                            <div class="alert alert-danger small mt-2 mb-0">{{ $message }}</div>
                        @enderror
                        <small class="text-muted d-block mt-2">Recommended: Square image, max 2MB (JPG, PNG, GIF)</small>
                    </div>

                    <!-- Profile Fields -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label fw-bold">Full Name <span class="text-danger">*</span></label>
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

                        <div class="col-md-6">
                            <label for="email" class="form-label fw-bold">Email Address <span class="text-danger">*</span></label>
                            <input type="email" 
                                   class="form-control @error('email') is-invalid @enderror" 
                                   id="email" 
                                   name="email" 
                                   value="{{ old('email', $user->email) }}" 
                                   required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">This is your contact email and is used for support</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="phone" class="form-label fw-bold">Phone Number</label>
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

                    <div class="d-flex gap-2 pt-3 border-top">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-2"></i>Save Changes
                        </button>
                        <button type="reset" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle me-2"></i>Discard
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Account Status & Info -->
    <div class="col-lg-4 mb-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-light border-bottom">
                <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Account Information</h5>
            </div>
            <div class="card-body">
                <div class="mb-4">
                    <label class="text-muted small fw-bold">Account Status</label>
                    <div class="mt-2">
                        @if($user->status === 'active')
                            <span class="badge bg-success">
                                <i class="bi bi-check-circle me-1"></i>Active
                            </span>
                        @elseif($user->status === 'inactive')
                            <span class="badge bg-secondary">
                                <i class="bi bi-pause-circle me-1"></i>Inactive
                            </span>
                        @else
                            <span class="badge bg-danger">
                                <i class="bi bi-x-circle me-1"></i>Suspended
                            </span>
                        @endif
                    </div>
                </div>

                <hr>

                <div class="mb-4">
                    <label class="text-muted small fw-bold">Member Since</label>
                    <div class="mt-2">
                        <i class="bi bi-calendar-event me-2 text-primary"></i>
                        @if($user->created_at)
                            {{ $user->created_at->format('M d, Y') }}
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </div>
                </div>

                @if($user->last_login_at)
                    <div class="mb-4">
                        <label class="text-muted small fw-bold">Last Login</label>
                        <div class="mt-2">
                            <i class="bi bi-clock-history me-2 text-info"></i>
                            {{ $user->last_login_at->format('M d, Y h:i A') }}
                        </div>
                    </div>
                @endif

                @if($user->email_verified_at)
                    <div>
                        <label class="text-muted small fw-bold">Email Verification</label>
                        <div class="mt-2">
                            <i class="bi bi-check-circle text-success me-1"></i>
                            <span class="text-success">Verified</span>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        @if(auth()->user()->isSuperAdmin())
        <div class="card shadow-sm border-0 border-danger mt-3">
            <div class="card-header bg-danger bg-opacity-10 border-danger">
                <h5 class="mb-0 text-danger">
                    <i class="bi bi-shield-fill me-2"></i>Super Admin Privileges
                </h5>
            </div>
            <div class="card-body">
                <p class="small text-muted mb-3">As a Super Admin, you have full access to:</p>
                <ul class="small mb-0 list-unstyled">
                    <li><i class="bi bi-check-circle text-success me-2"></i>System management</li>
                    <li><i class="bi bi-check-circle text-success me-2"></i>User management</li>
                    <li><i class="bi bi-check-circle text-success me-2"></i>All features</li>
                    <li><i class="bi bi-check-circle text-success me-2"></i>System configuration</li>
                </ul>
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Change Password -->
<div class="row mt-4">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-light border-bottom">
                <h5 class="mb-0"><i class="bi bi-shield-lock me-2"></i>Change Password</h5>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-4">
                    <i class="bi bi-info-circle me-2"></i>Keep your account secure by using a strong password with letters, numbers, and symbols.
                </p>

                <form action="{{ route('profile.password.update') }}" method="POST" id="password-form">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="current_password" class="form-label fw-bold">Current Password <span class="text-danger">*</span></label>
                        <input type="password" 
                               class="form-control @error('current_password') is-invalid @enderror" 
                               id="current_password" 
                               name="current_password" 
                               placeholder="Enter your current password"
                               required>
                        @error('current_password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label fw-bold">New Password <span class="text-danger">*</span></label>
                            <input type="password" 
                                   class="form-control @error('password') is-invalid @enderror" 
                                   id="password" 
                                   name="password" 
                                   placeholder="Enter new password"
                                   required>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @else
                                <small class="text-muted d-block mt-1">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Minimum 8 characters, mix of letters, numbers, and symbols recommended
                                </small>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="password_confirmation" class="form-label fw-bold">Confirm Password <span class="text-danger">*</span></label>
                            <input type="password" 
                                   class="form-control" 
                                   id="password_confirmation" 
                                   name="password_confirmation" 
                                   placeholder="Re-enter new password"
                                   required>
                        </div>
                    </div>

                    <div class="alert alert-info" role="alert">
                        <i class="bi bi-lightbulb me-2"></i>
                        <strong>Password Strength:</strong> Your new password must be different from your current password.
                    </div>

                    <div class="d-flex gap-2 pt-3 border-top">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-shield-check me-2"></i>Update Password
                        </button>
                        <button type="reset" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle me-2"></i>Clear Form
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
        // Validate file size
        if (input.files[0].size > 2 * 1024 * 1024) {
            alert('File size must not exceed 2MB');
            input.value = '';
            return;
        }

        const reader = new FileReader();
        
        reader.onload = function(e) {
            document.getElementById('profile-preview').src = e.target.result;
        }
        
        reader.readAsDataURL(input.files[0]);
    }
}

function deleteProfileImage() {
    if (!confirm('Are you sure you want to remove your profile image?')) {
        return;
    }

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("profile.image.delete") }}';
    form.innerHTML = '@csrf @method("DELETE")';
    document.body.appendChild(form);
    form.submit();
}

// Form submission feedback
document.getElementById('profile-form')?.addEventListener('submit', function() {
    const submitBtn = this.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';
});

document.getElementById('password-form')?.addEventListener('submit', function() {
    const submitBtn = this.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Updating...';
});
</script>
@endpush
