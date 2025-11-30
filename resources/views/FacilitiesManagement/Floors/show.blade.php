@extends('Partials.app', ['activeMenu' => 'floors'])

@section('title')
    {{ config('app.name') }} 
@endsection

@section('page_title')
    Floor Details
@endsection

@section('content')
    <div class="content">
        <div class="block block-rounded shadow-sm">
            <div class="block-header block-header-default" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none;">
                <h3 class="block-title text-white"><i class="fa fa-building me-2"></i>Floor Details</h3>
                <a href="{{ route('floors.index') }}" class="btn btn-sm btn-light"><i class="fa fa-arrow-left me-1"></i>Back to List</a>
            </div>
            <div class="block-content p-4">
                <ul class="nav nav-tabs nav-tabs-alt mb-4" id="floorTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="basic-tab" data-bs-toggle="tab" data-bs-target="#basic" type="button" role="tab" aria-controls="basic" aria-selected="true">
                            <i class="fa fa-info-circle me-1"></i>Basic Info
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="agreement-tab" data-bs-toggle="tab" data-bs-target="#agreement" type="button" role="tab" aria-controls="agreement" aria-selected="false">
                            <i class="fa fa-file-contract me-1"></i>Agreement & Rent
                        </button>
                    </li>
                </ul>
                <div class="tab-content" id="floorTabsContent">
                    <div class="tab-pane fade show active" id="basic" role="tabpanel" aria-labelledby="basic-tab">
                        <!-- Floor Information Card -->
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0"><i class="fa fa-layer-group me-2"></i>Floor Information</h5>
                            </div>
                            <div class="card-body" style="background: linear-gradient(to bottom, #ffffff 0%, #f8f9fa 100%);">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="p-3 rounded" style="background-color: rgba(102, 126, 234, 0.05); border-left: 3px solid #667eea;">
                                            <small class="text-muted d-block mb-1">Floor ID</small>
                                            <strong class="fs-5">{{ $floor->id }}</strong>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="p-3 rounded" style="background-color: rgba(102, 126, 234, 0.05); border-left: 3px solid #667eea;">
                                            <small class="text-muted d-block mb-1">Floor Label</small>
                                            <strong class="fs-5">{{ $floor->floor_label }}</strong>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="p-3 rounded" style="background-color: rgba(118, 75, 162, 0.05); border-left: 3px solid #764ba2;">
                                            <small class="text-muted d-block mb-1">Area (sft)</small>
                                            <strong class="fs-5">{{ number_format($floor->floor_area_sft) }}</strong>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="p-3 rounded" style="background-color: rgba(118, 75, 162, 0.05); border-left: 3px solid #764ba2;">
                                            <small class="text-muted d-block mb-1">DG Space (sft)</small>
                                            <strong class="fs-5">{{ number_format($floor->dg_space_sft) }}</strong>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="p-3 rounded" style="background-color: rgba(118, 75, 162, 0.05); border-left: 3px solid #764ba2;">
                                            <small class="text-muted d-block mb-1">Store Space (sft)</small>
                                            <strong class="fs-5">{{ number_format($floor->store_space_sft) }}</strong>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="p-3 rounded" style="background-color: rgba(40, 167, 69, 0.05); border-left: 3px solid #28a745;">
                                            <small class="text-muted d-block mb-1">Premises Type</small>
                                            <strong>{{ $floor->premises_type }}</strong>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="p-3 rounded" style="background-color: rgba(40, 167, 69, 0.05); border-left: 3px solid #28a745;">
                                            <small class="text-muted d-block mb-1">Car Parking</small>
                                            <strong>{{ $floor->car_parking }}</strong>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="p-3 rounded" style="background-color: rgba(255, 193, 7, 0.05); border-left: 3px solid #ffc107;">
                                            <small class="text-muted d-block mb-1">Project Name</small>
                                            <strong>{{ $floor->project_name }}</strong>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="p-3 rounded" style="background-color: rgba(255, 193, 7, 0.05); border-left: 3px solid #ffc107;">
                                            <small class="text-muted d-block mb-1">Status</small>
                                            <span class="badge bg-{{ ucfirst($floor->status) == 'Active' ? 'success' : 'secondary' }} fs-6">{{ ucfirst($floor->status) }}</span>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <!-- Building Information Card -->
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-info text-white">
                                <h5 class="mb-0"><i class="fa fa-building me-2"></i>Building Information</h5>
                            </div>
                            <div class="card-body" style="background: linear-gradient(to bottom, #ffffff 0%, #f8f9fa 100%);">
                                @if($building)
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="p-3 rounded" style="background-color: rgba(23, 162, 184, 0.05); border-left: 3px solid #17a2b8;">
                                            <small class="text-muted d-block mb-1">Building ID</small>
                                            <strong>{{ $building->id }}</strong>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="p-3 rounded" style="background-color: rgba(23, 162, 184, 0.05); border-left: 3px solid #17a2b8;">
                                            <small class="text-muted d-block mb-1">Site Name</small>
                                            <strong>{{ $building->site_name }}</strong>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="p-3 rounded" style="background-color: rgba(23, 162, 184, 0.05);">
                                            <small class="text-muted d-block mb-1">Code</small>
                                            <strong>{{ $building->code }}</strong>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="p-3 rounded" style="background-color: rgba(23, 162, 184, 0.05);">
                                            <small class="text-muted d-block mb-1">Country</small>
                                            <strong>{{ $building->country }}</strong>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="p-3 rounded" style="background-color: rgba(23, 162, 184, 0.05);">
                                            <small class="text-muted d-block mb-1">Division</small>
                                            <strong>{{ $building->division }}</strong>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="p-3 rounded" style="background-color: rgba(23, 162, 184, 0.05);">
                                            <small class="text-muted d-block mb-1">District</small>
                                            <strong>{{ $building->district }}</strong>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="p-3 rounded" style="background-color: rgba(23, 162, 184, 0.05);">
                                            <small class="text-muted d-block mb-1">Upazila</small>
                                            <strong>{{ $building->upazila }}</strong>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="p-3 rounded" style="background-color: rgba(23, 162, 184, 0.05);">
                                            <small class="text-muted d-block mb-1">Area</small>
                                            <strong>{{ $building->area }}</strong>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="p-3 rounded" style="background-color: rgba(23, 162, 184, 0.05); border-left: 3px solid #17a2b8;">
                                            <small class="text-muted d-block mb-1"><i class="fa fa-map-marker-alt me-1"></i>Address</small>
                                            <strong>{{ $building->address }}</strong>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="p-3 rounded" style="background-color: rgba(23, 162, 184, 0.05);">
                                            <small class="text-muted d-block mb-1">Latitude</small>
                                            <strong>{{ $building->lat }}</strong>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="p-3 rounded" style="background-color: rgba(23, 162, 184, 0.05);">
                                            <small class="text-muted d-block mb-1">Longitude</small>
                                            <strong>{{ $building->long }}</strong>
                                        </div>
                                    </div>
                                </div>
                                @else
                                <div class="alert alert-warning mb-0"><i class="fa fa-exclamation-triangle me-2"></i>No building data available.</div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="agreement" role="tabpanel" aria-labelledby="agreement-tab">
                        <!-- Agreement Card -->
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0"><i class="fa fa-file-signature me-2"></i>Agreement Details</h5>
                            </div>
                            <div class="card-body" style="background: linear-gradient(to bottom, #ffffff 0%, #f8f9fa 100%);">
                                @if($agreement)
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <tbody>
                                            <tr>
                                                <th style="width: 30%; background-color: rgba(40, 167, 69, 0.1);">Reference No</th>
                                                <td><strong>{{ $agreement->agreement_ref_no }}</strong></td>
                                            </tr>
                                            <tr>
                                                <th style="background-color: rgba(40, 167, 69, 0.1);">Agreement Date</th>
                                                <td>{{ $agreement->agreement_date ? \Carbon\Carbon::parse($agreement->agreement_date)->format('M d, Y') : 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <th style="background-color: rgba(40, 167, 69, 0.1);">From Date</th>
                                                <td>{{ $agreement->from_date ? \Carbon\Carbon::parse($agreement->from_date)->format('M d, Y') : 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <th style="background-color: rgba(40, 167, 69, 0.1);">To Date</th>
                                                <td>{{ $agreement->to_date ? \Carbon\Carbon::parse($agreement->to_date)->format('M d, Y') : 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <th style="background-color: rgba(40, 167, 69, 0.1);">Status</th>
                                                <td><span class="badge bg-{{ $agreement->status == 'active' ? 'success' : 'secondary' }}">{{ ucfirst($agreement->status) }}</span></td>
                                            </tr>
                                            <tr>
                                                <th style="background-color: rgba(40, 167, 69, 0.1);">Remarks</th>
                                                <td>{{ $agreement->remarks ?: 'N/A' }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                @else
                                <div class="alert alert-warning mb-0"><i class="fa fa-exclamation-triangle me-2"></i>No agreement data available.</div>
                                @endif
                            </div>
                        </div>

                        <!-- Rent Base Card -->
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-warning text-white">
                                <h5 class="mb-0"><i class="fa fa-dollar-sign me-2"></i>Rent Base</h5>
                            </div>
                            <div class="card-body" style="background: linear-gradient(to bottom, #ffffff 0%, #fff9e6 100%);">
                                @if($rentBase)
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <div class="p-3 text-center rounded" style="background-color: rgba(255, 193, 7, 0.15); border: 2px solid #ffc107;">
                                            <small class="text-muted d-block mb-2">Base Rent</small>
                                            <h4 class="mb-0 text-warning">৳{{ number_format($rentBase->base_rent, 2) }}</h4>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="p-3 text-center rounded" style="background-color: rgba(255, 193, 7, 0.1);">
                                            <small class="text-muted d-block mb-2">VAT</small>
                                            <h5 class="mb-0">{{ $rentBase->vat }}%</h5>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="p-3 text-center rounded" style="background-color: rgba(255, 193, 7, 0.1);">
                                            <small class="text-muted d-block mb-2">Tax</small>
                                            <h5 class="mb-0">{{ $rentBase->tax }}%</h5>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="p-3 text-center rounded" style="background-color: rgba(255, 193, 7, 0.1);">
                                            <small class="text-muted d-block mb-2">At Source</small>
                                            <h5 class="mb-0">{{ $rentBase->is_at_source ? 'Yes' : 'No' }}</h5>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="p-3 rounded" style="background-color: rgba(255, 193, 7, 0.1);">
                                            <small class="text-muted d-block mb-1">Rent Type</small>
                                            <strong>{{ $rentBase->rent_type }}</strong>
                                        </div>
                                    </div>
                                </div>
                                @else
                                <div class="alert alert-warning mb-0"><i class="fa fa-exclamation-triangle me-2"></i>No rent base data available.</div>
                                @endif
                            </div>
                        </div>

                        <!-- Rent Increments Card -->
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-danger text-white">
                                <h5 class="mb-0"><i class="fa fa-chart-line me-2"></i>Rent Increments</h5>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover table-striped mb-0">
                                        <thead style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); color: white;">
                                            <tr>
                                                <th>ID</th>
                                                <th>Incremented Amount</th>
                                                <th>Start Date</th>
                                                <th>End Date</th>
                                                <th>Amount</th>
                                                <th>Percentage</th>
                                                <th>Frequency</th>
                                                <th>Method</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($rentIncrements as $inc)
                                            <tr>
                                                <td><strong>{{ $inc->id }}</strong></td>
                                                <td><strong class="text-danger">৳{{ number_format($inc->incremented_amount, 2) }}</strong></td>
                                                <td>{{ $inc->increment_start_date ? \Carbon\Carbon::parse($inc->increment_start_date)->format('M d, Y') : 'N/A' }}</td>
                                                <td>{{ $inc->increment_end_date ? \Carbon\Carbon::parse($inc->increment_end_date)->format('M d, Y') : 'N/A' }}</td>
                                                <td>৳{{ number_format($inc->increment_amount, 2) }}</td>
                                                <td>{{ $inc->increment_percentage }}%</td>
                                                <td><span class="badge bg-secondary">{{ $inc->increment_frequency }}</span></td>
                                                <td>{{ $inc->method_description }}</td>
                                            </tr>
                                            @empty
                                            <tr><td colspan="8" class="text-center text-muted py-4"><i class="fa fa-inbox me-2"></i>No rent increments found.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Security Deposits Card -->
                        <div class="card border-0 shadow-sm">
                            <div class="card-header text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                <h5 class="mb-0"><i class="fa fa-shield-alt me-2"></i>Security Deposits</h5>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover table-striped mb-0">
                                        <thead style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                                            <tr>
                                                <th>ID</th>
                                                <th>Total</th>
                                                <th>Absorbable</th>
                                                <th>Non-Absorbable</th>
                                                <th>Absorb Start</th>
                                                <th>Absorb End</th>
                                                <th>Amount</th>
                                                <th>Percentage</th>
                                                <th>Frequency</th>
                                                <th>Method</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($securityDeposits as $sd)
                                            <tr>
                                                <td><strong>{{ $sd->id }}</strong></td>
                                                <td><strong class="text-primary">৳{{ number_format($sd->security_deposit_total, 2) }}</strong></td>
                                                <td>৳{{ number_format($sd->security_deposit_absorbable, 2) }}</td>
                                                <td>৳{{ number_format($sd->security_deposit_non_absorbable, 2) }}</td>
                                                <td>{{ $sd->absorb_start_date ? \Carbon\Carbon::parse($sd->absorb_start_date)->format('M d, Y') : 'N/A' }}</td>
                                                <td>{{ $sd->absorb_end_date ? \Carbon\Carbon::parse($sd->absorb_end_date)->format('M d, Y') : 'N/A' }}</td>
                                                <td>৳{{ number_format($sd->absorb_amount, 2) }}</td>
                                                <td>{{ $sd->absorb_amount_percentage }}%</td>
                                                <td><span class="badge bg-secondary">{{ $sd->absorb_frequency }}</span></td>
                                                <td>{{ $sd->method_description }}</td>
                                            </tr>
                                            @empty
                                            <tr><td colspan="10" class="text-center text-muted py-4"><i class="fa fa-inbox me-2"></i>No security deposits found.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection