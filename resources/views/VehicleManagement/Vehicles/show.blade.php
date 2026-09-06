@extends('Partials.app', ['activeMenu' => 'vehicles'])
@section('title') Vehicle Details - {{ $vehicle->registration_number }} @endsection

@section('content')
    <!-- Hero -->
    <div class="bg-body-light">
        <div class="content content-full">
            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
                <div class="flex-grow-1">
                    <h1 class="h3 fw-bold mb-1">
                        <i class="fa fa-car me-2 text-primary"></i> {{ $vehicle->registration_number }}
                    </h1>
                    <h2 class="fs-base lh-base fw-medium text-muted mb-0">
                        {{ $vehicle->brand }} {{ $vehicle->model }} &bull; {{ $vehicle->vehicleType->type_name ?? 'Unclassified' }}
                    </h2>
                </div>
                <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-alt">
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="javascript:void(0)">Vehicle Management</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('vehicles.index') }}">Vehicles</a>
                        </li>
                        <li class="breadcrumb-item" aria-current="page">
                            Details
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
    <!-- END Hero -->

    <!-- Page Content -->
    <div class="content">
        <!-- Vehicle Profile Summary Banner -->
        <div class="block block-rounded shadow-sm mb-4">
            <div class="block-content block-content-full">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="item item-2x item-circle bg-primary-light text-primary flex-shrink-0">
                            <i class="fa fa-car-side fs-2"></i>
                        </div>
                        <div>
                            <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                                <h2 class="h4 fw-bold mb-0">{{ $vehicle->registration_number }}</h2>
                                @if(strtolower($vehicle->status) === 'active')
                                    <span class="badge bg-success"><i class="fa fa-check-circle me-1"></i> Active</span>
                                @elseif(strtolower($vehicle->status) === 'inactive')
                                    <span class="badge bg-warning"><i class="fa fa-pause-circle me-1"></i> Inactive</span>
                                @else
                                    <span class="badge bg-danger"><i class="fa fa-times-circle me-1"></i> {{ ucfirst($vehicle->status) }}</span>
                                @endif

                                @if($vehicle->isRented)
                                    <span class="badge bg-warning text-dark"><i class="fa fa-handshake me-1"></i> Rented</span>
                                @else
                                    <span class="badge bg-info"><i class="fa fa-shield-alt me-1"></i> Company Owned</span>
                                @endif
                            </div>
                            <div class="text-muted fs-sm">
                                <span class="fw-semibold text-dark">{{ $vehicle->brand ?? 'N/A' }}</span> {{ $vehicle->model ?? '' }}
                                @if($vehicle->manufacture_year)
                                    &bull; Model Year: <span class="fw-medium text-dark">{{ $vehicle->manufacture_year }}</span>
                                @endif
                                @if($vehicle->color)
                                    &bull; Color: <span class="badge bg-light text-dark border">{{ $vehicle->color }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="d-flex gap-2 flex-shrink-0">
                        <a href="{{ route('vehicles.index') }}" class="btn btn-alt-secondary">
                            <i class="fa fa-arrow-left me-1"></i> Back to Fleet
                        </a>
                        @can('edit-vehicle')
                            <a href="{{ route('vehicles.edit', $vehicle->id) }}" class="btn btn-primary">
                                <i class="fa fa-pencil-alt me-1"></i> Edit Vehicle
                            </a>
                        @endcan
                    </div>
                </div>

                <!-- KPI Metric Strip -->
                <hr class="my-3">
                <div class="row g-3 text-center">
                    <div class="col-6 col-md-3">
                        <div class="p-2 bg-body-light rounded">
                            <div class="fs-xs fw-semibold text-uppercase text-muted">Engine CC</div>
                            <div class="fs-4 fw-bold text-primary">{{ $vehicle->engine_cc ? number_format($vehicle->engine_cc) . ' cc' : 'N/A' }}</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-2 bg-body-light rounded">
                            <div class="fs-xs fw-semibold text-uppercase text-muted">Seating Capacity</div>
                            <div class="fs-4 fw-bold text-dark">{{ $vehicle->seating_capacity ? $vehicle->seating_capacity . ' Persons' : 'N/A' }}</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-2 bg-body-light rounded">
                            <div class="fs-xs fw-semibold text-uppercase text-muted">Vehicle Type</div>
                            <div class="fs-4 fw-bold text-dark">{{ $vehicle->vehicleType->type_name ?? 'N/A' }}</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-2 bg-body-light rounded">
                            <div class="fs-xs fw-semibold text-uppercase text-muted">Purchase Price</div>
                            <div class="fs-4 fw-bold text-success">{{ $vehicle->purchase_price ? '৳ ' . number_format($vehicle->purchase_price, 2) : 'N/A' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Specifications Grid -->
        <div class="row g-4">
            <!-- 1. Identity & Classification -->
            <div class="col-lg-6">
                <div class="block block-rounded block-bordered h-100 shadow-sm mb-0">
                    <div class="block-header block-header-default bg-body-light py-2">
                        <h3 class="block-title fs-sm fw-bold text-uppercase">
                            <i class="fa fa-id-card me-1 text-primary"></i> 1. Identity & Classification
                        </h3>
                    </div>
                    <div class="block-content p-0">
                        <table class="table table-striped table-borderless table-vcenter fs-sm mb-0">
                            <tbody>
                                <tr>
                                    <td class="fw-semibold text-muted" style="width: 40%;">Registration Number</td>
                                    <td class="fw-bold text-dark">{{ $vehicle->registration_number }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold text-muted">Vehicle Type</td>
                                    <td>
                                        <span class="badge bg-primary-light text-primary fw-medium">
                                            {{ $vehicle->vehicleType->type_name ?? 'N/A' }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold text-muted">Brand / Make</td>
                                    <td>{{ $vehicle->brand ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold text-muted">Model Name</td>
                                    <td>{{ $vehicle->model ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold text-muted">Manufacture Year</td>
                                    <td>{{ $vehicle->manufacture_year ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold text-muted">Exterior Color</td>
                                    <td>{{ $vehicle->color ?? 'N/A' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- 2. Technical Specifications -->
            <div class="col-lg-6">
                <div class="block block-rounded block-bordered h-100 shadow-sm mb-0">
                    <div class="block-header block-header-default bg-body-light py-2">
                        <h3 class="block-title fs-sm fw-bold text-uppercase">
                            <i class="fa fa-cogs me-1 text-primary"></i> 2. Technical Specifications
                        </h3>
                    </div>
                    <div class="block-content p-0">
                        <table class="table table-striped table-borderless table-vcenter fs-sm mb-0">
                            <tbody>
                                <tr>
                                    <td class="fw-semibold text-muted" style="width: 40%;">Engine Displacement (CC)</td>
                                    <td class="fw-bold">{{ $vehicle->engine_cc ? number_format($vehicle->engine_cc) . ' cc' : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold text-muted">Seating Capacity</td>
                                    <td>{{ $vehicle->seating_capacity ? $vehicle->seating_capacity . ' Seats' : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold text-muted">Engine Number</td>
                                    <td><code>{{ $vehicle->engine_number ?? 'N/A' }}</code></td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold text-muted">Chassis Number / VIN</td>
                                    <td><code>{{ $vehicle->chassis_number ?? 'N/A' }}</code></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- 3. Operations & Usage -->
            <div class="col-lg-6">
                <div class="block block-rounded block-bordered h-100 shadow-sm mb-0">
                    <div class="block-header block-header-default bg-body-light py-2">
                        <h3 class="block-title fs-sm fw-bold text-uppercase">
                            <i class="fa fa-route me-1 text-primary"></i> 3. Operations & Usage
                        </h3>
                    </div>
                    <div class="block-content p-0">
                        <table class="table table-striped table-borderless table-vcenter fs-sm mb-0">
                            <tbody>
                                <tr>
                                    <td class="fw-semibold text-muted" style="width: 40%;">Assigned Purpose</td>
                                    <td>{{ $vehicle->use_purpose ?? 'General Fleet' }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold text-muted">Operating Company</td>
                                    <td>{{ $vehicle->use_company ?? 'Summit Communications Ltd.' }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold text-muted">Ownership Type</td>
                                    <td>
                                        @if($vehicle->isRented)
                                            <span class="badge bg-warning text-dark"><i class="fa fa-handshake me-1"></i> Rented Fleet</span>
                                        @else
                                            <span class="badge bg-info"><i class="fa fa-shield-alt me-1"></i> Company Owned</span>
                                        @endif
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- 4. Financials & Audit Trail -->
            <div class="col-lg-6">
                <div class="block block-rounded block-bordered h-100 shadow-sm mb-0">
                    <div class="block-header block-header-default bg-body-light py-2">
                        <h3 class="block-title fs-sm fw-bold text-uppercase">
                            <i class="fa fa-receipt me-1 text-primary"></i> 4. Purchase & Audit Trail
                        </h3>
                    </div>
                    <div class="block-content p-0">
                        <table class="table table-striped table-borderless table-vcenter fs-sm mb-0">
                            <tbody>
                                <tr>
                                    <td class="fw-semibold text-muted" style="width: 40%;">Purchase Price</td>
                                    <td class="fw-bold text-success">{{ $vehicle->purchase_price ? '৳ ' . number_format($vehicle->purchase_price, 2) : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold text-muted">Purchase Date</td>
                                    <td>{{ $vehicle->purchase_date ? \Carbon\Carbon::parse($vehicle->purchase_date)->format('d M, Y') : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold text-muted">Record ID</td>
                                    <td>#{{ $vehicle->id }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold text-muted">Created Date</td>
                                    <td>{{ $vehicle->created_at ? $vehicle->created_at->format('d M, Y h:i A') : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold text-muted">Last Updated</td>
                                    <td>{{ $vehicle->updated_at ? $vehicle->updated_at->format('d M, Y h:i A') : 'N/A' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
