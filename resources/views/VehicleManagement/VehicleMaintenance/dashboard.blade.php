@extends('Partials.app', ['activeMenu' => 'vehicles'])

@section('title', 'Maintenance Dashboard')

@section('content')
<div class="content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">Vehicle Maintenance Dashboard</h1>
            <div>
                <a href="{{ route('maintenance.maintenances.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> New Maintenance
                </a>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Vehicles</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalVehicles }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-car fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Service Due in 7 Days</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $vehiclesDue }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Pending Invoices</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $pendingInvoices }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-file-invoice fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card border-left-danger shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Overdue Invoices</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $overdueInvoices }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-exclamation-circle fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Monthly Cost Trends Chart -->
            <div class="col-lg-8 mb-4">
                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Monthly Cost Trends (Last 6 Months)</h6>
                    </div>
                    <div class="card-body">
                        <canvas id="monthlyCostChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Top Vendors -->
            <div class="col-lg-4 mb-4">
                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Top 5 Vendors by Cost</h6>
                    </div>
                    <div class="card-body">
                        <div class="list-group">
                            @forelse($topVendors as $vendor)
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>{{ $vendor->name }}</strong>
                                    <br><small class="text-muted">{{ $vendor->vendor_code }}</small>
                                </div>
                                <span class="badge bg-primary rounded-pill">৳ {{ number_format($vendor->total_cost, 2) }}</span>
                            </div>
                            @empty
                            <div class="text-center text-muted py-3">No vendor data available</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- High Cost Vehicles -->
            <div class="col-lg-6 mb-4">
                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">High-Cost Vehicles (Top 5)</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Registration</th>
                                        <th>Type</th>
                                        <th class="text-end">Total Cost</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($highCostVehicles as $vehicle)
                                    <tr>
                                        <td>{{ $vehicle->registration_number }}</td>
                                        <td>{{ $vehicle->vehicle_type }}</td>
                                        <td class="text-end">৳ {{ number_format($vehicle->total_cost, 2) }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">No data available</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Maintenance Activities -->
            <div class="col-lg-6 mb-4">
                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Recent Maintenance Activities</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Vehicle</th>
                                        <th>Type</th>
                                        <th class="text-end">Cost</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentMaintenances as $maintenance)
                                    <tr>
                                        <td>{{ $maintenance->start_datetime->format('d M') }}</td>
                                        <td>{{ $maintenance->vehicle->registration_number ?? 'N/A' }}</td>
                                        <td><span class="badge bg-{{ $maintenance->getMaintenanceTypeBadge() }}">{{ $maintenance->getMaintenanceTypeLabel() }}</span></td>
                                        <td class="text-end">৳ {{ number_format($maintenance->total_service_cost, 2) }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">No recent activities</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Links -->
        <div class="row">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Quick Links</h6>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-md-2">
                                <a href="{{ route('maintenance.maintenances.index') }}" class="btn btn-outline-primary btn-block">
                                    <i class="fas fa-wrench fa-2x mb-2"></i>
                                    <br>Maintenances
                                </a>
                            </div>
                            <div class="col-md-2">
                                <a href="{{ route('maintenance.vendors.index') }}" class="btn btn-outline-primary btn-block">
                                    <i class="fas fa-store fa-2x mb-2"></i>
                                    <br>Vendors
                                </a>
                            </div>
                            <div class="col-md-2">
                                <a href="{{ route('maintenance.parts.index') }}" class="btn btn-outline-primary btn-block">
                                    <i class="fas fa-cogs fa-2x mb-2"></i>
                                    <br>Parts
                                </a>
                            </div>
                            <div class="col-md-2">
                                <a href="{{ route('maintenance.operational-logs.index') }}" class="btn btn-outline-primary btn-block">
                                    <i class="fas fa-clipboard-list fa-2x mb-2"></i>
                                    <br>Logs
                                </a>
                            </div>
                            <div class="col-md-2">
                                <a href="{{ route('maintenance.invoices.index') }}" class="btn btn-outline-primary btn-block">
                                    <i class="fas fa-file-invoice-dollar fa-2x mb-2"></i>
                                    <br>Invoices
                                </a>
                            </div>
                            <div class="col-md-2">
                                <a href="{{ route('maintenance.reports.index') }}" class="btn btn-outline-primary btn-block">
                                    <i class="fas fa-chart-line fa-2x mb-2"></i>
                                    <br>Reports
                                </a>
                            </div>
                        </div>
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
$(document).ready(function() {
    // Monthly Cost Trends Chart
    var ctx = document.getElementById('monthlyCostChart').getContext('2d');
    var monthlyCostChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: [
                @foreach($monthlyCosts as $cost)
                    '{{ \Carbon\Carbon::parse($cost->month . "-01")->format("M Y") }}',
                @endforeach
            ],
            datasets: [{
                label: 'Maintenance Cost (৳)',
                data: [
                    @foreach($monthlyCosts as $cost)
                        {{ $cost->total_cost }},
                    @endforeach
                ],
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
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
});
</script>
@endsection
