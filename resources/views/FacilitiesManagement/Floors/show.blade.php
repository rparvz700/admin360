@extends('Partials.app', ['activeMenu' => 'floors'])

@section('title')
    {{ $floor->floor_label ?: 'Floor Details' }}
@endsection

@section('page_title')
    Floor Details
@endsection

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/floor-details.css') }}">
@endsection

@section('content')
    <div class="content fd-page">
        <!-- Clean Enterprise Page Header -->
        <div class="fd-header">
            <div class="fd-header-top">
                <div class="fd-title-group">
                    <div class="fd-title-icon">
                        <i class="fa fa-layer-group"></i>
                    </div>
                    <div>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb breadcrumb-alt mb-1 fs-sm">
                                <li class="breadcrumb-item">
                                    <a href="{{ route('floors.index') }}" class="text-muted">Floors</a>
                                </li>
                                <li class="breadcrumb-item active text-dark" aria-current="page">
                                    {{ $floor->floor_label ?: 'Floor Details' }}
                                </li>
                            </ol>
                        </nav>
                        <div class="d-flex align-items-center gap-2">
                            <h1 class="h4 fw-bold text-dark mb-0">
                                {{ $floor->floor_label ?: 'Floor Details' }}
                            </h1>
                            <span class="fd-pill {{ strtolower($floor->status) == 'active' ? 'fd-pill-active' : 'fd-pill-inactive' }}">
                                <i class="fa fa-circle fs-xs"></i> {{ ucfirst($floor->status ?: 'N/A') }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('floors.index') }}" class="btn btn-sm btn-alt-secondary">
                        <i class="fa fa-arrow-left me-1"></i> Back to List
                    </a>
                    @can('edit-floor')
                        <a href="{{ route('floors.edit', $floor->id) }}" class="btn btn-sm btn-primary">
                            <i class="fa fa-pencil-alt me-1"></i> Edit Floor
                        </a>
                    @endcan
                </div>
            </div>

            <!-- Inline Meta Summary Bar -->
            <div class="fd-header-meta">
                <div class="fd-meta-item">
                    <i class="fa fa-building"></i>
                    <span>Building: <strong>{{ $building ? $building->site_name : 'N/A' }}</strong> @if($building && $building->code)<span class="text-muted">({{ $building->code }})</span>@endif</span>
                </div>
                <div class="fd-meta-dot"></div>
                <div class="fd-meta-item">
                    <i class="fa fa-ruler-combined"></i>
                    <span>Area: <strong>{{ number_format($floor->floor_area_sft) }} sft</strong></span>
                </div>
                <div class="fd-meta-dot"></div>
                <div class="fd-meta-item">
                    <i class="fa fa-dollar-sign"></i>
                    <span>Base Rent: <strong>{{ $rentBase ? '৳' . number_format($rentBase->base_rent, 2) : 'N/A' }}</strong></span>
                </div>
                <div class="fd-meta-dot"></div>
                <div class="fd-meta-item">
                    <i class="fa fa-file-contract"></i>
                    <span>Agreement Ref: <strong>{{ $agreement ? $agreement->agreement_ref_no : 'N/A' }}</strong></span>
                </div>
            </div>
        </div>

        <!-- Section Navigation Tabs -->
        <ul class="nav fd-tabs" id="floorTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="basic-tab" data-bs-toggle="tab" data-bs-target="#basic"
                    type="button" role="tab" aria-controls="basic" aria-selected="true">
                    <i class="fa fa-info-circle me-1"></i> Property Overview
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="agreement-tab" data-bs-toggle="tab" data-bs-target="#agreement"
                    type="button" role="tab" aria-controls="agreement" aria-selected="false">
                    <i class="fa fa-file-contract me-1"></i> Agreement & Financials
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link view-history" id="history-tab" data-bs-toggle="tab"
                    data-bs-target="#history" data-id="{{ $floor->id }}" type="button" role="tab"
                    aria-controls="history" aria-selected="false">
                    <i class="fa fa-history me-1"></i> Activity History
                </button>
            </li>
        </ul>

        <!-- Tab Content Panels -->
        <div class="tab-content" id="floorTabsContent">
            
            <!-- OVERVIEW TAB -->
            <div class="tab-pane fade show active" id="basic" role="tabpanel" aria-labelledby="basic-tab">
                <div class="row g-4">
                    <!-- Floor Details Panel -->
                    <div class="col-lg-7">
                        <div class="fd-panel h-100">
                            <div class="fd-panel-header">
                                <h3 class="fd-panel-title">
                                    <i class="fa fa-layer-group"></i> Floor Specifications
                                </h3>
                            </div>
                            <div class="fd-panel-body">
                                <div class="fd-detail-list">
                                    <div class="fd-detail-item">
                                        <span class="fd-detail-label">Floor Label</span>
                                        <span class="fd-detail-value">{{ $floor->floor_label ?: 'N/A' }}</span>
                                    </div>
                                    <div class="fd-detail-item">
                                        <span class="fd-detail-label">Premises Type</span>
                                        <span class="fd-detail-value">{{ $floor->premises_type ?: 'N/A' }}</span>
                                    </div>
                                    <div class="fd-detail-item">
                                        <span class="fd-detail-label">Total Floor Area</span>
                                        <span class="fd-detail-value num">{{ number_format($floor->floor_area_sft) }} sft</span>
                                    </div>
                                    <div class="fd-detail-item">
                                        <span class="fd-detail-label">DG Space Area</span>
                                        <span class="fd-detail-value num">{{ number_format($floor->dg_space_sft) }} sft</span>
                                    </div>
                                    <div class="fd-detail-item">
                                        <span class="fd-detail-label">Store Space Area</span>
                                        <span class="fd-detail-value num">{{ number_format($floor->store_space_sft) }} sft</span>
                                    </div>
                                    <div class="fd-detail-item">
                                        <span class="fd-detail-label">Car Parking Area</span>
                                        <span class="fd-detail-value num">{{ number_format($floor->car_parking) }} sft</span>
                                    </div>
                                    <div class="fd-detail-item">
                                        <span class="fd-detail-label">Project Name</span>
                                        <span class="fd-detail-value">{{ $floor->project->name ?? 'N/A' }}</span>
                                    </div>
                                    <div class="fd-detail-item">
                                        <span class="fd-detail-label">Status</span>
                                        <div>
                                            <span class="fd-pill {{ strtolower($floor->status) == 'active' ? 'fd-pill-active' : 'fd-pill-inactive' }}">
                                                {{ ucfirst($floor->status ?: 'N/A') }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Building Context Sidebar -->
                    <div class="col-lg-5">
                        <div class="fd-panel h-100">
                            <div class="fd-panel-header">
                                <h3 class="fd-panel-title">
                                    <i class="fa fa-building"></i> Building & Location
                                </h3>
                                @if($building && $building->code)
                                    <span class="fd-pill fd-pill-info">Code: {{ $building->code }}</span>
                                @endif
                            </div>
                            <div class="fd-panel-body">
                                @if ($building)
                                    <div class="fd-sidebar-block">
                                        <div class="fw-bold text-dark fs-base mb-1">{{ $building->site_name }}</div>
                                        <div class="text-muted fs-sm">
                                            <i class="fa fa-map-marker-alt me-1 text-muted"></i>
                                            {{ $building->address ?: 'No street address specified' }}
                                        </div>
                                    </div>

                                    <div class="fd-detail-list">
                                        <div class="fd-detail-item">
                                            <span class="fd-detail-label">Division</span>
                                            <span class="fd-detail-value">{{ $building->division ?: 'N/A' }}</span>
                                        </div>
                                        <div class="fd-detail-item">
                                            <span class="fd-detail-label">District</span>
                                            <span class="fd-detail-value">{{ $building->district ?: 'N/A' }}</span>
                                        </div>
                                        <div class="fd-detail-item">
                                            <span class="fd-detail-label">Upazila</span>
                                            <span class="fd-detail-value">{{ $building->upazila ?: 'N/A' }}</span>
                                        </div>
                                        <div class="fd-detail-item">
                                            <span class="fd-detail-label">Area / Location</span>
                                            <span class="fd-detail-value">{{ $building->area ?: 'N/A' }}</span>
                                        </div>
                                    </div>

                                    @if($building->lat || $building->long)
                                        <div class="mt-3 pt-3 border-top d-flex gap-3 fs-xs text-muted">
                                            <div><i class="fa fa-compass me-1"></i> Lat: <span class="fw-medium text-dark">{{ $building->lat ?: 'N/A' }}</span></div>
                                            <div><i class="fa fa-compass me-1"></i> Long: <span class="fw-medium text-dark">{{ $building->long ?: 'N/A' }}</span></div>
                                        </div>
                                    @endif
                                @else
                                    <div class="fd-empty">
                                        <i class="fa fa-building fs-3 text-muted mb-2"></i>
                                        <p class="mb-0 fs-sm">No building information linked to this floor.</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- AGREEMENT & FINANCIALS TAB -->
            <div class="tab-pane fade" id="agreement" role="tabpanel" aria-labelledby="agreement-tab">
                <div class="row g-4 mb-4">
                    <!-- Agreement Details Panel -->
                    <div class="col-lg-6">
                        <div class="fd-panel h-100">
                            <div class="fd-panel-header">
                                <h3 class="fd-panel-title">
                                    <i class="fa fa-file-signature"></i> Agreement Information
                                </h3>
                                @if($agreement)
                                    <span class="fd-pill {{ $agreement->status == '1' ? 'fd-pill-active' : 'fd-pill-inactive' }}">
                                        {{ $agreement->status == '1' ? 'Active' : 'Inactive' }}
                                    </span>
                                @endif
                            </div>
                            <div class="fd-panel-body">
                                @if ($agreement)
                                    <div class="fd-detail-list mb-3">
                                        <div class="fd-detail-item">
                                            <span class="fd-detail-label">Reference No</span>
                                            <span class="fd-detail-value text-primary">{{ $agreement->agreement_ref_no }}</span>
                                        </div>
                                        <div class="fd-detail-item">
                                            <span class="fd-detail-label">Agreement Date</span>
                                            <span class="fd-detail-value">{{ $agreement->agreement_date ? \Carbon\Carbon::parse($agreement->agreement_date)->format('M d, Y') : 'N/A' }}</span>
                                        </div>
                                        <div class="fd-detail-item">
                                            <span class="fd-detail-label">Start Date</span>
                                            <span class="fd-detail-value">{{ $agreement->from_date ? \Carbon\Carbon::parse($agreement->from_date)->format('M d, Y') : 'N/A' }}</span>
                                        </div>
                                        <div class="fd-detail-item">
                                            <span class="fd-detail-label">End Date</span>
                                            <span class="fd-detail-value">{{ $agreement->to_date ? \Carbon\Carbon::parse($agreement->to_date)->format('M d, Y') : 'N/A' }}</span>
                                        </div>
                                    </div>
                                    <div class="p-3 bg-light rounded border">
                                        <span class="fd-detail-label mb-1">Remarks</span>
                                        <span class="fs-sm text-dark">{{ $agreement->remarks ?: 'No remarks recorded.' }}</span>
                                    </div>
                                @else
                                    <div class="fd-empty">
                                        <i class="fa fa-file-contract fs-3 text-muted mb-2"></i>
                                        <p class="mb-0 fs-sm">No agreement records linked to this floor.</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Rent Base Breakdown Panel -->
                    <div class="col-lg-6">
                        <div class="fd-panel h-100">
                            <div class="fd-panel-header">
                                <h3 class="fd-panel-title">
                                    <i class="fa fa-calculator"></i> Rent Base Breakdown
                                </h3>
                                @if($rentBase)
                                    <span class="fd-pill fd-pill-info">{{ $rentBase->rent_type ?: 'Standard' }}</span>
                                @endif
                            </div>
                            <div class="fd-panel-body">
                                @if ($rentBase)
                                    <div class="fd-detail-list">
                                        <div class="fd-detail-item">
                                            <span class="fd-detail-label">Base Rent</span>
                                            <span class="fd-detail-value num fs-5 text-dark">৳{{ number_format($rentBase->base_rent, 2) }}</span>
                                        </div>
                                        <div class="fd-detail-item">
                                            <span class="fd-detail-label">Total (Inc. VAT & TAX)</span>
                                            <span class="fd-detail-value num fs-5 text-success">৳{{ number_format($rentBase->base_rent + $rentBase->vat + $rentBase->tax, 2) }}</span>
                                        </div>
                                        <div class="fd-detail-item">
                                            <span class="fd-detail-label">Tax Deduction At Source</span>
                                            <span class="fd-detail-value">{{ $rentBase->is_at_source ? 'Yes (Applicable)' : 'No' }}</span>
                                        </div>
                                        <div class="fd-detail-item">
                                            <span class="fd-detail-label">Rent Type</span>
                                            <span class="fd-detail-value">{{ $rentBase->rent_type ?: 'N/A' }}</span>
                                        </div>
                                    </div>
                                @else
                                    <div class="fd-empty">
                                        <i class="fa fa-dollar-sign fs-3 text-muted mb-2"></i>
                                        <p class="mb-0 fs-sm">No rent base records available.</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Rent Increments Schedule Table -->
                <div class="fd-panel">
                    <div class="fd-panel-header">
                        <h3 class="fd-panel-title">
                            <i class="fa fa-chart-line"></i> Rent Increments Schedule
                        </h3>
                        <span class="badge bg-light text-dark border fw-normal fs-xs">{{ $rentIncrements->count() }} Record(s)</span>
                    </div>
                    <div class="fd-panel-body p-0">
                        <div class="fd-table-wrap">
                            <table class="fd-table-clean">
                                <thead>
                                    <tr>
                                        <th style="width: 70px;">ID</th>
                                        <th class="num">Incremented Amount</th>
                                        <th>Start Date</th>
                                        <th>End Date</th>
                                        <th class="num">Increment Amount</th>
                                        <th class="num">Percentage</th>
                                        <th>Frequency</th>
                                        <th>Method Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($rentIncrements as $inc)
                                        <tr>
                                            <td class="fw-bold">#{{ $inc->id }}</td>
                                            <td class="num fw-bold text-dark">৳{{ number_format($inc->incremented_amount, 2) }}</td>
                                            <td>{{ $inc->increment_start_date ? \Carbon\Carbon::parse($inc->increment_start_date)->format('M d, Y') : 'N/A' }}</td>
                                            <td>{{ $inc->increment_end_date ? \Carbon\Carbon::parse($inc->increment_end_date)->format('M d, Y') : 'N/A' }}</td>
                                            <td class="num">৳{{ number_format($inc->increment_amount, 2) }}</td>
                                            <td class="num">{{ $inc->increment_percentage }}%</td>
                                            <td>
                                                <span class="fd-pill fd-pill-info">{{ $inc->increment_frequency ?: 'N/A' }}</span>
                                            </td>
                                            <td><span class="fs-xs text-muted">{{ $inc->method_description ?: 'N/A' }}</span></td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8">
                                                <div class="fd-empty">
                                                    <i class="fa fa-inbox fs-3 text-muted mb-2"></i>
                                                    <p class="mb-0 fs-sm">No rent increments configured for this agreement.</p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Security Deposits Absorption Schedule Table -->
                <div class="fd-panel">
                    <div class="fd-panel-header">
                        <h3 class="fd-panel-title">
                            <i class="fa fa-shield-alt"></i> Security Deposits & Absorption Schedule
                        </h3>
                        <span class="badge bg-light text-dark border fw-normal fs-xs">{{ $securityDeposits->count() }} Record(s)</span>
                    </div>
                    <div class="fd-panel-body p-0">
                        <div class="fd-table-wrap">
                            <table class="fd-table-clean">
                                <thead>
                                    <tr>
                                        <th style="width: 70px;">ID</th>
                                        <th class="num">Total Deposit</th>
                                        <th class="num">Absorbable</th>
                                        <th class="num">Non-Absorbable</th>
                                        <th>Adjust Start</th>
                                        <th>Adjust End</th>
                                        <th class="num">Absorb Amount</th>
                                        <th>Frequency</th>
                                        <th class="num">Adjust / Month</th>
                                        <th>Method Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($securityDeposits as $sd)
                                        @php
                                            $monthInterval = (int) ($sd->absorb_frequency ?? 0);
                                            $adjustPerMonth = $monthInterval > 0
                                                ? ((float) ($sd->absorb_amount ?? 0)) / $monthInterval
                                                : null;
                                        @endphp
                                        <tr>
                                            <td class="fw-bold">#{{ $sd->id }}</td>
                                            <td class="num fw-bold text-primary">৳{{ number_format($sd->security_deposit_total, 2) }}</td>
                                            <td class="num">৳{{ number_format($sd->security_deposit_absorbable, 2) }}</td>
                                            <td class="num">৳{{ number_format($sd->security_deposit_non_absorbable, 2) }}</td>
                                            <td>{{ $sd->absorb_start_date ? \Carbon\Carbon::parse($sd->absorb_start_date)->format('M d, Y') : 'N/A' }}</td>
                                            <td>{{ $sd->absorb_end_date ? \Carbon\Carbon::parse($sd->absorb_end_date)->format('M d, Y') : 'N/A' }}</td>
                                            <td class="num">৳{{ number_format($sd->absorb_amount, 2) }}</td>
                                            <td>
                                                <span class="fd-pill fd-pill-info">
                                                    {{ $monthInterval > 0 ? $monthInterval . ' Month(s)' : 'N/A' }}
                                                </span>
                                            </td>
                                            <td class="num fw-medium">
                                                {{ $adjustPerMonth !== null ? '৳' . number_format($adjustPerMonth, 2) : 'N/A' }}
                                            </td>
                                            <td><span class="fs-xs text-muted">{{ $sd->method_description ?: 'N/A' }}</span></td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="10">
                                                <div class="fd-empty">
                                                    <i class="fa fa-inbox fs-3 text-muted mb-2"></i>
                                                    <p class="mb-0 fs-sm">No security deposits found for this agreement.</p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ACTIVITY HISTORY TAB -->
            <div class="tab-pane fade" id="history" role="tabpanel" aria-labelledby="history-tab">
                <div class="fd-panel">
                    <div class="fd-panel-header">
                        <h3 class="fd-panel-title">
                            <i class="fa fa-history"></i> Floor Change History & Audit Logs
                        </h3>
                    </div>
                    <div class="fd-panel-body p-0">
                        <div class="fd-table-wrap">
                            <table class="fd-table-clean">
                                <thead>
                                    <tr>
                                        <th style="width: 170px;">Date & Time</th>
                                        <th style="width: 140px;">User</th>
                                        <th style="width: 160px;">Modified Field</th>
                                        <th>Previous Value</th>
                                        <th>New Value</th>
                                    </tr>
                                </thead>
                                <tbody id="history-items-body">
                                    <tr>
                                        <td colspan="5" class="fd-empty py-4">
                                            <i class="fa fa-spinner fa-spin me-2"></i> Loading change history...
                                        </td>
                                    </tr>
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
    <script src="{{ asset('js/lib/jquery.min.js') }}"></script>

    <script>
        $(document).on('click', '.view-history', function() {
            let id = $(this).data('id');
            $('#history-items-body').html('<tr><td colspan="5" class="fd-empty py-4"><i class="fa fa-spinner fa-spin me-2"></i> Loading change history...</td></tr>');

            $.get(`/floors/${id}/history`, function(data) {
                let html = '';
                if (data && data.length > 0) {
                    data.forEach(log => {
                        if (log.changes && log.changes.length > 0) {
                            log.changes.forEach(change => {
                                html += `<tr>
                                    <td class="text-muted fs-xs">${log.date}</td>
                                    <td><span class="fw-medium text-dark">${log.user}</span></td>
                                    <td><span class="badge bg-light text-dark border">${change.field}</span></td>
                                    <td class="text-danger">${change.from !== null ? change.from : '<em class="text-muted">None</em>'}</td>
                                    <td class="text-success fw-medium">${change.to !== null ? change.to : '<em class="text-muted">None</em>'}</td>
                                </tr>`;
                            });
                        }
                    });
                }
                $('#history-items-body').html(html ||
                    '<tr><td colspan="5" class="fd-empty py-4"><i class="fa fa-inbox me-2 text-muted"></i> No change history records found for this floor.</td></tr>');
            }).fail(function() {
                $('#history-items-body').html(
                    '<tr><td colspan="5" class="fd-empty py-4 text-danger"><i class="fa fa-exclamation-triangle me-2"></i> Failed to load change history.</td></tr>'
                );
            });
        });
    </script>
@endsection
