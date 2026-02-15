@extends('Partials.app', ['activeMenu' => 'maintenance-reports'])

@section('title', 'Maintenance Reports')

@section('content')
<div class="content">
    <div class="container-fluid">

        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">Maintenance Reports</h1>
        </div>

        <div class="row">

            <!-- Vehicle Cost Report -->
            <div class="col-md-4 mb-4">
                <a href="{{ route('maintenance.reports.vehicle-cost') }}" class="text-decoration-none">
                    <div class="card shadow h-100 border-left-primary">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col">
                                    <h5 class="font-weight-bold text-primary">Vehicle Cost Report</h5>
                                    <p class="mb-0 text-muted small">
                                        View maintenance cost breakdown by vehicle.
                                    </p>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-car fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Vendor Cost Report -->
            <div class="col-md-4 mb-4">
                <a href="{{ route('maintenance.reports.vendor-cost') }}" class="text-decoration-none">
                    <div class="card shadow h-100 border-left-success">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col">
                                    <h5 class="font-weight-bold text-success">Vendor Cost Report</h5>
                                    <p class="mb-0 text-muted small">
                                        Analyze spending by vendor.
                                    </p>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-building fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Monthly Expenses -->
            <div class="col-md-4 mb-4">
                <a href="{{ route('maintenance.reports.monthly-expenses') }}" class="text-decoration-none">
                    <div class="card shadow h-100 border-left-info">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col">
                                    <h5 class="font-weight-bold text-info">Monthly Expenses</h5>
                                    <p class="mb-0 text-muted small">
                                        Track monthly maintenance expenses trends.
                                    </p>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Parts History -->
            <div class="col-md-4 mb-4">
                <a href="{{ route('maintenance.reports.parts-history') }}" class="text-decoration-none">
                    <div class="card shadow h-100 border-left-warning">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col">
                                    <h5 class="font-weight-bold text-warning">Parts History</h5>
                                    <p class="mb-0 text-muted small">
                                        Review parts replacement history.
                                    </p>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-cogs fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Service Due Report -->
            <div class="col-md-4 mb-4">
                <a href="{{ route('maintenance.reports.service-due') }}" class="text-decoration-none">
                    <div class="card shadow h-100 border-left-danger">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col">
                                    <h5 class="font-weight-bold text-danger">Service Due Report</h5>
                                    <p class="mb-0 text-muted small">
                                        View upcoming and overdue services.
                                    </p>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Vendor Comparison -->
            <div class="col-md-4 mb-4">
                <a href="{{ route('maintenance.reports.vendor-comparison') }}" class="text-decoration-none">
                    <div class="card shadow h-100 border-left-secondary">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col">
                                    <h5 class="font-weight-bold text-secondary">Vendor Comparison</h5>
                                    <p class="mb-0 text-muted small">
                                        Compare vendor cost and performance.
                                    </p>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-balance-scale fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

        </div>

    </div>
</div>
@endsection
