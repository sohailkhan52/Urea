@extends('layouts.admin')

@section('title', 'Families Management')

@section('content')
<style>
    body.modal-open .sidebar-edge-toggle {
        display: none !important;
    }

    .family-pagination {
        display: flex;
        justify-content: flex-end;
        padding-top: .75rem;
    }

    .family-pagination nav {
        max-width: 100%;
        overflow-x: auto;
    }

    .family-pagination .pagination {
        flex-wrap: nowrap;
        margin-bottom: 0;
    }

    .family-pagination .page-link {
        min-width: 38px;
        text-align: center;
    }

    @media (max-width: 575.98px) {
        .family-page {
            min-width: 0;
            padding: .75rem !important;
        }

        .family-page-header {
            margin-bottom: .85rem;
            padding: .75rem !important;
        }

        .family-page-header-inner {
            flex-wrap: wrap;
            gap: .4rem;
        }

        .family-page-header-inner > div:first-child {
            flex: 0 0 100%;
            min-width: 0;
        }

        .family-page-header h1 {
            font-size: 1.25rem;
            line-height: 1.2;
        }

        .family-page-header p {
            font-size: .78rem;
        }

        .family-page-header .btn {
            padding: .35rem .5rem;
            font-size: .76rem;
        }

        .family-per-page {
            margin-bottom: .4rem !important;
        }

        .family-table-card .card-header {
            padding: .6rem .75rem;
        }

        .family-table-card .card-header h5 {
            font-size: 1rem;
        }

        .family-table-card .card-body {
            padding: .65rem !important;
        }

        .family-table {
            min-width: 680px;
            margin-bottom: 0;
            font-size: .78rem;
        }

        .family-table th,
        .family-table td {
            padding: .55rem .65rem;
            vertical-align: middle;
        }

        .family-table td:last-child .btn {
            padding: .25rem .4rem;
        }

        .family-pagination {
            justify-content: center;
            overflow-x: auto;
            padding: .65rem .25rem 0;
        }

        .family-pagination .page-link {
            min-width: 34px;
            padding: .35rem .5rem;
            font-size: .82rem;
        }

        .family-modal-dialog {
            width: calc(100% - 2rem);
            max-width: none;
            margin: 1rem auto;
        }

        .family-modal-content {
            max-height: calc(100dvh - 2rem);
            overflow: hidden;
        }

        .family-modal-content .modal-header,
        .family-modal-content .modal-footer {
            flex-shrink: 0;
            padding: .55rem .75rem;
        }

        .family-modal-content .modal-title {
            font-size: .98rem;
        }

        .family-modal-content .modal-body {
            min-height: 0;
            overflow-y: auto;
            padding: .65rem .75rem;
        }

        .family-modal-content .form-label {
            margin-bottom: .2rem;
            font-size: .78rem;
        }

        .family-modal-content .form-control {
            min-height: 36px;
            padding: .3rem .5rem;
            font-size: .82rem;
        }

        .family-modal-content textarea.form-control {
            min-height: 60px;
        }

        .family-modal-content .modal-footer .btn {
            padding: .32rem .5rem;
            font-size: .76rem;
        }
    }

    @media (min-width: 576px) and (max-width: 991.98px) {
        .family-page-header {
            padding: 1rem !important;
        }

        .family-page-header-inner {
            gap: .75rem;
        }

        .family-page-header h1 {
            font-size: 1.45rem;
        }

        .family-page-header p {
            font-size: .85rem;
        }

        .family-page-header .btn {
            padding: .4rem .6rem;
            font-size: .82rem;
        }

        .family-table {
            min-width: 800px;
        }

        .family-table th,
        .family-table td {
            padding: .65rem .75rem;
        }
    }
