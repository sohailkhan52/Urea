@extends('layouts.admin')

@section('title', 'Welcome Page Settings')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="bi bi-gear me-2"></i>Welcome Page Settings</h1>
    </div>

    {{-- Validation Errors --}}
    @if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong><i class="bi bi-exclamation-triangle me-2"></i>Please fix the errors below:</strong>
        <ul class="mb-0 mt-2">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    {{-- Main Settings Card --}}
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-briefcase me-2"></i>Company Information</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.welcome-page.settings.update') }}" method="POST" enctype="multipart/form-data">
                @csrf

                {{-- Company Name --}}
                <div class="mb-4">
                    <label for="company_name" class="form-label fw-600">Company Name</label>
                    <input type="text" 
                           class="form-control @error('company_name') is-invalid @enderror" 
                           id="company_name" 
                           name="company_name" 
                           value="{{ old('company_name', $settings->company_name) }}" 
                           placeholder="e.g., Fertilizer Management System"
                           required>
                    @error('company_name')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted d-block mt-2">This name will be displayed on the welcome page header</small>
                </div>

                {{-- Company Short Name --}}
                <div class="mb-4">
                    <label for="company_short_name" class="form-label fw-600">Company Short Name</label>
                    <input type="text" 
                           class="form-control @error('company_short_name') is-invalid @enderror" 
                           id="company_short_name" 
                           name="company_short_name" 
                           value="{{ old('company_short_name', $settings->company_short_name) }}" 
                           placeholder="e.g., DN"
                           maxlength="10">
                    @error('company_short_name')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted d-block mt-2">Short name for the sidebar (max 10 characters)</small>
                </div>

                {{-- Hero Title --}}
                <div class="mb-4">
                    <label for="hero_title" class="form-label fw-600">Welcome Page Title</label>
                    <input type="text" 
                           class="form-control @error('hero_title') is-invalid @enderror" 
                           id="hero_title" 
                           name="hero_title" 
                           value="{{ old('hero_title', $settings->hero_title) }}" 
                           placeholder="e.g., Welcome to Your Company"
                           required>
                    @error('hero_title')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted d-block mt-2">This title appears on the welcome page hero section</small>
                </div>

                {{-- Company Logo --}}
                <div class="mb-4">
                    <label for="company_logo" class="form-label fw-600">Company Logo</label>
                    
                    <div class="mb-3">
                        @if ($settings->company_logo)
                        <div class="d-flex align-items-center gap-3">
                            <div class="position-relative" id="logo-preview">
                                <img src="{{ $settings->getLogoUrlAttribute() }}" 
                                     alt="Company Logo" 
                                     class="img-thumbnail" 
                                     style="max-width: 150px; height: auto;"
                                     id="current-logo">
                                <button type="button" 
                                        class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1" 
                                        onclick="markLogoForDeletion()"
                                        id="delete-logo-btn"
                                        style="padding: 0.25rem 0.5rem; line-height: 1;">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                                <div class="position-absolute top-0 start-0 w-100 h-100 bg-danger bg-opacity-75 d-none align-items-center justify-content-center" 
                                     id="deletion-overlay"
                                     style="border-radius: 0.375rem;">
                                    <span class="text-white fw-bold">Will be deleted</span>
                                </div>
                            </div>
                            <span class="text-muted"><i class="bi bi-check-circle me-2 text-success"></i>Logo uploaded</span>
                        </div>
                        @else
                        <div class="alert alert-info mb-0">
                            <i class="bi bi-info-circle me-2"></i>No logo uploaded yet. Upload an image to display on the welcome page.
                        </div>
                        @endif
                    </div>

                    <input type="file" 
                           class="form-control @error('company_logo') is-invalid @enderror" 
                           id="company_logo" 
                           name="company_logo" 
                           accept="image/*">
                    
                    {{-- Hidden field to mark logo for deletion --}}
                    <input type="hidden" name="delete_logo" id="delete_logo" value="0">
                    
                    @error('company_logo')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted d-block mt-2">Max file size: 2MB. Supported formats: JPEG, PNG, GIF, SVG</small>
                </div>

                <script>
                function markLogoForDeletion() {
                    const overlay = document.getElementById('deletion-overlay');
                    const deleteInput = document.getElementById('delete_logo');
                    const deleteBtn = document.getElementById('delete-logo-btn');
                    
                    if (deleteInput.value === '0') {
                        // Mark for deletion
                        deleteInput.value = '1';
                        overlay.classList.remove('d-none');
                        overlay.classList.add('d-flex');
                        deleteBtn.innerHTML = '<i class="bi bi-arrow-counterclockwise"></i>';
                        deleteBtn.classList.remove('btn-danger');
                        deleteBtn.classList.add('btn-warning');
                    } else {
                        // Unmark deletion
                        deleteInput.value = '0';
                        overlay.classList.add('d-none');
                        overlay.classList.remove('d-flex');
                        deleteBtn.innerHTML = '<i class="bi bi-x-lg"></i>';
                        deleteBtn.classList.remove('btn-warning');
                        deleteBtn.classList.add('btn-danger');
                    }
                }
                </script>

                {{-- Company Description --}}
                <div class="mb-4">
                    <label for="company_description" class="form-label fw-600">Company Description</label>
                    <textarea class="form-control @error('company_description') is-invalid @enderror" 
                              id="company_description" 
                              name="company_description" 
                              rows="4" 
                              placeholder="Enter a brief description of your company"
                              style="resize: vertical;">{{ old('company_description', isset($settings->company_description) ? $settings->company_description : '') }}</textarea>
                    @error('company_description')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted d-block mt-2">This description will appear on your welcome page</small>
                </div>

                {{-- Submit Button --}}
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle me-2"></i>Save Changes
                    </button>
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle me-2"></i>Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Info Box --}}
    <div class="alert alert-info mt-4" role="alert">
        <h6 class="alert-heading"><i class="bi bi-lightbulb me-2"></i>Note</h6>
        <p class="mb-0">These settings control the basic branding information displayed on your welcome page. All other welcome page elements remain static and are managed by the system.</p>
    </div>
</div>
@endsection
