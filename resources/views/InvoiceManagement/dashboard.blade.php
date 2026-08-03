@extends('Partials.app', ['activeMenu' => 'invoices'])

@section('title') Invoice Management Dashboard @endsection

@section('styles')
    <link rel="stylesheet" href="{{ asset('js/plugins/datatables-bs5/css/dataTables.bootstrap5.min.css') }}">
@endsection

@section('content')
<div class="content">
    <!-- Hero / Header -->
    <div class="d-flex flex-column flex-md-row justify-content-md-between align-items-md-center py-2 text-center text-md-start mb-3">
        <div class="flex-grow-1 mb-2 mb-md-0">
            <h1 class="h3 fw-bold mb-1">
                <i class="fa fa-chart-line text-primary me-2"></i> Invoice Management Dashboard
            </h1>
            <h2 class="h6 fw-medium text-muted mb-0">
                Real-time financial overview of billing, collections, outstanding balances, and category analytics.
            </h2>
        </div>
        <div class="mt-2 mt-md-0">
            @can('create-invoice')
                <a href="{{ route('invoices.bulk-generate') }}" class="btn btn-sm btn-success me-1">
                    <i class="fa fa-calendar-plus me-1"></i> Generate Monthly Rent
                </a>
                <a href="{{ route('invoices.create') }}" class="btn btn-sm btn-primary me-1">
                    <i class="fa fa-plus me-1"></i> Add Invoice
                </a>
            @endcan
        </div>
    </div>

    <!-- KPI Metrics Row -->
    <div class="row">
        <!-- Total Billed -->
        <div class="col-sm-6 col-xxl-3 mb-4">
            <div class="block block-rounded block-link-pop h-100 border-start border-4 border-primary">
                <div class="block-content block-content-full">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="fs-xs fw-semibold text-uppercase text-muted">Total Billed Volume</span>
                        <span class="badge bg-primary-light text-primary fw-bold">{{ $kpiData->total_count }} Invoices</span>
                    </div>
                    <div class="fs-2 fw-bold text-dark mb-1">৳ {{ number_format($kpiData->total_billed, 2) }}</div>
                    <div class="fs-xs text-muted">
                        <i class="fa fa-file-invoice text-primary me-1"></i> Gross invoiced amount
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Paid -->
        <div class="col-sm-6 col-xxl-3 mb-4">
            <div class="block block-rounded block-link-pop h-100 border-start border-4 border-success">
                <div class="block-content block-content-full">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="fs-xs fw-semibold text-uppercase text-muted">Collected Revenue</span>
                        <span class="badge bg-success-light text-success fw-bold">
                            {{ $kpiData->total_billed > 0 ? number_format(($kpiData->total_paid / $kpiData->total_billed) * 100, 1) : 0 }}% Paid
                        </span>
                    </div>
                    <div class="fs-2 fw-bold text-success mb-1">৳ {{ number_format($kpiData->total_paid, 2) }}</div>
                    <div class="fs-xs text-muted">
                        <i class="fa fa-check-circle text-success me-1"></i> {{ $kpiData->paid_count }} fully paid invoices
                    </div>
                </div>
            </div>
        </div>

        <!-- Outstanding Receivables -->
        <div class="col-sm-6 col-xxl-3 mb-4">
            <div class="block block-rounded block-link-pop h-100 border-start border-4 border-info">
                <div class="block-content block-content-full">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="fs-xs fw-semibold text-uppercase text-muted">Outstanding Balance</span>
                        <span class="badge bg-info-light text-info fw-bold">{{ $kpiData->pending_count + $kpiData->partial_count }} Active</span>
                    </div>
                    <div class="fs-2 fw-bold text-info mb-1">৳ {{ number_format($kpiData->total_outstanding, 2) }}</div>
                    <div class="fs-xs text-muted">
                        <i class="fa fa-hourglass-half text-info me-1"></i> {{ $kpiData->pending_count }} pending & {{ $kpiData->partial_count }} partial
                    </div>
                </div>
            </div>
        </div>

        <!-- Overdue Receivables -->
        <div class="col-sm-6 col-xxl-3 mb-4">
            <div class="block block-rounded block-link-pop h-100 border-start border-4 border-danger">
                <div class="block-content block-content-full">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="fs-xs fw-semibold text-uppercase text-muted">Overdue Balance</span>
                        <span class="badge bg-danger-light text-danger fw-bold">{{ $kpiData->overdue_count }} Overdue</span>
                    </div>
                    <div class="fs-2 fw-bold text-danger mb-1">৳ {{ number_format($kpiData->overdue_amount, 2) }}</div>
                    <div class="fs-xs text-muted">
                        <i class="fa fa-exclamation-triangle text-danger me-1"></i> Requires urgent payment record
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Category Distribution Mini Cards -->
    <div class="row mb-4">
        <div class="col-md-4 mb-2 mb-md-0">
            <div class="p-3 bg-body-light border rounded d-flex align-items-center justify-content-between">
                <div>
                    <div class="fs-xs text-uppercase fw-bold text-muted">Rent Requisitions</div>
                    <div class="fs-5 fw-bold text-dark">৳ {{ number_format($rentBilled, 2) }}</div>
                </div>
                <div class="p-2 bg-info-light rounded">
                    <i class="fa fa-building fa-2x text-info"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-2 mb-md-0">
            <div class="p-3 bg-body-light border rounded d-flex align-items-center justify-content-between">
                <div>
                    <div class="fs-xs text-uppercase fw-bold text-muted">Vehicle Maintenances</div>
                    <div class="fs-5 fw-bold text-dark">৳ {{ number_format($maintBilled, 2) }}</div>
                </div>
                <div class="p-2 bg-primary-light rounded">
                    <i class="fa fa-car fa-2x text-primary"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="p-3 bg-body-light border rounded d-flex align-items-center justify-content-between">
                <div>
                    <div class="fs-xs text-uppercase fw-bold text-muted">General Services</div>
                    <div class="fs-5 fw-bold text-dark">৳ {{ number_format($generalBilled, 2) }}</div>
                </div>
                <div class="p-2 bg-secondary-light rounded">
                    <i class="fa fa-cog fa-2x text-secondary"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Visual Analytics Charts Row -->
    <div class="row">
        <!-- 12-Month Trend Line Chart -->
        <div class="col-xl-8 mb-4">
            <div class="block block-rounded h-100 mb-0">
                <div class="block-header block-header-default">
                    <h3 class="block-title">
                        <i class="fa fa-chart-area text-primary me-2"></i> 12-Month Financial Billed vs Paid Trends
                    </h3>
                </div>
                <div class="block-content block-content-full text-center">
                    <div style="height: 320px; position: relative;">
                        <canvas id="invoiceTrendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Status Ratios Donut Chart -->
        <div class="col-xl-4 mb-4">
            <div class="block block-rounded h-100 mb-0">
                <div class="block-header block-header-default">
                    <h3 class="block-title">
                        <i class="fa fa-chart-pie text-info me-2"></i> Payment Status Distribution
                    </h3>
                </div>
                <div class="block-content block-content-full d-flex align-items-center justify-content-center">
                    <div style="height: 280px; width: 100%; position: relative;">
                        <canvas id="invoiceStatusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Actionable Tables Row -->
    <div class="row">
        <!-- Top Vendors Table -->
        <div class="col-xl-6 mb-4">
            <div class="block block-rounded h-100 mb-0">
                <div class="block-header block-header-default">
                    <h3 class="block-title">
                        <i class="fa fa-users text-primary me-2"></i> Top Vendors by Invoiced Value
                    </h3>
                </div>
                <div class="block-content block-content-full fs-sm">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover table-vcenter">
                            <thead>
                                <tr>
                                    <th>Vendor</th>
                                    <th class="text-center">Invoices</th>
                                    <th class="text-end">Total Billed</th>
                                    <th class="text-end">Total Paid</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topVendors as $vendor)
                                <tr>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $vendor->name }}</div>
                                        <small class="text-muted">{{ $vendor->vendor_code }}</small>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-dark border">{{ $vendor->invoice_count }}</span>
                                    </td>
                                    <td class="text-end fw-bold">৳ {{ number_format($vendor->total_billed, 2) }}</td>
                                    <td class="text-end text-success">৳ {{ number_format($vendor->total_paid, 2) }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-3">No vendor data available</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Overdue Urgent Action Table -->
        <div class="col-xl-6 mb-4">
            <div class="block block-rounded h-100 mb-0 border-start border-4 border-danger">
                <div class="block-header block-header-default">
                    <h3 class="block-title text-danger">
                        <i class="fa fa-exclamation-circle me-2"></i> Urgent Overdue Invoices
                    </h3>
                    <div class="block-options">
                        <a href="{{ route('invoices.index') }}" class="btn btn-sm btn-alt-danger">View All</a>
                    </div>
                </div>
                <div class="block-content block-content-full fs-sm">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover table-vcenter">
                            <thead>
                                <tr>
                                    <th>Invoice #</th>
                                    <th>Vendor</th>
                                    <th>Due Date</th>
                                    <th class="text-end">Outstanding</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentOverdue as $inv)
                                <tr>
                                    <td>
                                        <a href="{{ route('invoices.show', $inv->id) }}" class="fw-bold text-primary">
                                            {{ $inv->invoice_number }}
                                        </a>
                                    </td>
                                    <td>{{ $inv->vendor->name ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge bg-danger">
                                            {{ $inv->due_date ? $inv->due_date->format('d M Y') : 'N/A' }}
                                        </span>
                                    </td>
                                    <td class="text-end text-danger fw-bold">
                                        ৳ {{ number_format($inv->getOutstandingAmount(), 2) }}
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('invoices.show', $inv->id) }}" class="btn btn-xs btn-outline-success">
                                            Pay
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-3">
                                        <i class="fa fa-check-circle text-success me-1"></i> No overdue invoices currently
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(function() {
    // 12-Month Financial Trend Chart
    const trendCtx = document.getElementById('invoiceTrendChart').getContext('2d');
    new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($trendLabels) !!},
            datasets: [
                {
                    label: 'Gross Billed (৳)',
                    data: {!! json_encode($trendBilledData) !!},
                    borderColor: '#0284c7',
                    backgroundColor: 'rgba(2, 132, 199, 0.08)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.3
                },
                {
                    label: 'Collected Paid (৳)',
                    data: {!! json_encode($trendPaidData) !!},
                    borderColor: '#16a34a',
                    backgroundColor: 'rgba(22, 163, 74, 0.08)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.3
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'top' },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': ৳ ' + context.parsed.y.toLocaleString('en-IN', {minimumFractionDigits: 2});
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '৳ ' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });

    // Payment Status Ratio Donut Chart
    const statusCtx = document.getElementById('invoiceStatusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode(array_keys($statusCounts)) !!},
            datasets: [{
                data: {!! json_encode(array_values($statusCounts)) !!},
                backgroundColor: ['#16a34a', '#0284c7', '#d97706', '#dc2626'],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });
});
</script>
@endsection
