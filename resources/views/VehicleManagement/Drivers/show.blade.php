@extends('Partials.app', ['activeMenu' => 'drivers'])
@section('title') Driver Profile - {{ $driver->name }} @endsection

@section('content')
    <!-- Hero -->
    <div class="bg-body-light">
        <div class="content content-full">
            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
                <div class="flex-grow-1">
                    <h1 class="h3 fw-bold mb-1">
                        <i class="fa fa-user me-2 text-primary"></i> {{ $driver->name }} {{ $driver->sur_name ?? '' }}
                    </h1>
                    <h2 class="fs-base lh-base fw-medium text-muted mb-0">
                        {{ $driver->designation ?? 'Driver' }} &bull; {{ $driver->department ?? 'Fleet Management' }}
                    </h2>
                </div>
                <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-alt">
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="javascript:void(0)">Vehicle Management</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('drivers.index') }}">Drivers</a>
                        </li>
                        <li class="breadcrumb-item" aria-current="page">
                            Profile
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
    <!-- END Hero -->

    <!-- Page Content -->
    <div class="content">
        <!-- Driver Profile Summary Banner -->
        <div class="block block-rounded shadow-sm mb-4">
            <div class="block-content block-content-full">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="item item-2x item-circle bg-primary-light text-primary flex-shrink-0">
                            @php
                                $initials = strtoupper(substr($driver->name, 0, 1) . substr($driver->sur_name ?? '', 0, 1));
                                if (empty(trim($initials))) { $initials = 'DR'; }
                            @endphp
                            <span class="fs-4 fw-bold">{{ $initials }}</span>
                        </div>
                        <div>
                            <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                                <h2 class="h4 fw-bold mb-0">{{ $driver->name }} {{ $driver->sur_name ?? '' }}</h2>
                                <span class="badge bg-secondary font-monospace">
                                    <i class="fa fa-id-badge me-1"></i> HR: {{ $driver->hr_id }}
                                </span>
                                @if($driver->blood_group)
                                    <span class="badge bg-danger-light text-danger fw-bold">
                                        <i class="fa fa-tint me-1"></i> {{ $driver->blood_group }}
                                    </span>
                                @endif
                                @if($driver->employment_contract)
                                    <span class="badge bg-primary-light text-primary">
                                        {{ $driver->employment_contract }}
                                    </span>
                                @endif
                            </div>
                            <div class="text-muted fs-sm">
                                <span class="fw-semibold text-dark">{{ $driver->designation ?? 'Driver' }}</span>
                                @if($driver->department)
                                    &bull; <span>{{ $driver->department }}</span>
                                @endif
                                @if($driver->office_location || $driver->job_location)
                                    &bull; <i class="fa fa-map-marker-alt me-1 text-muted"></i>{{ $driver->office_location ?? $driver->job_location }}
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="d-flex gap-2 flex-shrink-0">
                        <a href="{{ route('drivers.index') }}" class="btn btn-alt-secondary">
                            <i class="fa fa-arrow-left me-1"></i> Back to Roster
                        </a>
                        @can('edit-driver')
                            <a href="{{ route('drivers.edit', $driver->id) }}" class="btn btn-primary">
                                <i class="fa fa-pencil-alt me-1"></i> Edit Profile
                            </a>
                        @endcan
                    </div>
                </div>

                <!-- KPI Metric Strip -->
                <hr class="my-3">
                <div class="row g-3 text-center">
                    <div class="col-6 col-md-3">
                        <div class="p-2 bg-body-light rounded">
                            <div class="fs-xs fw-semibold text-uppercase text-muted">HR Identifier</div>
                            <div class="fs-4 fw-bold font-monospace text-primary">{{ $driver->hr_id }}</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-2 bg-body-light rounded">
                            <div class="fs-xs fw-semibold text-uppercase text-muted">Phone Number</div>
                            <div class="fs-5 fw-bold text-dark text-truncate">
                                @if($driver->phone)
                                    <a href="tel:{{ $driver->phone }}" class="text-dark">{{ $driver->phone }}</a>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-2 bg-body-light rounded">
                            <div class="fs-xs fw-semibold text-uppercase text-muted">Blood Group</div>
                            <div class="fs-4 fw-bold text-danger">{{ $driver->blood_group ?? 'N/A' }}</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-2 bg-body-light rounded">
                            <div class="fs-xs fw-semibold text-uppercase text-muted">Contract Type</div>
                            <div class="fs-5 fw-bold text-dark text-truncate">{{ $driver->employment_contract ?? 'N/A' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Profile Grid -->
        <div class="row g-4">
            <!-- 1. Personal Identity -->
            <div class="col-lg-6">
                <div class="block block-rounded block-bordered h-100 shadow-sm mb-0">
                    <div class="block-header block-header-default bg-body-light py-2">
                        <h3 class="block-title fs-sm fw-bold text-uppercase">
                            <i class="fa fa-id-card me-1 text-primary"></i> 1. Personal Information
                        </h3>
                    </div>
                    <div class="block-content p-0">
                        <table class="table table-striped table-borderless table-vcenter fs-sm mb-0">
                            <tbody>
                                <tr>
                                    <td class="fw-semibold text-muted" style="width: 40%;">Full Name</td>
                                    <td class="fw-bold text-dark">{{ $driver->name }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold text-muted">Surname</td>
                                    <td>{{ $driver->sur_name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold text-muted">National ID (NID)</td>
                                    <td>
                                        @if($driver->nid)
                                            <span class="font-monospace fw-semibold">{{ $driver->nid }}</span>
                                        @else
                                            <span class="text-muted">Not Provided</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold text-muted">Gender</td>
                                    <td>{{ $driver->gender ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold text-muted">Date of Birth</td>
                                    <td>
                                        @if($driver->date_of_birth)
                                            {{ \Carbon\Carbon::parse($driver->date_of_birth)->format('d M, Y') }}
                                            <span class="text-muted fs-xs">({{ \Carbon\Carbon::parse($driver->date_of_birth)->age }} yrs)</span>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold text-muted">Marital Status</td>
                                    <td>{{ $driver->marital_status ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold text-muted">Passport Number</td>
                                    <td>{{ $driver->passport_no ?? 'N/A' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- 2. Contact & Emergency -->
            <div class="col-lg-6">
                <div class="block block-rounded block-bordered h-100 shadow-sm mb-0">
                    <div class="block-header block-header-default bg-body-light py-2">
                        <h3 class="block-title fs-sm fw-bold text-uppercase">
                            <i class="fa fa-phone-alt me-1 text-primary"></i> 2. Contact & Emergency Details
                        </h3>
                    </div>
                    <div class="block-content p-0">
                        <table class="table table-striped table-borderless table-vcenter fs-sm mb-0">
                            <tbody>
                                <tr>
                                    <td class="fw-semibold text-muted" style="width: 40%;">Primary Phone</td>
                                    <td>
                                        @if($driver->phone)
                                            <a href="tel:{{ $driver->phone }}" class="fw-bold text-primary">
                                                <i class="fa fa-phone me-1"></i> {{ $driver->phone }}
                                            </a>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold text-muted">Emergency Contact</td>
                                    <td>
                                        @if($driver->emergency_contact)
                                            <a href="tel:{{ $driver->emergency_contact }}" class="fw-bold text-danger">
                                                <i class="fa fa-phone-square me-1"></i> {{ $driver->emergency_contact }}
                                            </a>
                                        @else
                                            <span class="text-muted">Not Provided</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold text-muted">Email Address</td>
                                    <td>
                                        @if($driver->email)
                                            <a href="mailto:{{ $driver->email }}" class="text-dark">
                                                <i class="fa fa-envelope me-1 text-muted"></i> {{ $driver->email }}
                                            </a>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold text-muted">System Record ID</td>
                                    <td>#{{ $driver->id }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold text-muted">Record Created</td>
                                    <td>{{ $driver->created_at ? $driver->created_at->format('d M, Y h:i A') : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold text-muted">Last Updated</td>
                                    <td>{{ $driver->updated_at ? $driver->updated_at->format('d M, Y h:i A') : 'N/A' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- 3. Employment & Placement -->
            <div class="col-lg-6">
                <div class="block block-rounded block-bordered h-100 shadow-sm mb-0">
                    <div class="block-header block-header-default bg-body-light py-2">
                        <h3 class="block-title fs-sm fw-bold text-uppercase">
                            <i class="fa fa-briefcase me-1 text-primary"></i> 3. Employment & Placement
                        </h3>
                    </div>
                    <div class="block-content p-0">
                        <table class="table table-striped table-borderless table-vcenter fs-sm mb-0">
                            <tbody>
                                <tr>
                                    <td class="fw-semibold text-muted" style="width: 40%;">Designation</td>
                                    <td class="fw-bold">{{ $driver->designation ?? 'Driver' }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold text-muted">Employment Contract</td>
                                    <td>
                                        <span class="badge bg-primary-light text-primary">
                                            {{ $driver->employment_contract ?? 'N/A' }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold text-muted">Contract Renewed</td>
                                    <td>
                                        @if($driver->contract_renewed)
                                            <span class="badge bg-success-light text-success"><i class="fa fa-check me-1"></i> Yes</span>
                                        @else
                                            <span class="badge bg-light text-dark">No</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold text-muted">Joining Date</td>
                                    <td>{{ $driver->joining_date ? \Carbon\Carbon::parse($driver->joining_date)->format('d M, Y') : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold text-muted">Confirmation Date</td>
                                    <td>{{ $driver->confirmation_date ? \Carbon\Carbon::parse($driver->confirmation_date)->format('d M, Y') : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold text-muted">Contract End Date</td>
                                    <td>{{ $driver->contract_end_date ? \Carbon\Carbon::parse($driver->contract_end_date)->format('d M, Y') : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold text-muted">Department / Division</td>
                                    <td>{{ $driver->department ?? 'N/A' }} {{ $driver->division ? '(' . $driver->division . ')' : '' }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold text-muted">Office / Job Location</td>
                                    <td>{{ $driver->office_location ?? $driver->job_location ?? 'Head Office' }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold text-muted">Subcenter</td>
                                    <td>{{ $driver->subcenter ?? 'N/A' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- 4. Supervision & Billing Approvals -->
            <div class="col-lg-6">
                <div class="block block-rounded block-bordered h-100 shadow-sm mb-0">
                    <div class="block-header block-header-default bg-body-light py-2">
                        <h3 class="block-title fs-sm fw-bold text-uppercase">
                            <i class="fa fa-user-tie me-1 text-primary"></i> 4. Supervision & Approvals
                        </h3>
                    </div>
                    <div class="block-content p-0">
                        <table class="table table-striped table-borderless table-vcenter fs-sm mb-0">
                            <tbody>
                                <tr>
                                    <td colspan="2" class="bg-body-light fw-bold text-uppercase fs-xs text-muted">
                                        Direct Supervisor
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold text-muted" style="width: 40%;">Supervisor Name</td>
                                    <td class="fw-bold">{{ $driver->supervisor_name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold text-muted">Supervisor HR ID</td>
                                    <td>
                                        @if($driver->supervisor_hr_id)
                                            <span class="badge bg-secondary font-monospace">{{ $driver->supervisor_hr_id }}</span>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold text-muted">Supervisor Email</td>
                                    <td>{{ $driver->supervisor_email ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold text-muted">Supervisor Company</td>
                                    <td>{{ $driver->supervisor_company ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td colspan="2" class="bg-body-light fw-bold text-uppercase fs-xs text-muted">
                                        Bill Reviewer
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold text-muted">Reviewer Name</td>
                                    <td class="fw-bold">{{ $driver->bill_reviewer_name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold text-muted">Reviewer HR ID</td>
                                    <td>
                                        @if($driver->bill_reviewer_hr_id)
                                            <span class="badge bg-secondary font-monospace">{{ $driver->bill_reviewer_hr_id }}</span>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold text-muted">Reviewer Email</td>
                                    <td>{{ $driver->bill_reviewer_email ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold text-muted">Reviewer Company</td>
                                    <td>{{ $driver->bill_reviewer_company ?? 'N/A' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
