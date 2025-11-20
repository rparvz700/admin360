@extends('Partials.app')

@section('title')
    {{ config('app.name') }} - Dashboard
@endsection

@section('content')
    @php
        // FLEET MANAGEMENT - Static Data
        $vehiclesOwned = 45;
        $vehiclesRental = 12;

        $driverTotal = 40;
        $driverActive = 35;
        $driverInactive = 5;
        
        $vehicleLicenseActive = 50;
        $vehicleLicenseExpiring = 5;
        $vehicleLicenseExpired = 2;
        
        $driverLicenseActive = 35;
        $driverLicenseExpiring = 3;
        $driverLicenseExpired = 2;
        
        $vehiclesInGarage = 8;
        
        // TOURS AND TRAVELS - Static Data
        $visaEmployeeData = 120;
        $visaSampleForms = 15;
        
        $busTickets = 85;
        $airTickets = 142;
        
        // FACILITY MANAGEMENT - Static Data
        $rentalSites = 8;
        $contractsActive = 6;
        $contractsExpiring = 1;
        $contractsExpired = 1;
        
        $totalSites = 8;
        $billsCollected = 6;
        $billsPending = 2;
        
        $airconTotal = 120;
        $airconOperational = 110;
        $airconDamaged = 5;
        $airconServiceRequired = 5;
        $airconUpcomingService = 15;
        $airconServiceDue = 3;
        
        $fireExtinguishers = 30;
        $fireExtinguisherActive = 25;
        $fireExtinguisherInactive = 5;
        $fireUpcomingRefill = 8;
        $fireRefillDue = 2;

        $assetTotal = 200;
        $assetInUse = 180;
        $assetUnderMaintenance = 10;
        $assetRecentDisposed = 10;
        
        $employeesTracked = 45;
        $tadaPending = 12;
    @endphp

    <!-- Hero -->
    <div class="content">
        <div class="d-flex flex-column flex-md-row justify-content-md-between align-items-md-center py-2 text-center text-md-start">
            <div class="flex-grow-1 mb-1 mb-md-0">
                <h1 class="h3 fw-bold mb-1">
                    Dashboard
                </h1>
                <h2 class="h6 fw-medium text-muted mb-0">
                    Birds eye view of Admin Departments activities & status
                </h2>
            </div>
        </div>
    </div>

    <!-- FLEET MANAGEMENT SECTION -->
    <div class="content">
        <h2 class="content-heading">
            <i class="fa fa-car me-2"></i>Fleet Management
        </h2>
        
        <div class="row">
            <!-- Vehicle Information -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="block block-rounded h-100">
                    <div class="block-content block-content-full">
                        <div class="d-flex align-items-center mb-3">
                            <div class="me-3">
                                <i class="fa fa-car fa-2x text-primary"></i>
                            </div>
                            <div>
                                <div class="fs-3 fw-bold">{{ $vehiclesOwned + $vehiclesRental }}</div>
                                <div class="text-muted">Total Vehicles</div>
                            </div>
                        </div>
                        <div class="row g-0">
                            <div class="col-6 border-end">
                                <div class="p-2">
                                    <div class="fs-sm text-muted">Owned</div>
                                    <div class="fs-5 fw-semibold">{{ $vehiclesOwned }}</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-2">
                                    <div class="fs-sm text-muted">Rental</div>
                                    <div class="fs-5 fw-semibold">{{ $vehiclesRental }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Driver Information -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="block block-rounded h-100">
                    <div class="block-content block-content-full">
                        <div class="d-flex align-items-center mb-3">
                            <div class="me-3">                          
                                <i class="fa fa-user-tie fa-2x text-info"></i>
                            </div>
                            <div>
                                <div class="fs-3 fw-bold">{{ $driverTotal }}</div>
                                <div class="text-muted">Total Drivers</div>
                            </div>
                        </div>
                        <div class="row g-0">
                            <div class="col-6 border-end">
                                <div class="p-2">
                                    <div class="fs-sm text-muted">Active</div>
                                    <div class="fs-5 fw-semibold">{{ $driverActive }}</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-2">
                                    <div class="fs-sm text-muted">Inactive</div>
                                    <div class="fs-5 fw-semibold">{{ $driverInactive }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Vehicle License -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="block block-rounded h-100">
                    <div class="block-content block-content-full">
                        <div class="d-flex align-items-center mb-3">
                            <div class="me-3">
                                <i class="si si-docs fa-2x text-success"></i>
                            </div>
                            <div>
                                <div class="fs-3 fw-bold">{{ $vehicleLicenseActive + $vehicleLicenseExpiring + $vehicleLicenseExpired }}</div>
                                <div class="text-muted">Vehicle Papers</div>
                            </div>
                        </div>
                        <div class="fs-sm mb-2">
                            <span class="badge bg-success">Active: {{ $vehicleLicenseActive }}</span>
                        </div>
                        <div class="fs-sm mb-2">
                            <span class="badge bg-warning">Expiring: {{ $vehicleLicenseExpiring }}</span>
                        </div>
                        <div class="fs-sm">
                            <span class="badge bg-danger">Expired: {{ $vehicleLicenseExpired }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Driver Licenses -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="block block-rounded h-100">
                    <div class="block-content block-content-full">
                        <div class="d-flex align-items-center mb-3">
                            <div class="me-3">
                                <i class="fa fa-id-card fa-2x text-info"></i>
                            </div>
                            <div>
                                <div class="fs-3 fw-bold">{{ $driverLicenseActive + $driverLicenseExpiring + $driverLicenseExpired }}</div>
                                <div class="text-muted">Driver Licenses</div>
                            </div>
                        </div>
                        <div class="fs-sm mb-2">
                            <span class="badge bg-success">Active: {{ $driverLicenseActive }}</span>
                        </div>
                        <div class="fs-sm mb-2">
                            <span class="badge bg-warning">Expiring: {{ $driverLicenseExpiring }}</span>
                        </div>
                        <div class="fs-sm">
                            <span class="badge bg-danger">Expired: {{ $driverLicenseExpired }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Vehicle Maintenance -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="block block-rounded h-100">
                    <div class="block-content block-content-full">
                        <div class="d-flex align-items-center mb-3">
                            <div class="me-3">
                                <i class="fa fa-tools fa-2x text-warning"></i>
                            </div>
                            <div>
                                <div class="fs-3 fw-bold">{{ $vehiclesInGarage }}</div>
                                <div class="text-muted">Vehicles in Garage</div>
                            </div>
                        </div>
                        <a href="#" class="btn btn-sm btn-alt-secondary w-100">
                            <i class="fa fa-history me-1"></i>Maintenance History
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- TOURS AND TRAVELS SECTION -->
    <div class="content">
        <h2 class="content-heading">
            <i class="fa fa-plane me-2"></i>Tours and Travels
        </h2>
        
        <div class="row">
            <!-- Visa Processing -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="block block-rounded h-100">
                    <div class="block-content block-content-full">
                        <div class="d-flex align-items-center mb-3">
                            <div class="me-3">
                                <i class="fa fa-passport fa-2x text-primary"></i>
                            </div>
                            <div>
                                <div class="fs-3 fw-bold">{{ $visaEmployeeData }}</div>
                                <div class="text-muted">Visa Processing</div>
                            </div>
                        </div>
                        <div class="row g-0">
                            <div class="col-6 border-end">
                                <div class="p-2">
                                    <div class="fs-sm text-muted">Employees</div>
                                    <div class="fs-5 fw-semibold">{{ $visaEmployeeData }}</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-2">
                                    <div class="fs-sm text-muted">Forms</div>
                                    <div class="fs-5 fw-semibold">{{ $visaSampleForms }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Travel History -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="block block-rounded h-100">
                    <div class="block-content block-content-full">
                        <div class="d-flex align-items-center mb-3">
                            <div class="me-3">
                                <i class="fa fa-ticket-alt fa-2x text-success"></i>
                            </div>
                            <div>
                                <div class="fs-3 fw-bold">{{ $busTickets + $airTickets }}</div>
                                <div class="text-muted">Travel Tickets</div>
                            </div>
                        </div>
                        <div class="row g-0">
                            <div class="col-6 border-end">
                                <div class="p-2">
                                    <div class="fs-sm text-muted">Bus</div>
                                    <div class="fs-5 fw-semibold">{{ $busTickets }}</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-2">
                                    <div class="fs-sm text-muted">Air</div>
                                    <div class="fs-5 fw-semibold">{{ $airTickets }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- FACILITY MANAGEMENT SECTION -->
    <div class="content">
        <h2 class="content-heading">
            <i class="fa fa-building me-2"></i>Facility Management
        </h2>
        
        <div class="row">
            <!-- Contract Management -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="block block-rounded h-100">
                    <div class="block-content block-content-full">
                        <div class="d-flex align-items-center mb-3">
                            <div class="me-3">
                                <i class="fa fa-file-contract fa-2x text-primary"></i>
                            </div>
                            <div>
                                <div class="fs-3 fw-bold">{{ $rentalSites }}</div>
                                <div class="text-muted">Rental Sites</div>
                            </div>
                        </div>
                        <div class="fs-sm mb-2">
                            <span class="badge bg-success">Active: {{ $contractsActive }}</span>
                        </div>
                        <div class="fs-sm mb-2">
                            <span class="badge bg-warning">Expiring: {{ $contractsExpiring }}</span>
                        </div>
                        <div class="fs-sm">
                            <span class="badge bg-danger">Expired: {{ $contractsExpired }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Utility Bills -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="block block-rounded h-100">
                    <div class="block-content block-content-full">
                        <div class="d-flex align-items-center mb-3">
                            <div class="me-3">
                                <i class="fa fa-file-invoice-dollar fa-2x text-success"></i>
                            </div>
                            <div>
                                <div class="fs-3 fw-bold">{{ $totalSites }}</div>
                                <div class="text-muted">Utility Bills</div>
                            </div>
                        </div>
                        <div class="row g-0">
                            <div class="col-6 border-end">
                                <div class="p-2">
                                    <div class="fs-sm text-muted">Collected</div>
                                    <div class="fs-5 fw-semibold text-success">{{ $billsCollected }}</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-2">
                                    <div class="fs-sm text-muted">Pending</div>
                                    <div class="fs-5 fw-semibold text-danger">{{ $billsPending }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Aircon Maintenance -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="block block-rounded h-100">
                    <div class="block-content block-content-full">
                        <div class="d-flex align-items-center mb-3">
                            <div class="me-3">
                                <i class="fa fa-snowflake fa-2x text-info"></i>
                            </div>
                            <div>
                                <div class="fs-3 fw-bold">{{ $airconTotal }}</div>
                                <div class="text-muted">Air Conditioners</div>
                            </div>
                        </div>
                        <div class="fs-sm mb-1">
                            <i class="fa fa-check-circle text-success me-1"></i>Operational: {{ $airconOperational }}
                        </div>
                        <div class="fs-sm mb-1">
                            <i class="fa fa-exclamation-triangle text-warning me-1"></i>Service Req: {{ $airconServiceRequired }}
                        </div>
                        <div class="fs-sm mb-1">
                            <i class="fa fa-times-circle text-danger me-1"></i>Damaged: {{ $airconDamaged }}
                        </div>
                        <div class="fs-sm">
                            <i class="fa fa-calendar text-primary me-1"></i>Upcoming Service: {{ $airconUpcomingService }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Fire Extinguishers -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="block block-rounded h-100">
                    <div class="block-content block-content-full">
                        <div class="d-flex align-items-center mb-3">
                            <div class="me-3">
                                <i class="fa fa-fire-extinguisher fa-2x text-danger"></i>
                            </div>
                            <div>
                                <div class="fs-3 fw-bold">{{ $fireExtinguishers }}</div>
                                <div class="text-muted">Fire Extinguishers</div>
                            </div>
                        </div>
                        <div class="row g-0 mb-2">
                            <div class="col-6 border-end">
                                <div class="p-2">
                                    <div class="fs-sm text-muted">Active</div>
                                    <div class="fs-5 fw-semibold text-success">{{ $fireExtinguisherActive }}</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-2">
                                    <div class="fs-sm text-muted">Inactive</div>
                                    <div class="fs-5 fw-semibold text-danger">{{ $fireExtinguisherInactive }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="fs-sm">
                            <span class="badge bg-warning">Upcoming Refill: {{ $fireUpcomingRefill }}</span>
                            <span class="badge bg-danger ms-1">Due: {{ $fireRefillDue }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Office Assets -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="block block-rounded h-100">
                    <div class="block-content block-content-full">
                        <div class="d-flex align-items-center mb-3">
                            <div class="me-3">
                                <i class="fa fa-chair fa-2x text-warning"></i>
                            </div>
                            <div>
                                <div class="fs-3 fw-bold">{{ $assetTotal }}</div>
                                <div class="text-muted">Office Assets</div>
                            </div>
                        </div>
                        <div class="fs-sm mb-1">
                            <i class="fa fa-check-circle text-success me-1"></i>In Use: {{ $assetInUse }}
                        </div>
                        <div class="fs-sm mb-1">
                            <i class="fa fa-tools text-warning me-1"></i>Maintenance: {{ $assetUnderMaintenance }}
                        </div>
                        <div class="fs-sm">
                            <i class="fa fa-trash-alt text-danger me-1"></i>Disposed: {{ $assetRecentDisposed }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- TA/DA Records -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="block block-rounded h-100">
                    <div class="block-content block-content-full">
                        <div class="d-flex align-items-center mb-3">
                            <div class="me-3">
                                <i class="fa fa-file-invoice-dollar fa-2x text-success"></i>
                            </div>
                            <div>
                                <div class="fs-3 fw-bold">{{ $employeesTracked }}</div>
                                <div class="text-muted">TA/DA Records</div>
                            </div>
                        </div>
                        <div class="row g-0">
                            <div class="col-6 border-end">
                                <div class="p-2">
                                    <div class="fs-sm text-muted">Tracked</div>
                                    <div class="fs-5 fw-semibold">{{ $employeesTracked }}</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-2">
                                    <div class="fs-sm text-muted">Pending</div>
                                    <div class="fs-5 fw-semibold text-warning">{{ $tadaPending }}</div>
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
    <!-- Page JS Plugins -->
    <script src="{{ asset('js/plugins/chart.js/chart.umd.js') }}"></script>

    <!-- Page JS Code -->
    <script src="{{ asset('js/pages/be_pages_dashboard.min.js') }}"></script>
@endsection