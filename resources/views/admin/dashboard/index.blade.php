@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<div class="container-fluid">
    {{-- Page Header --}}
    <div class="mb-4">
        <h1 class="h3 mb-0">Dashboard</h1>
        <p class="text-muted small">Welcome back! Here's your business overview.</p>
    </div>

    {{-- Management Quick Links --}}
    <div class="row mb-5">
        <!-- Sales Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <a href="{{ route('admin.sales.index') }}" class="text-decoration-none">
                <div class="card management-card h-100 border-0 shadow-sm" style="cursor: pointer; transition: all 0.3s ease; border-left: 4px solid #e3165b !important;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-muted small mb-2">Sales</div>
                                <div class="h4 mb-0 fw-bold text-primary">
                                    {{ $totalSales ?? 0 }}
                                </div>
                            </div>
                            <div class="text-primary" style="font-size: 3rem; opacity: 0.15;">
                                <i class="bi bi-bag-check"></i>
                            </div>
                        </div>
                        <div class="mt-3 pt-3 border-top">
                            <small class="text-muted">
                                <i class="bi bi-arrow-right me-1"></i>View All Sales
                            </small>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <!-- Purchases Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <a href="{{ route('admin.purchases.index') }}" class="text-decoration-none">
                <div class="card management-card h-100 border-0 shadow-sm" style="cursor: pointer; transition: all 0.3s ease; border-left: 4px solid #198754 !important;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-muted small mb-2">Purchases</div>
                                <div class="h4 mb-0 fw-bold text-success">
                                    {{ $totalPurchases ?? 0 }}
                                </div>
                            </div>
                            <div class="text-success" style="font-size: 3rem; opacity: 0.15;">
                                <i class="bi bi-cart-plus"></i>
                            </div>
                        </div>
                        <div class="mt-3 pt-3 border-top">
                            <small class="text-muted">
                                <i class="bi bi-arrow-right me-1"></i>View All Purchases
                            </small>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <!-- Udhar (Credit) Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <a href="{{ route('admin.udhar.index') }}" class="text-decoration-none">
                <div class="card management-card h-100 border-0 shadow-sm" style="cursor: pointer; transition: all 0.3s ease; border-left: 4px solid #ffc107 !important;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-muted small mb-2">Udhar (Credit)</div>
                                <div class="h4 mb-0 fw-bold text-warning">
                                    PKR {{ number_format($totalUdhar ?? 0, 0) }}
                                </div>
                            </div>
                            <div class="text-warning" style="font-size: 3rem; opacity: 0.15;">
                                <i class="bi bi-cash-stack"></i>
                            </div>
                        </div>
                        <div class="mt-3 pt-3 border-top">
                            <small class="text-muted">
                                <i class="bi bi-arrow-right me-1"></i>View Udhar Details
                            </small>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <!-- Payables Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <a href="{{ route('admin.supplier-payables.index') }}" class="text-decoration-none">
                <div class="card management-card h-100 border-0 shadow-sm" style="cursor: pointer; transition: all 0.3s ease; border-left: 4px solid #dc3545 !important;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-muted small mb-2">Payables</div>
                                <div class="h4 mb-0 fw-bold text-danger">
                                    PKR {{ number_format($totalPayables ?? 0, 0) }}
                                </div>
                            </div>
                            <div class="text-danger" style="font-size: 3rem; opacity: 0.15;">
                                <i class="bi bi-exclamation-circle"></i>
                            </div>
                        </div>
                        <div class="mt-3 pt-3 border-top">
                            <small class="text-muted">
                                <i class="bi bi-arrow-right me-1"></i>View Payables
                            </small>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <style>
        .management-card {
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .management-card:hover {
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15) !important;
            transform: translateY(-3px);
        }

        .management-card .card-body {
            padding: 1.5rem;
        }
    </style>

    {{-- Today's Statistics --}}
    <div class="row mb-4">
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card border-left-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted d-block">Today's Sales</small>
                            <h5 class="mb-0">{{ number_format($todayStats['total_sales'], 0) }}</h5>
                        </div>
                        <i class="bi bi-cart-check" style="font-size: 2rem; color: #e3165b;"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card border-left-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted d-block">Today's Purchases</small>
                            <h5 class="mb-0">{{ number_format($todayStats['total_purchases'], 0) }}</h5>
                        </div>
                        <i class="bi bi-bag-check" style="font-size: 2rem; color: #198754;"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card border-left-info">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted d-block">Payments Received</small>
                            <h5 class="mb-0">{{ number_format($todayStats['payments_received'], 0) }}</h5>
                        </div>
                        <i class="bi bi-cash-coin" style="font-size: 2rem; color: #0d6efd;"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card border-left-warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted d-block">Items Sold</small>
                            <h5 class="mb-0">{{ number_format($todayStats['items_sold'], 0) }}</h5>
                        </div>
                        <i class="bi bi-box" style="font-size: 2rem; color: #ffc107;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content Row --}}
    <div class="row mb-4">
        {{-- Sidebar Column --}}
        <div class="col-lg-12">


    {{-- Low Stock Alert --}}
    @if($lowStockItems->count() > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-warning">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="bi bi-exclamation-triangle me-2"></i> Low Stock Alert</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Current Stock</th>
                                    <th>Minimum Level</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($lowStockItems->take(10) as $item)
                                <tr>
                                    <td>
                                        <strong>{{ $item->product->name }}</strong><br>
                                        <small class="text-muted">{{ $item->product->sku }}</small>
                                    </td>
                                    <td><span class="badge bg-danger">{{ $item->quantity }}</span></td>
                                    <td>10</td> {{-- Fixed threshold since minimum_stock_level was removed --}}
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Out of Stock Alert --}}
    @if($outOfStockItems->count() > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-danger">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0"><i class="bi bi-exclamation-circle me-2"></i> Out of Stock Items</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Warehouse</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($outOfStockItems->take(10) as $item)
                                <tr>
                                    <td>
                                        <strong>{{ $item->product->name }}</strong><br>
                                        <small class="text-muted">{{ $item->product->sku }}</small>
                                    </td>
                                    <td>{{ $item->warehouse->name ?? 'N/A' }}</td>
                                    <td><span class="badge bg-danger">Out of Stock</span></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Project Settings --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-info">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-gear me-2"></i> Project Settings</h5>
                </div>
                <div class="card-body">
                    <form id="projectSettingsForm" method="POST" action="{{ route('admin.dashboard.update-settings') }}" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="row">
                            {{-- Project Name Input --}}
                            <div class="col-md-6 mb-3">
                                <label for="project_name" class="form-label">
                                    <i class="bi bi-text-left me-2"></i> Project Name
                                </label>
                                <input type="text" 
                                       class="form-control @error('project_name') is-invalid @enderror" 
                                       id="project_name" 
                                       name="project_name" 
                                       value="{{ $company->name ?? config('app.name') }}"
                                       placeholder="Enter project name"
                                       required>
                                @error('project_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Logo Upload --}}
                            <div class="col-md-6 mb-3">
                                <label for="logo" class="form-label">
                                    <i class="bi bi-image me-2"></i> Logo & Favicon
                                </label>
                                <input type="file" 
                                       class="form-control @error('logo') is-invalid @enderror" 
                                       id="logo" 
                                       name="logo" 
                                       accept="image/*"
                                       onchange="previewLogo(event)">
                                <small class="text-muted d-block mt-2">
                                    Supported formats: JPG, PNG, GIF, SVG. Max size: 2MB
                                </small>
                                @error('logo')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Logo Preview --}}
                        <div class="row mb-3">
                            <div class="col-12">
                                <label class="form-label">Current Logo Preview</label>
                                <div class="d-flex gap-3 align-items-center">
                                    <div>
                                        <small class="text-muted d-block mb-2">Logo</small>
                                        <div style="width: 120px; height: 120px; border: 1px solid #ddd; border-radius: 8px; display: flex; align-items: center; justify-content: center; background-color: #f8f9fa; overflow: hidden;">
                                            @if($company && $company->logo)
                                                <img id="logoPreview" src="{{ asset('storage/' . $company->logo) }}" alt="Logo" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                                            @else
                                                <img id="logoPreview" src="https://ui-avatars.com/api/?name={{ urlencode(config('app.name')) }}&color=fff&background=6c757d&size=200" alt="Default Logo" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <div>
                                        <small class="text-muted d-block mb-2">Favicon Preview</small>
                                        <div style="width: 120px; height: 120px; border: 1px solid #ddd; border-radius: 8px; display: flex; align-items: center; justify-content: center; background-color: #f8f9fa; padding: 20px;">
                                            <img id="faviconPreview" src="{{ asset('favicon.ico') }}" alt="Favicon" style="width: 32px; height: 32px;">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-info">
                                <i class="bi bi-save me-2"></i> Save Settings
                            </button>
                            <button type="reset" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-clockwise me-2"></i> Reset
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
function previewLogo(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('logoPreview').src = e.target.result;
            document.getElementById('faviconPreview').src = e.target.result;
        };
        reader.readAsDataURL(file);
    }
}

// Handle form submission
document.getElementById('projectSettingsForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Saving...';
    
    fetch(this.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': formData.get('_token')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            const alert = document.createElement('div');
            alert.className = 'alert alert-success alert-dismissible fade show mt-3';
            alert.innerHTML = `
                <i class="bi bi-check-circle me-2"></i> ${data.message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.querySelector('.card-body').prepend(alert);
            
            // Auto-hide after 3 seconds
            setTimeout(() => alert.remove(), 3000);
            
            // Update window title if project name changed
            if (data.new_name) {
                document.title = data.new_name + ' - Dashboard';
            }
            
            // Reload page to update sidebar with new logo and name
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            alert('Error: ' + (data.message || 'Failed to save settings'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error saving settings. Please try again.');
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });
});
</script>
@endpush
@endsection
