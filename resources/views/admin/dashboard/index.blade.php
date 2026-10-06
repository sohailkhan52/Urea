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
    <div class="row mb-5 management-cards-row">
        
        <!-- Purchases Card -->
        <div class="col-6 col-md-6 col-xl-3 mb-4">
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
                                <i class="bi bi-arrow-right me-1"></i>View Purchases
                            </small>
                        </div>
                    </div>
                </div>
            </a>
        </div>


        <!-- Sales Card -->
        <div class="col-6 col-md-6 col-xl-3 mb-4">
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
                                <i class="bi bi-arrow-right me-1"></i>View Sales
                            </small>
                        </div>
                    </div>
                </div>
            </a>
        </div>


        <!-- Payables Card -->
        <div class="col-6 col-md-6 col-xl-3 mb-4">
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

          
        <!-- Udhar (Credit) Card -->
        <div class="col-6 col-md-6 col-xl-3 mb-4">
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
                                <i class="bi bi-arrow-right me-1"></i>View Udhar
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

        @media (min-width: 576px) and (max-width: 1024px) {
            .management-cards-row,
            .management-cards-row > div,
            .management-cards-row a,
            .management-card {
                scrollbar-width: none;
                -ms-overflow-style: none;
            }

            .management-cards-row::-webkit-scrollbar,
            .management-cards-row > div::-webkit-scrollbar,
            .management-cards-row a::-webkit-scrollbar,
            .management-card::-webkit-scrollbar {
                display: none;
            }
        }

        @media (max-width: 575.98px) {
            .management-cards-row {
                justify-content: space-between;
                overflow: hidden;
                scrollbar-width: none;
                margin-bottom: 1rem !important;
            }

            .management-cards-row::-webkit-scrollbar,
            .management-cards-row > div::-webkit-scrollbar,
            .management-cards-row a::-webkit-scrollbar,
            .management-card::-webkit-scrollbar {
                display: none;
            }

            .management-cards-row > div {
                flex: 0 0 47%;
                max-width: 47%;
                padding-right: 4px;
                padding-left: 4px;
                margin-bottom: 0.1rem !important;
                overflow: hidden;
                scrollbar-width: none;
            }

            .management-cards-row a,
            .management-card {
                display: block;
                overflow: hidden;
                scrollbar-width: none;
            }

            .management-card .card-body {
                position: relative;
                padding: 0.55rem;
            }

            .management-card .card-body > .d-flex > div:last-child {
                position: absolute;
                top: 0.45rem;
                right: 0.45rem;
                font-size: 1rem !important;
            }

            .management-card .card-body > .d-flex > div:first-child {
                flex: 1 1 auto;
                min-width: 0;
                padding-right: 1rem;
            }

            .management-card .text-muted.small {
                font-size: 0.68rem;
                line-height: 1.2;
            }

            .management-card .h4 {
                font-size: 0.68rem;
                white-space: nowrap;
                letter-spacing: -0.04em;
            }

            .management-card .mt-3 {
                margin-top: 0.75rem !important;
                padding-top: 0.75rem !important;
            }

            .management-card small {
                font-size: 0.65rem;
            }

            .dashboard-stats-row .card-body {
                position: relative;
            }

            .dashboard-stats-row {
                --bs-gutter-x: 0.75rem;
                margin-bottom: 1rem !important;
            }

            .dashboard-stats-row > div {
                margin-bottom: 0.1rem !important;
            }

            .dashboard-section-row {
                margin-bottom: 0.25rem !important;
            }

            .dashboard-section-row .card {
                margin-bottom: 0.25rem !important;
            }

            .dashboard-stats-row .card-body > .d-flex > div:first-child {
                padding-right: 1.75rem;
            }

            .dashboard-stats-row .card-body > .d-flex > i {
                position: absolute;
                top: 0.4rem;
                right: 0.4rem;
                font-size: 1.35rem !important;
            }

            .project-settings-card .card-header {
                padding: 0.65rem 0.85rem;
            }

            .project-settings-card .card-header h5 {
                font-size: 1rem;
            }

            .project-settings-card .card-body {
                padding: 0.85rem;
            }

            .project-settings-form .form-label {
                margin-bottom: 0.3rem;
                font-size: 0.9rem;
            }

            .project-settings-form .form-label i {
                margin-right: 0.35rem !important;
            }

            .project-settings-form .form-label.mt-3 {
                margin-top: 0.75rem !important;
            }

            .project-settings-form .form-control {
                min-height: 38px;
                padding: 0.375rem 0.6rem;
                font-size: 0.9rem;
            }

            .project-settings-form textarea.form-control {
                min-height: 60px;
            }

            .project-settings-form > .row > div {
                margin-bottom: 0.75rem !important;
            }

            .project-settings-form .text-muted {
                font-size: 0.78rem;
                line-height: 1.45;
            }

            #logoPreviewWrapper {
                width: 72px !important;
                height: 72px !important;
            }

            .project-settings-actions {
                gap: 0.5rem !important;
            }

            .project-settings-actions .btn {
                padding: 0.4rem 0.65rem;
                font-size: 0.82rem;
                white-space: nowrap;
            }

            .project-settings-actions .btn i {
                margin-right: 0.35rem !important;
            }
        }
    </style>

    {{-- Today's Statistics --}}
    <div class="row mb-4 dashboard-stats-row">
        <div class="col-6 col-md-6 col-lg-3 mb-3">
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

        <div class="col-6 col-md-6 col-lg-3 mb-3">
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

        <div class="col-6 col-md-6 col-lg-3 mb-3">
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

        <div class="col-6 col-md-6 col-lg-3 mb-3">
            <div class="card border-left-warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted d-block">Items Sold (Base Units)</small>
                            <h5 class="mb-0">{{ number_format($todayStats['items_sold'], 0) }}</h5>
                            <small class="text-muted" style="font-size: 0.7rem;">Total in base units</small>
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
    <div class="row mb-4 dashboard-section-row">
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
                                    <th>Unit</th>
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
                                    <td>
                                        <span class="badge bg-danger">
                                            {{ number_format($item->quantity, 2) }}
                                        </span>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            {{ $item->product->baseUnit ? $item->product->baseUnit->abbreviation : $item->product->unit }}
                                        </small>
                                    </td>
                                    <td>{{ number_format($item->product->minimum_stock_level, 2) }}</td>
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

    {{-- No Low Stock Items Message --}}
    @if($lowStockItems->count() == 0)
    <div class="row mb-4 dashboard-section-row">
        <div class="col-12">
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i> <strong>Great!</strong> No products with low stock (1-9 units).
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    </div>
    @endif
    {{-- Out of Stock Alert --}}
    @if($outOfStockItems->count() > 0)
    <div class="row mb-4 dashboard-section-row">
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
    <div class="row mb-4 dashboard-section-row">
        <div class="col-12">
            <div class="card border-info project-settings-card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-gear me-2"></i> Project Settings</h5>
                </div>
                <div class="card-body">
                    <form id="projectSettingsForm" class="project-settings-form" method="POST" action="{{ route('admin.dashboard.update-settings') }}" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="row">
                            {{-- Company Name Input --}}
                            <div class="col-md-6 mb-3">
                                <label for="project_name" class="form-label">
                                    <i class="bi bi-text-left me-2"></i> Company Name
                                </label>
                                <input type="text" 
                                       class="form-control @error('project_name') is-invalid @enderror" 
                                       id="project_name" 
                                       name="project_name" 
                                       value="{{ $company->name ?? config('app.name') }}"
                                       placeholder="Enter company name"
                                       required>
                                @error('project_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror

                                <label for="phone" class="form-label mt-3">
                                    <i class="bi bi-telephone me-2"></i> Phone Number
                                </label>
                                <input type="text"
                                       class="form-control @error('phone') is-invalid @enderror"
                                       id="phone"
                                       name="phone"
                                       value="{{ old('phone', $company->phone ?? '') }}"
                                       placeholder="Enter phone number">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror

                                <label for="additional_number" class="form-label mt-3">
                                    <i class="bi bi-telephone-forward me-2"></i> Additional Number
                                </label>
                                <input type="text"
                                       class="form-control @error('additional_number') is-invalid @enderror"
                                       id="additional_number"
                                       name="additional_number"
                                       value="{{ old('additional_number', $company->additional_number ?? '') }}"
                                       placeholder="Enter additional number">
                                @error('additional_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror

                                <label for="address" class="form-label mt-3">
                                    <i class="bi bi-geo-alt me-2"></i> Address
                                </label>
                                <textarea class="form-control @error('address') is-invalid @enderror"
                                          id="address"
                                          name="address"
                                          rows="2"
                                          placeholder="Enter address">{{ old('address', $company->address ?? '') }}</textarea>
                                @error('address')
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

                                <div class="d-flex justify-content-start align-items-center mt-3">
                                    <div>
                                        <small class="text-muted d-block mb-2">Logo & Favicon</small>
                                        <div id="logoPreviewWrapper" style="position: relative; width: 90px; height: 90px; border: 1px solid #ddd; border-radius: 12px; display: flex; align-items: center; justify-content: center; background-color: #f8f9fa; overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,0.08);">
                                            @if($company && $company->logo)
                                                <img id="logoPreview" src="{{ asset('storage/' . $company->logo) }}" alt="Logo" data-default="{{ asset('storage/' . $company->logo) }}" style="width: 100%; height: 100%; object-fit: cover;">
                                            @else
                                                <img id="logoPreview" src="https://ui-avatars.com/api/?name={{ urlencode(config('app.name')) }}&color=fff&background=6c757d&size=200" alt="Default Logo" data-default="https://ui-avatars.com/api/?name={{ urlencode(config('app.name')) }}&color=fff&background=6c757d&size=200" style="width: 100%; height: 100%; object-fit: cover;">
                                            @endif
                                            <button type="button" id="clearLogoPreview" aria-label="Remove image" style="position:absolute; top:4px; right:4px; width:18px; height:18px; border:none; border-radius:50%; background:rgba(0,0,0,0.7); color:#fff; font-size:12px; line-height:1; display:flex; align-items:center; justify-content:center; cursor:pointer; padding:0;">×</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2 project-settings-actions">
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
const logoInput = document.getElementById('logo');
const logoPreview = document.getElementById('logoPreview');
const clearLogoPreview = document.getElementById('clearLogoPreview');
const defaultLogoSrc = logoPreview ? logoPreview.dataset.default : '';

function previewLogo(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            if (logoPreview) {
                logoPreview.src = e.target.result;
            }
            if (clearLogoPreview) {
                clearLogoPreview.style.display = 'flex';
            }
        };
        reader.readAsDataURL(file);
        return;
    }

    if (logoPreview && defaultLogoSrc) {
        logoPreview.src = defaultLogoSrc;
    }
    if (clearLogoPreview) {
        clearLogoPreview.style.display = defaultLogoSrc ? 'flex' : 'none';
    }
}

if (clearLogoPreview) {
    clearLogoPreview.addEventListener('click', function() {
        if (logoInput) {
            logoInput.value = '';
        }
        if (logoPreview && defaultLogoSrc) {
            logoPreview.src = defaultLogoSrc;
        }
        clearLogoPreview.style.display = 'none';
    });
}

if (logoInput) {
    logoInput.addEventListener('change', function(event) {
        if (!event.target.files || event.target.files.length === 0) {
            previewLogo({ target: { files: [] } });
            return;
        }
        previewLogo(event);
    });
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
            
            // Update window title if Company Name changed
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
