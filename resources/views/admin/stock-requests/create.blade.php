@extends('layouts.admin')

@section('title', 'Create Stock Request')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Create Stock Request</h1>
        <a href="{{ route('admin.stock-requests.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to List
        </a>
    </div>

    <form action="{{ route('admin.stock-requests.store') }}" method="POST">
        @csrf
        
        <div class="row">
            {{-- Main Form --}}
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Request Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            {{-- Warehouse --}}
                            <div class="col-md-6">
                                <label for="warehouse_id" class="form-label">
                                    Warehouse <span class="text-danger">*</span>
                                </label>
                                @if(auth()->user()->isSuperAdmin())
                                    <select class="form-select @error('warehouse_id') is-invalid @enderror" 
                                            id="warehouse_id" 
                                            name="warehouse_id" 
                                            required>
                                        <option value="">Select Warehouse</option>
                                        @foreach($warehouses as $warehouse)
                                        <option value="{{ $warehouse->id }}" 
                                                {{ old('warehouse_id', $defaultWarehouse?->id) == $warehouse->id ? 'selected' : '' }}>
                                            {{ $warehouse->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                @else
                                    <input type="hidden" name="warehouse_id" value="{{ $defaultWarehouse->id }}">
                                    <input type="text" 
                                           class="form-control" 
                                           value="{{ $defaultWarehouse->name }}" 
                                           readonly 
                                           disabled>
                                    <small class="form-text text-muted">Your assigned warehouse</small>
                                @endif
                                @error('warehouse_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Priority --}}
                            <div class="col-md-6">
                                <label for="priority" class="form-label">
                                    Priority <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('priority') is-invalid @enderror" 
                                        id="priority" 
                                        name="priority" 
                                        required>
                                    @foreach($priorities as $key => $label)
                                    <option value="{{ $key }}" {{ old('priority', 'normal') == $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('priority')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Reason --}}
                            <div class="col-12">
                                <label for="reason" class="form-label">
                                    Reason for Request
                                </label>
                                <textarea class="form-control @error('reason') is-invalid @enderror" 
                                          id="reason" 
                                          name="reason" 
                                          rows="2"
                                          placeholder="Brief reason for this stock request...">{{ old('reason') }}</textarea>
                                @error('reason')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Notes --}}
                            <div class="col-12">
                                <label for="notes" class="form-label">
                                    Additional Notes
                                </label>
                                <textarea class="form-control @error('notes') is-invalid @enderror" 
                                          id="notes" 
                                          name="notes" 
                                          rows="3"
                                          placeholder="Any additional information...">{{ old('notes') }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Help/Info Sidebar --}}
            <div class="col-md-4">
                <div class="card bg-light">
                    <div class="card-body">
                        <h6 class="card-title">
                            <i class="bi bi-info-circle me-1"></i> Instructions
                        </h6>
                        <p class="small mb-3">
                            Fill in the basic details to create your stock request. After creation, you'll be able to add products to the request.
                        </p>
                        
                        <h6 class="mt-3">Priority Levels:</h6>
                        <ul class="small">
                            <li><strong>Low:</strong> Can wait, no urgency</li>
                            <li><strong>Normal:</strong> Standard processing</li>
                            <li><strong>High:</strong> Needed soon</li>
                            <li><strong>Urgent:</strong> Immediate attention required</li>
                        </ul>

                        <div class="alert alert-info alert-sm mt-3 mb-0">
                            <small>
                                <i class="bi bi-lightbulb me-1"></i>
                                <strong>Note:</strong> You'll add products to your request on the next screen.
                            </small>
                        </div>
                    </div>
                </div>

                {{-- Quick Stats --}}
                @if($warehouses->count() > 1)
                <div class="card mt-3">
                    <div class="card-body">
                        <h6 class="card-title">
                            <i class="bi bi-building me-1"></i> Active Warehouses
                        </h6>
                        <div class="d-flex align-items-center">
                            <div class="display-6 text-primary me-3">{{ $warehouses->count() }}</div>
                            <small class="text-muted">warehouses available</small>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>

        {{-- Submit Button --}}
        <div class="row mt-3">
            <div class="col-md-8">
                <div class="d-flex justify-content-between">
                    <a href="{{ route('admin.stock-requests.index') }}" class="btn btn-secondary">
                        <i class="bi bi-x-circle me-1"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle me-1"></i> Create Request
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    // Auto-select priority based on reason keywords
    document.getElementById('reason')?.addEventListener('input', function() {
        const reason = this.value.toLowerCase();
        const prioritySelect = document.getElementById('priority');
        
        if (!prioritySelect || prioritySelect.value !== 'normal') {
            return; // Don't auto-change if user already selected something
        }
        
        if (reason.includes('urgent') || reason.includes('emergency') || reason.includes('critical')) {
            prioritySelect.value = 'urgent';
        } else if (reason.includes('soon') || reason.includes('important') || reason.includes('needed')) {
            prioritySelect.value = 'high';
        }
    });
</script>
@endpush
