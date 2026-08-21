@extends('layouts.admin')

@section('title', 'Payables Aging Report')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Payables Aging Report</h1>
            <p class="text-muted mb-0">Analyze outstanding payables by age</p>
        </div>
        <a href="{{ route('admin.payables.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Payables
        </a>
    </div>

    {{-- Supplier Filter --}}
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-6">
                    <label for="supplier_id" class="form-label">Filter by Supplier (Optional)</label>
                    <select class="form-select" id="supplier_id" name="supplier_id">
                        <option value="">All Suppliers</option>
                        @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" {{ $selectedSupplierId == $supplier->id ? 'selected' : '' }}>
                            {{ $supplier->name }} @if($supplier->company_name) ({{ $supplier->company_name }}) @endif
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-filter me-1"></i> Filter
                    </button>
                    <a href="{{ route('admin.payables.aging') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-clockwise me-1"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Aging Summary Cards --}}
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-info">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Current (0-30 Days)</h6>
                    <h3 class="text-info mb-2">{{ $agingData['current']['purchases'] }}</h3>
                    <p class="mb-0 text-muted">
                        <strong>Rs. {{ number_format($agingData['current']['amount'], 2) }}</strong>
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-warning">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Aged (31-60 Days)</h6>
                    <h3 class="text-warning mb-2">{{ $agingData['aged_30_60']['purchases'] }}</h3>
                    <p class="mb-0 text-muted">
                        <strong>Rs. {{ number_format($agingData['aged_30_60']['amount'], 2) }}</strong>
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-orange">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Aged (61-90 Days)</h6>
                    <h3 style="color: #ff9800;" class="mb-2">{{ $agingData['aged_60_90']['purchases'] }}</h3>
                    <p class="mb-0 text-muted">
                        <strong>Rs. {{ number_format($agingData['aged_60_90']['amount'], 2) }}</strong>
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-danger">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Overdue (90+ Days)</h6>
                    <h3 class="text-danger mb-2">{{ $agingData['aged_90_plus']['purchases'] }}</h3>
                    <p class="mb-0 text-muted">
                        <strong>Rs. {{ number_format($agingData['aged_90_plus']['amount'], 2) }}</strong>
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Aging Chart --}}
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Payables Distribution by Age</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <canvas id="agingChart"></canvas>
                </div>
                <div class="col-md-6">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Age Category</th>
                                    <th class="text-end">Purchases</th>
                                    <th class="text-end">Amount</th>
                                    <th class="text-end">%</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $totalAmount = $agingData['current']['amount'] + 
                                                   $agingData['aged_30_60']['amount'] + 
                                                   $agingData['aged_60_90']['amount'] + 
                                                   $agingData['aged_90_plus']['amount'];
                                @endphp
                                <tr>
                                    <td><span class="badge bg-info">Current</span></td>
                                    <td class="text-end">{{ $agingData['current']['purchases'] }}</td>
                                    <td class="text-end">Rs. {{ number_format($agingData['current']['amount'], 2) }}</td>
                                    <td class="text-end">{{ $totalAmount > 0 ? round(($agingData['current']['amount'] / $totalAmount) * 100, 2) : 0 }}%</td>
                                </tr>
                                <tr>
                                    <td><span class="badge bg-warning">31-60 Days</span></td>
                                    <td class="text-end">{{ $agingData['aged_30_60']['purchases'] }}</td>
                                    <td class="text-end">Rs. {{ number_format($agingData['aged_30_60']['amount'], 2) }}</td>
                                    <td class="text-end">{{ $totalAmount > 0 ? round(($agingData['aged_30_60']['amount'] / $totalAmount) * 100, 2) : 0 }}%</td>
                                </tr>
                                <tr>
                                    <td><span class="badge" style="background-color: #ff9800;">61-90 Days</span></td>
                                    <td class="text-end">{{ $agingData['aged_60_90']['purchases'] }}</td>
                                    <td class="text-end">Rs. {{ number_format($agingData['aged_60_90']['amount'], 2) }}</td>
                                    <td class="text-end">{{ $totalAmount > 0 ? round(($agingData['aged_60_90']['amount'] / $totalAmount) * 100, 2) : 0 }}%</td>
                                </tr>
                                <tr>
                                    <td><span class="badge bg-danger">90+ Days</span></td>
                                    <td class="text-end">{{ $agingData['aged_90_plus']['purchases'] }}</td>
                                    <td class="text-end">Rs. {{ number_format($agingData['aged_90_plus']['amount'], 2) }}</td>
                                    <td class="text-end">{{ $totalAmount > 0 ? round(($agingData['aged_90_plus']['amount'] / $totalAmount) * 100, 2) : 0 }}%</td>
                                </tr>
                                <tr class="table-light fw-bold">
                                    <td>Total</td>
                                    <td class="text-end">
                                        {{ $agingData['current']['purchases'] + 
                                           $agingData['aged_30_60']['purchases'] + 
                                           $agingData['aged_60_90']['purchases'] + 
                                           $agingData['aged_90_plus']['purchases'] }}
                                    </td>
                                    <td class="text-end">Rs. {{ number_format($totalAmount, 2) }}</td>
                                    <td class="text-end">100%</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('agingChart').getContext('2d');
const agingChart = new Chart(ctx, {
    type: 'doughnut',
    data: {
        labels: [
            'Current (0-30 Days)',
            'Aged (31-60 Days)',
            'Aged (61-90 Days)',
            'Overdue (90+ Days)'
        ],
        datasets: [{
            data: [
                {{ $agingData['current']['amount'] }},
                {{ $agingData['aged_30_60']['amount'] }},
                {{ $agingData['aged_60_90']['amount'] }},
                {{ $agingData['aged_90_plus']['amount'] }}
            ],
            backgroundColor: [
                '#0dcaf0',
                '#ffc107',
                '#ff9800',
                '#dc3545'
            ],
            borderColor: '#fff',
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                position: 'bottom',
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        const value = context.parsed;
                        return 'Rs. ' + value.toLocaleString('en-PK', {minimumFractionDigits: 2});
                    }
                }
            }
        }
    }
});
</script>
@endsection
