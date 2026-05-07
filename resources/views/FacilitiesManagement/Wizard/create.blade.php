@extends('Partials.app', ['activeMenu' => 'agreements'])

@section('title')
    Property Setup Wizard
@endsection

@section('styles')
    <link rel="stylesheet" href="{{ asset('js/plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <link rel="stylesheet" href="{{ asset('css/building-location-map.css') }}">
    <style>
        .wizard-page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .wizard-eyebrow {
            color: #64748b;
            font-size: .75rem;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
            margin-bottom: .25rem;
        }

        .wizard-page-header h2 {
            margin: 0;
            color: #1e293b;
            font-size: 1.75rem;
            font-weight: 700;
        }

        .wizard-page-header p {
            max-width: 680px;
            margin: .35rem 0 0;
            color: #64748b;
        }

        .wizard-shell {
            border: 1px solid #e5e7eb;
            box-shadow: 0 12px 30px rgba(15, 23, 42, .06);
        }

        .wizard-block-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            background: #f8fafc !important;
            border-bottom: 1px solid #e5e7eb;
        }

        .wizard-step-count {
            color: #64748b;
            font-size: .8125rem;
        }

        .wizard-step {
            display: none;
        }

        .wizard-step.active {
            display: block;
        }

        .step-indicator {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: .75rem;
            margin-bottom: 1.5rem;
            padding: 1rem;
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
        }

        .step {
            display: flex;
            align-items: center;
            gap: .75rem;
            min-width: 0;
            padding: .875rem;
            color: #64748b;
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            position: relative;
        }

        .step.active {
            color: #1d4ed8;
            border-color: #bfdbfe;
            background: #eff6ff;
            font-weight: bold;
        }

        .step.completed {
            color: #15803d;
            border-color: #bbf7d0;
            background: #f0fdf4;
        }

        .step-number {
            width: 34px;
            height: 34px;
            line-height: 34px;
            border-radius: 50%;
            background: #e2e8f0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 auto;
            color: #475569;
            font-weight: 700;
        }

        .step.active .step-number {
            background: #2356d7;
            color: #fff;
        }

        .step.completed .step-number {
            background: #16a34a;
            color: #fff;
        }

        .step-copy {
            min-width: 0;
        }

        .step-title {
            display: block;
            color: inherit;
            font-weight: 700;
            line-height: 1.2;
        }

        .step-subtitle {
            display: block;
            margin-top: .15rem;
            color: #64748b;
            font-size: .75rem;
            font-weight: 500;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .wizard-form-section {
            padding: 1.25rem;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            background: #fff;
        }

        .wizard-section-heading {
            display: flex;
            align-items: flex-start;
            gap: .875rem;
            margin-bottom: 1.25rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #eef2f7;
        }

        .wizard-section-icon {
            width: 38px;
            height: 38px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 auto;
            color: #2356d7;
            background: #eef4ff;
            border-radius: 8px;
        }

        .wizard-section-heading h4 {
            margin: 0;
            color: #1e293b;
            font-size: 1.05rem;
            font-weight: 700;
        }

        .wizard-section-heading p {
            margin: .2rem 0 0;
            color: #64748b;
            font-size: .875rem;
        }

        .wizard-finance-panel {
            padding: 1rem;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            background: #fbfdff;
        }

        .wizard-finance-panel+.wizard-finance-panel {
            margin-top: 1rem;
        }

        .wizard-finance-panel h5 {
            margin: 0 0 1rem;
            color: #1e293b;
            font-size: .95rem;
            font-weight: 700;
        }

        .wizard-action-bar {
            display: flex;
            justify-content: flex-end;
            gap: .75rem;
            margin-top: 1.5rem;
            padding: 1rem 1.25rem;
            background: #f8fafc;
            border-top: 1px solid #e5e7eb;
            border-radius: 0 0 8px 8px;
        }

        .wizard-table thead th {
            color: #475569;
            background: #f8fafc;
            font-size: .75rem;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        @media (max-width: 991.98px) {
            .step-indicator {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 575.98px) {

            .wizard-page-header,
            .wizard-block-header,
            .wizard-action-bar {
                align-items: stretch;
                flex-direction: column;
            }

            .step-indicator {
                grid-template-columns: 1fr;
            }

            .wizard-action-bar .btn {
                width: 100%;
            }
        }
    </style>
@endsection

@section('content')
    <div class="content">
        <div class="wizard-page-header">
            <div>
                <div class="wizard-eyebrow">Facilities Management</div>
                <h2>Property Setup Wizard</h2>
                <p>Create the agreement, building, floor profile, and rent setup in one guided workflow.</p>
            </div>
            <a href="{{ route('agreements.index') }}" class="btn btn-alt-secondary">
                <i class="fa fa-arrow-left me-1"></i> Back to Agreements
            </a>
        </div>

        <div class="block block-rounded wizard-shell">
            <div class="block-header block-header-default wizard-block-header">
                <div>
                    <h3 class="block-title">Complete Property Setup</h3>
                    <div class="text-muted fs-sm">Move step by step and review required details before saving.</div>
                </div>
                <div class="wizard-step-count">4-step setup</div>
            </div>
            <div class="block-content">
                <div class="step-indicator">
                    <div class="step active" id="ind-1">
                        <span class="step-number">1</span>
                        <span class="step-copy">
                            <span class="step-title">Agreement</span>
                            <span class="step-subtitle">Reference and dates</span>
                        </span>
                    </div>
                    <div class="step" id="ind-2">
                        <span class="step-number">2</span>
                        <span class="step-copy">
                            <span class="step-title">Building</span>
                            <span class="step-subtitle">Site and location</span>
                        </span>
                    </div>
                    <div class="step" id="ind-3">
                        <span class="step-number">3</span>
                        <span class="step-copy">
                            <span class="step-title">Floor</span>
                            <span class="step-subtitle">Space profile</span>
                        </span>
                    </div>
                    <div class="step" id="ind-4">
                        <span class="step-number">4</span>
                        <span class="step-copy">
                            <span class="step-title">Rent</span>
                            <span class="step-subtitle">Deposits and increments</span>
                        </span>
                    </div>
                </div>

                <form action="{{ route('wizard.property.store') }}" method="POST" id="wizardForm" autocomplete="off">
                    @csrf

                    <!-- STEP 1: Agreement -->
                    <div class="wizard-step active" id="step-1">
                        <div class="wizard-form-section">
                            <div class="wizard-section-heading">
                                <span class="wizard-section-icon"><i class="fa fa-file-signature"></i></span>
                                <div>
                                    <h4>Agreement Information</h4>
                                    <p>Capture the agreement reference, validity window, and current status.</p>
                                </div>
                            </div>
                            <div class="row g-4">
                                <div class="col-md-6 mb-2">
                                    <label class="form-label">Agreement Reference No <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="agreement_ref_no" class="form-control" required
                                        value="{{ old('agreement_ref_no') }}">
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="form-label">Agreement Date</label>
                                    <input type="date" name="agreement_date" class="form-control"
                                        value="{{ old('agreement_date') }}">
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="form-label">From Date</label>
                                    <input type="date" name="from_date" class="form-control"
                                        value="{{ old('from_date') }}">
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="form-label">To Date</label>
                                    <input type="date" name="to_date" class="form-control" value="{{ old('to_date') }}">
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="form-label">Status <span class="text-danger">*</span></label>
                                    <select name="agreement_status" class="form-select" required>
                                        <option value="1">Active</option>
                                        <option value="0">Inactive</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="form-label">Remarks</label>
                                    <textarea name="agreement_remarks" class="form-control" rows="1">{{ old('agreement_remarks') }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- STEP 2: Building -->
                    <div class="wizard-step" id="step-2">
                        <div class="wizard-form-section">
                            <div class="wizard-section-heading">
                                <span class="wizard-section-icon"><i class="fa fa-building"></i></span>
                                <div>
                                    <h4>Building Location</h4>
                                    <p>Define the site identity, administrative area, and map coordinates.</p>
                                </div>
                            </div>
                            <div class="row g-4">
                                <div class="col-md-6 mb-2">
                                    <label class="form-label">Building Code <span class="text-danger">*</span></label>
                                    <input type="text" name="building_code" class="form-control" required
                                        value="{{ old('building_code') }}">
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="form-label">Site Name</label>
                                    <input type="text" name="site_name" class="form-control">
                                </div>
                                <div class="col-md-4 mb-2">
                                    <label class="form-label">Division</label>
                                    <select class="form-select js-select2" id="division" name="division">
                                        <option value="">Select Division</option>
                                        @foreach ($divisions as $division)
                                            @php
                                                $divisionName =
                                                    $division['division'] ??
                                                    ($division['division_name'] ?? ($division['name'] ?? ''));
                                            @endphp
                                            @if ($divisionName)
                                                <option value="{{ $divisionName }}"
                                                    {{ old('division') == $divisionName ? 'selected' : '' }}>
                                                    {{ $divisionName }}
                                                </option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <label class="form-label">District</label>
                                    <select class="form-select js-select2" id="district" name="district">
                                        <option value="">Select District</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <label class="form-label">Upazila</label>
                                    <select class="form-select js-select2" id="upazila" name="upazila"></select>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <label class="form-label">Area</label>
                                    <input type="text" name="area" class="form-control" value="{{ old('area') }}">
                                </div>
                                <div class="col-md-4 mb-2">
                                    <label class="form-label">Latitude</label>
                                    <input type="text" name="lat" id="wizard_lat" class="form-control"
                                        placeholder="23.8103">
                                </div>
                                <div class="col-md-4 mb-2">
                                    <label class="form-label">Longitude</label>
                                    <input type="text" name="long" id="wizard_long" class="form-control"
                                        placeholder="90.4125">
                                </div>
                                <div class="col-md-4 mb-2">
                                    <label class="form-label">Address</label>
                                    <input type="text" name="address" id="wizard_address" class="form-control">
                                </div>
                                <div class="col-md-12 mb-2">
                                    <div class="building-location-map-panel js-building-location-map"
                                        data-lat-input="#wizard_lat" data-lng-input="#wizard_long"
                                        data-address-input="#wizard_address">
                                        <div class="building-location-map-toolbar">
                                            <input type="text" class="form-control building-location-map-search"
                                                data-location-map-search placeholder="Search location">
                                            <button type="button" class="btn btn-alt-primary" data-location-map-locate>
                                                <i class="fa fa-location-arrow me-1"></i> Current
                                            </button>
                                            <button type="button" class="btn btn-alt-secondary" data-location-map-clear>
                                                <i class="fa fa-times me-1"></i> Clear
                                            </button>
                                        </div>
                                        <div class="building-location-search-results list-group" data-location-map-results>
                                        </div>
                                        <div class="building-location-map-canvas" data-location-map-canvas></div>
                                        <div class="building-location-map-footer">
                                            <span data-location-map-status>Type coordinates, search, or click the
                                                map.</span>
                                            <span class="text-muted">OpenStreetMap</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- STEP 3: Floor -->
                    <div class="wizard-step" id="step-3">
                        <div class="wizard-form-section">
                            <div class="wizard-section-heading">
                                <span class="wizard-section-icon"><i class="fa fa-layer-group"></i></span>
                                <div>
                                    <h4>Floor Profile</h4>
                                    <p>Record usable space, parking, premises type, and project assignment.</p>
                                </div>
                            </div>
                            <div class="row g-4">
                                <div class="col-md-6 mb-2">
                                    <label class="form-label">Floor Label</label>
                                    <input type="text" name="floor_label" class="form-control"
                                        placeholder="e.g. 1st Floor">
                                </div>
                                <div class="col-md-3 mb-2">
                                    <label class="form-label">Floor Area (sft)</label>
                                    <input type="number" step="0.01" name="floor_area_sft" class="form-control">
                                </div>
                                <div class="col-md-3 mb-2">
                                    <label class="form-label">Car Parking</label>
                                    <input type="number" name="car_parking" class="form-control">
                                </div>
                                <div class="col-md-3 mb-2">
                                    <label class="form-label">DG Space (sft)</label>
                                    <input type="number" step="0.01" name="dg_space_sft" class="form-control">
                                </div>
                                <div class="col-md-3 mb-2">
                                    <label class="form-label">Store Space (sft)</label>
                                    <input type="number" step="0.01" name="store_space_sft" class="form-control">
                                </div>
                                <div class="col-md-3 mb-2">
                                    <label class="form-label">Premises Type</label>
                                    {{-- <input type="text" name="premises_type" class="form-control"> --}}
                                    <select class="form-select" id="premises_type" name="premises_type">
                                        <option value="">Select Premises Type</option>
                                        <option value="Office Room">Office Room</option>
                                        <option value="PoP Room">PoP Room</option>
                                        <option value="DG Room">DG Room</option>
                                        <option value="Store Room">Store Room</option>
                                        <option value="Power Room">Power Room</option>
                                        <option value="Client Room">Client Room</option>
                                    </select>
                                </div>
                                <div class="col-md-3 mb-2">
                                    <label class="form-label" for="project">Project</label>
                                    <select class="form-select" id="project" name="project_id">
                                        <option value="">Select project</option>
                                        @foreach ($projects as $project)
                                            <option value="{{ $project->id }}">{{ $project->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- STEP 4: Rent -->
                    <div class="wizard-step" id="step-4">
                        <div class="wizard-form-section">
                            <div class="wizard-section-heading">
                                <span class="wizard-section-icon"><i class="fa fa-receipt"></i></span>
                                <div>
                                    <h4>Rent and Deposits</h4>
                                    <p>Configure the base rent, source deduction status, increments, and deposit absorption.
                                    </p>
                                </div>
                            </div>
                            <section class="wizard-finance-panel">
                                <h5>Base Rent</h5>
                                <div class="row g-4">
                                    <div class="col-md-4 mb-2">
                                        <label class="form-label">Base Rent <span class="text-danger">*</span></label>
                                        <input type="number" step="0.01" name="base_rent" id="base_rent"
                                            class="form-control" required>
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <label class="form-label">Rent Type</label>
                                        <select class="form-select" name="rent_type">
                                            <option value="Monthly">Monthly</option>
                                            <option value="Quarterly">Quarterly</option>
                                            <option value="Half Yearly">Half Yearly</option>
                                            <option value="Yearly">Yearly</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <label class="form-label">Is At Source? <span class="text-danger">*</span></label>
                                        <select class="form-select" name="is_at_source" required>
                                            <option value="">Select</option>
                                            <option value="1">Yes</option>
                                            <option value="0">No</option>
                                        </select>
                                    </div>
                                </div>
                            </section>

                            <section class="wizard-finance-panel">
                                <div class="d-flex align-items-center justify-content-between gap-3 mb-3">
                                    <h5 class="mb-0">Rent Increments</h5>
                                    <button type="button" class="btn btn-sm btn-alt-success" id="addIncrement">
                                        <i class="fa fa-plus me-1"></i> Add Increment
                                    </button>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm wizard-table" id="incrementsTable">
                                        <thead>
                                            <tr>
                                                <th>Start Date</th>
                                                <th>End Date</th>
                                                <th>Amount</th>
                                                <th>%</th>
                                                <th>Method Desc</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </section>

                            <section class="wizard-finance-panel">
                                <div class="d-flex align-items-center justify-content-between gap-3 mb-3">
                                    <h5 class="mb-0">Security Deposits</h5>
                                    <button type="button" class="btn btn-sm btn-alt-success" id="addDeposit">
                                        <i class="fa fa-plus me-1"></i> Add Deposit
                                    </button>
                                </div>
                                @error('deposits')
                                    <div class="alert alert-danger py-2">{{ $message }}</div>
                                @enderror
                                <div class="row g-4 mb-3">
                                    <div class="col-md-4"><label>Total</label><input type="number" step="0.01"
                                            name="security_deposit_total" class="form-control"></div>
                                    <div class="col-md-4"><label>Adjustable</label><input type="number" step="0.01"
                                            name="security_deposit_absorbable" class="form-control"></div>
                                    <div class="col-md-4"><label>Non-Adjustable</label><input type="number"
                                            step="0.01" name="security_deposit_non_absorbable" class="form-control">
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm wizard-table" id="depositsTable">
                                        <thead>
                                            <tr>
                                                <th>Adjust Amount</th>
                                                <th>Adjust %</th>
                                                <th>Adjust Start</th>
                                                <th>Adjust End</th>
                                                <th>Method Desc</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </section>
                        </div>
                    </div>

                    <!-- Buttons -->
                    <div class="wizard-action-bar">
                        <button type="button" class="btn btn-alt-secondary" id="btn-prev" onclick="changeStep(-1)"
                            disabled>
                            <i class="fa fa-arrow-left me-1"></i> Previous
                        </button>
                        <button type="button" class="btn btn-primary" id="btn-next" onclick="changeStep(1)">Next <i
                                class="fa fa-arrow-right ms-1"></i></button>
                        <button type="submit" class="btn btn-success d-none" id="btn-submit">
                            <i class="fa fa-check me-1"></i> Save Everything
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('js/plugins/select2/js/select2.full.min.js') }}"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="{{ asset('js/building-location-map.js') }}"></script>
    <script src="{{ asset('js/plugins/bootstrap-notify/bootstrap-notify.min.js') }}"></script>

    <script>
        One.helpersOnLoad(["jq-notify"]);
        let currentStep = @json($errors->has('deposits') ? 4 : 1);
        const totalSteps = 4;

        function syncStepUi() {
            for (let step = 1; step <= totalSteps; step++) {
                document.getElementById(`step-${step}`).classList.toggle('active', step === currentStep);
                document.getElementById(`ind-${step}`).classList.toggle('active', step === currentStep);
                document.getElementById(`ind-${step}`).classList.toggle('completed', step < currentStep);
            }

            document.getElementById('btn-prev').disabled = (currentStep === 1);
            document.getElementById('btn-next').classList.toggle('d-none', currentStep === totalSteps);
            document.getElementById('btn-submit').classList.toggle('d-none', currentStep !== totalSteps);
        }

        function changeStep(n) {
            if (n === 1 && !validateCurrentStep()) return;

            document.getElementById(`step-${currentStep}`).classList.remove('active');
            document.getElementById(`ind-${currentStep}`).classList.remove('active');
            if (n === 1) document.getElementById(`ind-${currentStep}`).classList.add('completed');

            currentStep += n;

            document.getElementById(`step-${currentStep}`).classList.add('active');
            document.getElementById(`ind-${currentStep}`).classList.add('active');

            if (typeof window.refreshBuildingLocationMaps === 'function') {
                window.refreshBuildingLocationMaps();
            }

            document.getElementById('btn-prev').disabled = (currentStep === 1);
            if (currentStep === totalSteps) {
                document.getElementById('btn-next').classList.add('d-none');
                document.getElementById('btn-submit').classList.remove('d-none');
            } else {
                document.getElementById('btn-next').classList.remove('d-none');
                document.getElementById('btn-submit').classList.add('d-none');
            }
        }

        function validateCurrentStep() {
            const activeStep = document.getElementById(`step-${currentStep}`);
            const inputs = activeStep.querySelectorAll('[required]');
            let valid = true;
            inputs.forEach(input => {
                if (!input.value) {
                    input.classList.add('is-invalid');
                    valid = false;
                } else {
                    input.classList.remove('is-invalid');
                }
            });
            if (!valid) {
                One.helpers('jq-notify', {
                    type: 'danger',
                    icon: 'fa fa-times me-1',
                    message: 'Please fill required fields!'
                });
            }
            return valid;
        }

        $(document).ready(function() {
            syncStepUi();

            $('.js-select2').select2({
                width: '100%'
            });

            const allUpazilas = @json($upazillas);
            const allDistricts = @json($districts);
            const selectedDivision = @json(old('division'));
            const selectedDistrict = @json(old('district'));
            const selectedUpazila = @json(old('upazila'));

            function getDivisionName(item) {
                return item.division || item.division_name || item.name || '';
            }

            function getDistrictName(item) {
                return item.district || item.district_name || item.name || '';
            }

            function getUpazilaName(item) {
                return item.upazilla || item.upazila || item.name || '';
            }

            function loadDistricts(division, preselected = null) {
                let $district = $('#district');
                $district.empty().append('<option value="">Select District</option>');
                loadUpazilas('', null);

                if (!division) {
                    $district.prop('disabled', true).trigger('change');
                    return;
                }

                const hasDivisionData = allDistricts.some(function(item) {
                    return getDivisionName(item);
                });
                let filtered = allDistricts.filter(function(item) {
                    if (!hasDivisionData) return true;
                    return getDivisionName(item).trim().toLowerCase() === division.trim().toLowerCase();
                });

                filtered.forEach(function(item) {
                    const district = getDistrictName(item);
                    if (district) {
                        $district.append(new Option(district, district));
                    }
                });

                $district.prop('disabled', false);

                if (preselected) {
                    $district.val(preselected);
                }

                $district.trigger('change');
            }

            function loadUpazilas(district, preselected = null) {
                let $upazila = $('#upazila');
                $upazila.empty().append('<option value="">Select Upazila</option>');

                if (!district) {
                    $upazila.prop('disabled', true).trigger('change');
                    return;
                }

                let filtered = allUpazilas.filter(item => (item.district || '').trim().toLowerCase() === district
                    .trim()
                    .toLowerCase());
                filtered.forEach(function(item) {
                    const upazila = getUpazilaName(item);
                    if (upazila) {
                        $upazila.append(new Option(upazila, upazila));
                    }
                });

                $upazila.prop('disabled', false);

                if (preselected) {
                    $upazila.val(preselected);
                }

                $upazila.trigger('change');
            }

            $('#division').on('change', function() {
                loadDistricts($(this).val());
            });

            $('#district').on('change', function() {
                loadUpazilas($(this).val());
            });

            $('#wizardForm').on('submit', function(e) {
                const absorbable = parseFloat($('[name="security_deposit_absorbable"]').val()) || 0;
                const nonAbsorbable = parseFloat($('[name="security_deposit_non_absorbable"]').val()) || 0;
                const depositRows = $('#depositsTable tbody tr').filter(function() {
                    return $(this).find('input').filter(function() {
                        return $(this).val() !== '';
                    }).length > 0;
                }).length;

                if ((absorbable > 0 || nonAbsorbable > 0) && depositRows === 0) {
                    e.preventDefault();
                    One.helpers('jq-notify', {
                        type: 'danger',
                        icon: 'fa fa-times me-1',
                        message: 'Please add at least one deposit schedule row when Adjustable or Non-Adjustable amount is entered.'
                    });
                }
            });

            if (selectedDivision) {
                $('#division').val(selectedDivision).trigger('change.select2');
            }

            loadDistricts($('#division').val(), selectedDistrict);
            if (selectedDistrict) {
                loadUpazilas(selectedDistrict, selectedUpazila);
            }

            let incIdx = 0;
            $('#addIncrement').click(function() {
                $('#incrementsTable tbody').append(`
                    <tr>
                        <td><input type="date" name="increments[${incIdx}][increment_start_date]" class="form-control" required></td>
                        <td><input type="date" name="increments[${incIdx}][increment_end_date]" class="form-control"></td>
                        <td><input type="number" step="0.01" name="increments[${incIdx}][increment_amount]" class="form-control inc-amount" required></td>
                        <td><input type="number" step="0.01" name="increments[${incIdx}][increment_percentage]" class="form-control inc-percent"></td>
                        <td><input type="text" name="increments[${incIdx}][method_description]" class="form-control"></td>
                        <td class="text-center"><button type="button" class="btn btn-alt-danger btn-sm" onclick="$(this).closest('tr').remove()"><i class="fa fa-times"></i></button></td>
                    </tr>
                `);
                incIdx++;
            });

            let depIdx = 0;
            $('#addDeposit').click(function() {
                $('#depositsTable tbody').append(`
                    <tr>
                        <td><input type="number" step="0.01" name="deposits[${depIdx}][absorb_amount]" class="form-control abs-amount"></td>
                        <td><input type="number" step="0.01" name="deposits[${depIdx}][absorb_amount_percentage]" class="form-control abs-percent"></td>
                        <td><input type="date" name="deposits[${depIdx}][absorb_start_date]" class="form-control"></td>
                        <td><input type="date" name="deposits[${depIdx}][absorb_end_date]" class="form-control"></td>
                        <td><input type="text" name="deposits[${depIdx}][method_description]" class="form-control"></td>
                        <td class="text-center"><button type="button" class="btn btn-alt-danger btn-sm" onclick="$(this).closest('tr').remove()"><i class="fa fa-times"></i></button></td>
                    </tr>
                `);
                depIdx++;
            });

            // Logic to calculate percentages for both Increments and Deposits
            $(document).on('input', '.inc-amount, .abs-amount', function() {
                let base = parseFloat($('#base_rent').val()) || 0;
                let amt = parseFloat($(this).val()) || 0;
                let targetClass = $(this).hasClass('inc-amount') ? '.inc-percent' : '.abs-percent';
                if (base > 0) $(this).closest('tr').find(targetClass).val(((amt / base) * 100).toFixed(2));
            });

            $(document).on('input', '.inc-percent, .abs-percent', function() {
                let base = parseFloat($('#base_rent').val()) || 0;
                let percent = parseFloat($(this).val()) || 0;
                let targetClass = $(this).hasClass('inc-percent') ? '.inc-amount' : '.abs-amount';
                if (base > 0) $(this).closest('tr').find(targetClass).val(((percent / 100) * base).toFixed(
                    2));
            });
        });
    </script>
@endsection
