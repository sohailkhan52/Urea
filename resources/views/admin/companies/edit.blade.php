@extends('layouts.admin')

@section('title', 'Edit Company')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <h1 class="h3 mb-0">Edit Company</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.companies.index') }}">Companies</a></li>
                <li class="breadcrumb-item active">Edit: {{ $company->name }}</li>
            </ol>
        </nav>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Company Information</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.companies.update', $company) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        {{-- Name --}}
                        <div class="mb-3">
                            <label for="name" class="form-label">Company Name <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name', $company->name) }}"
                                   required
                                   autofocus>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Code --}}
                        <div class="mb-3">
                            <label for="code" class="form-label">Company Code <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('code') is-invalid @enderror" 
                                   id="code" 
                                   name="code" 
                                   value="{{ old('code', $company->code) }}"
                                   required
                                   style="text-transform: uppercase;">
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Unique code (letters, numbers, dashes, underscores)</div>
                        </div>

                        {{-- Contact Person --}}
                        <div class="mb-3">
                            <label for="contact_person" class="form-label">Contact Person</label>
                            <input type="text" 
                                   class="form-control @error('contact_person') is-invalid @enderror" 
                                   id="contact_person" 
                                   name="contact_person" 
                                   value="{{ old('contact_person', $company->contact_person) }}">
                            @error('contact_person')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            {{-- Phone --}}
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Phone</label>
                                <input type="text" 
                                       class="form-control @error('phone') is-invalid @enderror" 
                                       id="phone" 
                                       name="phone" 
                                       value="{{ old('phone', $company->phone) }}">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Email --}}
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" 
                                       class="form-control @error('email') is-invalid @enderror" 
                                       id="email" 
                                       name="email" 
                                       value="{{ old('email', $company->email) }}">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Website --}}
                        <div class="mb-3">
                            <label for="website" class="form-label">Website</label>
                            <input type="url" 
                                   class="form-control @error('website') is-invalid @enderror" 
                                   id="website" 
                                   name="website" 
                                   value="{{ old('website', $company->website) }}"
                                   placeholder="https://www.company.com">
                            @error('website')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Address --}}
                        <div class="mb-3">
                            <label for="address" class="form-label">Address</label>
                            <textarea class="form-control @error('address') is-invalid @enderror" 
                                      id="address" 
                                      name="address" 
                                      rows="3">{{ old('address', $company->address) }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Current Logo --}}
                        @if($company->logo)
                        <div class="mb-3">
                            <label class="form-label">Current Logo</label>
                            <div>
                                <img src="{{ $company->logo_url }}" 
                                     alt="{{ $company->name }}" 
                                     class="img-thumbnail" 
                                     style="max-width: 150px;">
                            </div>
                        </div>
                        @endif

                        {{-- Logo --}}
                        <div class="mb-3">
                            <label for="logo" class="form-label">
                                {{ $company->logo ? 'Update Logo' : 'Upload Logo' }}
                            </label>
                            <input type="file" 
                                   class="form-control @error('logo') is-invalid @enderror" 
                                   id="logo" 
                                   name="logo"
                                   accept="image/*">
                            @error('logo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                Max 2MB. Supported formats: JPG, PNG, GIF, SVG
                                @if($company->logo)
                                    <br><span class="text-muted">Leave empty to keep current logo</span>
                                @endif
                            </div>
                        </div>

                        {{-- Logo Preview --}}
                        <div class="mb-3" id="logoPreview" style="display: none;">
                            <label class="form-label">New Logo Preview</label>
                            <div>
                                <img id="logoPreviewImage" src="" alt="Logo Preview" class="img-thumbnail" style="max-width: 200px;">
                            </div>
                        </div>

                        {{-- Status --}}
                        <div class="mb-4">
                            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                <option value="active" {{ old('status', $company->status) === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status', $company->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Buttons --}}
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i> Update Company
                            </button>
                            <a href="{{ route('admin.companies.index') }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle me-1"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Danger Zone --}}
            @can('companies.delete')
            <div class="card border-danger mt-4">
                <div class="card-header bg-danger text-white">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-exclamation-triangle me-1"></i> Danger Zone
                    </h5>
                </div>
                <div class="card-body">
                    <p class="mb-3">
                        <strong>Delete Company:</strong> Once deleted, this action cannot be undone. 
                        Companies with associated products or transactions cannot be deleted.
                    </p>
                    <form action="{{ route('admin.companies.destroy', $company) }}" 
                          method="POST"
                          onsubmit="return confirm('Are you sure you want to delete this company? This action cannot be undone.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash me-1"></i> Delete Company
                        </button>
                    </form>
                </div>
            </div>
            @endcan
        </div>

        {{-- Company Info Card --}}
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-info-circle me-1"></i> Company Details
                    </h5>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4 small">Created:</dt>
                        <dd class="col-sm-8 small text-muted">{{ $company->created_at->format('M d, Y') }}</dd>

                        <dt class="col-sm-4 small">Updated:</dt>
                        <dd class="col-sm-8 small text-muted">{{ $company->updated_at->format('M d, Y') }}</dd>

                        <dt class="col-sm-4 small">Status:</dt>
                        <dd class="col-sm-8 small">
                            @if($company->status === 'active')
                            <span class="badge bg-success">Active</span>
                            @else
                            <span class="badge bg-warning">Inactive</span>
                            @endif
                        </dd>
                    </dl>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-lightbulb me-1"></i> Tips
                    </h5>
                </div>
                <div class="card-body">
                    <ul class="small text-muted mb-0">
                        <li>Company code must be unique</li>
                        <li>Upload high-quality logos for best results</li>
                        <li>Keep contact information up to date</li>
                        <li>Use short, memorable company codes</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Logo preview
document.getElementById('logo').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('logoPreviewImage').src = e.target.result;
            document.getElementById('logoPreview').style.display = 'block';
        };
        reader.readAsDataURL(file);
    } else {
        document.getElementById('logoPreview').style.display = 'none';
    }
});

// Auto-uppercase company code
document.getElementById('code').addEventListener('input', function(e) {
    e.target.value = e.target.value.toUpperCase();
});
</script>
@endpush
@endsection