</style>
<div class="container-fluid family-page">
    {{-- Page Header --}}
    <div class="page-header family-page-header">
        <div class="d-flex justify-content-between align-items-center family-page-header-inner">
            <div>
                <h1><i class="bi bi-people me-2"></i>Families Management</h1>
                <p class="text-muted mb-0">Manage customer families and groups</p>
            </div>
            <button type="button" class="btn btn-primary family-add-button" data-bs-toggle="modal" data-bs-target="#newFamilyModal">
                <i class="bi bi-plus-lg me-2"></i> Add New Family
            </button>
        </div>
    </div>

    <div class="d-flex justify-content-end align-items-center mb-2 family-per-page">
        <form action="{{ route('admin.families.index') }}" method="GET" class="d-flex align-items-center gap-2">
            @foreach(request()->except(['page', 'per_page']) as $key => $value)
                @if(is_scalar($value))
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endif
            @endforeach
            <label for="families-per-page" class="small text-muted mb-0">Per Page</label>
            <select id="families-per-page" name="per_page" class="form-select form-select-sm" style="width: 82px;" onchange="this.form.submit()">
                @foreach([10, 25, 50, 100] as $option)
                    <option value="{{ $option }}" @selected($perPage == $option)>{{ $option }}</option>
                @endforeach
            </select>
        </form>
    </div>

    {{-- Families Table --}}
    <div class="card family-table-card">
        <div class="card-header">
            <h5 class="mb-0">All Families</h5>
        </div>
        <div class="card-body">
            @if($families->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover family-table">
                        <thead class="table-light">
                            <tr>
                                <th>Family Name</th>
                                <th>Family Code</th>
                                <th>Members</th>
                                <th>Description</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($families as $family)
                            <tr>
                                <td>
                                    <strong>{{ $family->name }}</strong>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark">{{ $family->family_code }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ $family->total_members }}</span>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        @if($family->notes)
                                            {{ $family->notes }}
                                        @else
                                            <em>—</em>
                                        @endif
                                    </small>
                                </td>
                                <td>
                                    @if($family->status === 'active')
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-warning">Inactive</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-primary" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editFamilyModal"
                                            onclick="loadFamilyForEdit({{ $family->id }}, '{{ $family->name }}', '{{ $family->family_code }}', '{{ $family->address ?? '' }}', '{{ $family->city ?? '' }}', '{{ $family->village ?? '' }}', '{{ $family->notes ?? '' }}', '{{ $family->status }}')">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-danger" 
                                            onclick="deleteFamily({{ $family->id }}, '{{ $family->name }}')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="d-flex justify-content-end align-items-center flex-wrap gap-2 family-pagination">
                    {{ $families->links() }}
                </div>
            @else
                <div class="alert alert-info mb-0">
                    <i class="bi bi-info-circle me-2"></i> No families created yet. Click "Add New Family" to create one.
                </div>
            @endif
        </div>
    </div>
</div>

@push('modals')
<!-- New Family Modal -->
<div class="modal fade family-modal" id="newFamilyModal" tabindex="-1">
    <div class="modal-dialog family-modal-dialog">
        <div class="modal-content family-modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New Family</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="newFamilyForm">
                    @csrf
                    <div class="mb-3">
                        <label for="new_family_name" class="form-label">Family Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="new_family_name" name="name" required maxlength="100" placeholder="Enter family name">
                    </div>
                    <div class="mb-3">
                        <label for="new_family_notes" class="form-label">Description</label>
                        <textarea class="form-control" id="new_family_notes" name="notes" rows="3" maxlength="500" placeholder="Enter description (optional)"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="createFamily()">Create Family</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Family Modal -->
<div class="modal fade family-modal" id="editFamilyModal" tabindex="-1">
    <div class="modal-dialog family-modal-dialog">
        <div class="modal-content family-modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Family</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editFamilyForm">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="edit_family_id" name="id">
                    <div class="mb-3">
                        <label for="edit_family_name" class="form-label">Family Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_family_name" name="name" required maxlength="100">
                    </div>
                    <div class="mb-3">
                        <label for="edit_family_notes" class="form-label">Description</label>
                        <textarea class="form-control" id="edit_family_notes" name="notes" rows="3" maxlength="500" placeholder="Enter description (optional)"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="edit_family_status" class="form-label">Status</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="edit_family_status" name="status" value="active">
                            <label class="form-check-label" for="edit_family_status">
                                Active
                            </label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="updateFamily()">Save Changes</button>
            </div>
        </div>
    </div>
</div>
@endpush

@push('scripts')
<script>
/* global FormData */

// Create Family
function createFamily() {
    const nameInput = document.getElementById('new_family_name');
    const form = document.getElementById('newFamilyForm');
    
    // Clear previous error
    nameInput.classList.remove('is-invalid');
    const existingError = nameInput.parentElement.querySelector('.invalid-feedback');
    if (existingError) {
        existingError.remove();
    }
    
    // Validate name is not empty
    if (!nameInput.value.trim()) {
        nameInput.classList.add('is-invalid');
        const errorDiv = document.createElement('div');
        errorDiv.className = 'invalid-feedback d-block';
        errorDiv.textContent = 'Family name is required';
        nameInput.parentElement.appendChild(errorDiv);
        return;
    }
    
    const formData = new FormData(form);
    
    fetch('/admin/families', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success || data.family) {
            // Show success message
            showAlert('success', 'Family created successfully!');
            
            // Close modal
            bootstrap.Modal.getInstance(document.getElementById('newFamilyModal')).hide();
            
            // Reset form
            form.reset();
            nameInput.classList.remove('is-invalid');
            
            // Reload page after 1 second
            setTimeout(() => location.reload(), 1000);
        } else {
            showAlert('danger', 'Error: ' + (data.message || 'Failed to create family'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('danger', 'Error creating family. Please try again.');
    });
}

// Load Family for Edit
function loadFamilyForEdit(id, name, code, address, city, village, notes, status) {
    document.getElementById('edit_family_id').value = id;
    document.getElementById('edit_family_name').value = name;
    document.getElementById('edit_family_notes').value = notes;
    
    // Set checkbox for status
    const statusCheckbox = document.getElementById('edit_family_status');
    statusCheckbox.checked = (status === 'active');
}

// Update Family
function updateFamily() {
    const nameInput = document.getElementById('edit_family_name');
    const form = document.getElementById('editFamilyForm');
    
    // Clear previous error
    nameInput.classList.remove('is-invalid');
    const existingError = nameInput.parentElement.querySelector('.invalid-feedback');
    if (existingError) {
        existingError.remove();
    }
    
    // Validate name is not empty
    if (!nameInput.value.trim()) {
        nameInput.classList.add('is-invalid');
        const errorDiv = document.createElement('div');
        errorDiv.className = 'invalid-feedback d-block';
        errorDiv.textContent = 'Family name is required';
        nameInput.parentElement.appendChild(errorDiv);
        return;
    }
    
    const familyId = document.getElementById('edit_family_id').value;
    const formData = new FormData(form);
    
    // Handle checkbox - if checked, set to 'active', otherwise 'inactive'
    const statusCheckbox = document.getElementById('edit_family_status');
    formData.set('status', statusCheckbox.checked ? 'active' : 'inactive');
    
    fetch(`/admin/families/${familyId}`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            // Show success message
            showAlert('success', 'Family updated successfully!');
            
            // Close modal
            bootstrap.Modal.getInstance(document.getElementById('editFamilyModal')).hide();
            
            // Reload page after 1 second
            setTimeout(() => location.reload(), 1000);
        } else {
            showAlert('danger', 'Error: ' + (data.message || 'Failed to update family'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('danger', 'Error updating family. Please try again.');
    });
}

// Delete Family
function deleteFamily(id, name) {
    if (confirm(`Are you sure you want to delete the family "${name}"? This action cannot be undone.`)) {
        fetch(`/admin/families/${id}`, {
            method: 'DELETE',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showAlert('success', 'Family deleted successfully!');
                
                // Reload page after 1 second
                setTimeout(() => location.reload(), 1000);
            } else {
                showAlert('danger', 'Error: ' + (data.message || 'Failed to delete family'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('danger', 'Error deleting family. Please try again.');
        });
    }
}

// Show Alert
function showAlert(type, message) {
    const alert = document.createElement('div');
    alert.className = `alert alert-${type} alert-dismissible fade show`;
    alert.style.position = 'fixed';
    alert.style.top = '20px';
    alert.style.right = '20px';
    alert.style.zIndex = '9999';
    alert.style.minWidth = '300px';
    alert.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.appendChild(alert);
    
    // Auto-hide after 5 seconds
    setTimeout(() => {
        alert.remove();
    }, 5000);
}
</script>
@endpush

@endsection
