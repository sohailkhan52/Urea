@extends('layouts.admin')

@section('title', 'Families Management')

@section('content')
<div class="container-fluid">
    {{-- Page Header --}}
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1><i class="bi bi-people me-2"></i>Families Management</h1>
                <p class="text-muted mb-0">Manage customer families and groups</p>
            </div>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newFamilyModal">
                <i class="bi bi-plus-lg me-2"></i> Add New Family
            </button>
        </div>
    </div>

    {{-- Families Table --}}
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">All Families</h5>
        </div>
        <div class="card-body">
            @if($families->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
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
                                        <i class="bi bi-pencil-square"></i> Edit
                                    </button>
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-danger" 
                                            onclick="deleteFamily({{ $family->id }}, '{{ $family->name }}')">
                                        <i class="bi bi-trash"></i> Delete
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <nav aria-label="Pagination">
                    {{ $families->links() }}
                </nav>
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
<div class="modal fade" id="newFamilyModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
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
<div class="modal fade" id="editFamilyModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
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
