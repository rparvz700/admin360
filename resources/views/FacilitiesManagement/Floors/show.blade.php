@extends('Partials.app', ['activeMenu' => 'floors'])

@section('title')
    {{ config('app.name') }} 
@endsection

@section('page_title')
    Floor Details
@endsection

@section('content')
    <div class="content">
        <div class="block block-rounded">
            <div class="block-header block-header-default">
                <h3 class="block-title">Floor Details</h3>
                <a href="{{ route('floors.index') }}" class="btn btn-sm btn-secondary">Back to List</a>
            </div>
            <div class="block-content">
                <ul class="nav nav-tabs mb-3" id="floorTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="basic-tab" data-bs-toggle="tab" data-bs-target="#basic" type="button" role="tab" aria-controls="basic" aria-selected="true">Basic Info</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="agreement-tab" data-bs-toggle="tab" data-bs-target="#agreement" type="button" role="tab" aria-controls="agreement" aria-selected="false">Agreement & Rent</button>
                    </li>
                </ul>
                <div class="tab-content" id="floorTabsContent">
                    <div class="tab-pane fade show active" id="basic" role="tabpanel" aria-labelledby="basic-tab">
                        <dl class="row">
                            <dt class="col-sm-3">Floor ID</dt>
                            <dd class="col-sm-9">{{ $floor->id }}</dd>
                            <dt class="col-sm-3">Floor Label</dt>
                            <dd class="col-sm-9">{{ $floor->floor_label }}</dd>
                            <dt class="col-sm-3">Area (sft)</dt>
                            <dd class="col-sm-9">{{ $floor->floor_area_sft }}</dd>
                            <dt class="col-sm-3">Premises Type</dt>
                            <dd class="col-sm-9">{{ $floor->premises_type }}</dd>
                            <dt class="col-sm-3">Car Parking</dt>
                            <dd class="col-sm-9">{{ $floor->car_parking }}</dd>
                            <dt class="col-sm-3">DG Space (sft)</dt>
                            <dd class="col-sm-9">{{ $floor->dg_space_sft }}</dd>
                            <dt class="col-sm-3">Store Space (sft)</dt>
                            <dd class="col-sm-9">{{ $floor->store_space_sft }}</dd>
                            <dt class="col-sm-3">Project Name</dt>
                            <dd class="col-sm-9">{{ $floor->project_name }}</dd>
                            <dt class="col-sm-3">Status</dt>
                            <dd class="col-sm-9">{{ $floor->status }}</dd>
                            <dt class="col-sm-3">Created At</dt>
                            <dd class="col-sm-9">{{ $floor->created_at }}</dd>
                            <dt class="col-sm-3">Updated At</dt>
                            <dd class="col-sm-9">{{ $floor->updated_at }}</dd>
                        </dl>
                        <hr>
                        <h5>Building Info</h5>
                        @if($building)
                        <dl class="row">
                            <dt class="col-sm-3">Building ID</dt>
                            <dd class="col-sm-9">{{ $building->id }}</dd>
                            <dt class="col-sm-3">Site Name</dt>
                            <dd class="col-sm-9">{{ $building->site_name }}</dd>
                            <dt class="col-sm-3">Code</dt>
                            <dd class="col-sm-9">{{ $building->code }}</dd>
                            <dt class="col-sm-3">Country</dt>
                            <dd class="col-sm-9">{{ $building->country }}</dd>
                            <dt class="col-sm-3">Division</dt>
                            <dd class="col-sm-9">{{ $building->division }}</dd>
                            <dt class="col-sm-3">District</dt>
                            <dd class="col-sm-9">{{ $building->district }}</dd>
                            <dt class="col-sm-3">Upazila</dt>
                            <dd class="col-sm-9">{{ $building->upazila }}</dd>
                            <dt class="col-sm-3">Area</dt>
                            <dd class="col-sm-9">{{ $building->area }}</dd>
                            <dt class="col-sm-3">Address</dt>
                            <dd class="col-sm-9">{{ $building->address }}</dd>
                            <dt class="col-sm-3">Latitude</dt>
                            <dd class="col-sm-9">{{ $building->lat }}</dd>
                            <dt class="col-sm-3">Longitude</dt>
                            <dd class="col-sm-9">{{ $building->long }}</dd>
                        </dl>
                        @else
                        <p>No building data available.</p>
                        @endif
                    </div>
                    <div class="tab-pane fade" id="agreement" role="tabpanel" aria-labelledby="agreement-tab">
                        <h5>Agreement</h5>
                        @if($agreement)
                        <table class="table table-bordered table-sm mb-4" style="background-color: #f8f9fa;">
                            <tr><th>Reference No</th><td>{{ $agreement->agreement_ref_no }}</td></tr>
                            <tr><th>Agreement Date</th><td>{{ $agreement->agreement_date }}</td></tr>
                            <tr><th>From Date</th><td>{{ $agreement->from_date }}</td></tr>
                            <tr><th>To Date</th><td>{{ $agreement->to_date }}</td></tr>
                            <tr><th>Status</th><td>{{ $agreement->status }}</td></tr>
                            <tr><th>Remarks</th><td>{{ $agreement->remarks }}</td></tr>
                        </table>
                        @else
                        <p>No agreement data available.</p>
                        @endif
                        <h5>Rent Base</h5>
                        @if($rentBase)
                        <table class="table table-bordered table-sm mb-4" style="background-color: #f8f9fa;">
                            <tr><th>Base Rent</th><td>{{ $rentBase->base_rent }}</td></tr>
                            <tr><th>VAT</th><td>{{ $rentBase->vat }}</td></tr>
                            <tr><th>Tax</th><td>{{ $rentBase->tax }}</td></tr>
                            <tr><th>Is At Source</th><td>{{ $rentBase->is_at_source ? 'Yes' : 'No' }}</td></tr>
                            <tr><th>Rent Type</th><td>{{ $rentBase->rent_type }}</td></tr>
                        </table>
                        @else
                        <p>No rent base data available.</p>
                        @endif
                        <h5>Rent Increments</h5>
                        <table class="table table-bordered table-sm mb-4" style="background-color: #f8f9fa;">
                            <thead>
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
                                    <td>{{ $inc->id }}</td>
                                    <td>{{ $inc->incremented_amount }}</td>
                                    <td>{{ $inc->increment_start_date }}</td>
                                    <td>{{ $inc->increment_end_date }}</td>
                                    <td>{{ $inc->increment_amount }}</td>
                                    <td>{{ $inc->increment_percentage }}</td>
                                    <td>{{ $inc->increment_frequency }}</td>
                                    <td>{{ $inc->method_description }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="8">No rent increments found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                        <h5>Security Deposits</h5>
                        <table class="table table-bordered table-sm mb-4" style="background-color: #f8f9fa;">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Total</th>
                                    <th>Absorbable</th>
                                    <th>Non-Absorbable</th>
                                    <th>Absorb Start</th>
                                    <th>Absorb End</th>
                                    <th>Absorb Amount</th>
                                    <th>Absorb %</th>
                                    <th>Frequency</th>
                                    <th>Method</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($securityDeposits as $sd)
                                <tr>
                                    <td>{{ $sd->id }}</td>
                                    <td>{{ $sd->security_deposit_total }}</td>
                                    <td>{{ $sd->security_deposit_absorbable }}</td>
                                    <td>{{ $sd->security_deposit_non_absorbable }}</td>
                                    <td>{{ $sd->absorb_start_date }}</td>
                                    <td>{{ $sd->absorb_end_date }}</td>
                                    <td>{{ $sd->absorb_amount }}</td>
                                    <td>{{ $sd->absorb_amount_percentage }}</td>
                                    <td>{{ $sd->absorb_frequency }}</td>
                                    <td>{{ $sd->method_description }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="10">No security deposits found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
