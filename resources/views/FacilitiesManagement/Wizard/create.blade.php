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

        @if (Session::has('error'))
            <div class="alert alert-danger alert-dismissible m-3 mb-0" role="alert">
                <small class="mb-0">
                    {{ Session::get('error') }}
                </small>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @php
            $invalidClass = fn($field) => $errors->has($field) ? ' is-invalid' : '';
            $errorKeys = array_keys($errors->getMessages());
            $hasErrorFor = fn(array $fields) => collect($errorKeys)->contains(function ($key) use ($fields) {
                foreach ($fields as $field) {
                    if ($key === $field || str_starts_with($key, $field . '.')) {
                        return true;
                    }
                }

                return false;
            });
            $wizardErrorStep = 1;
            if (
                $hasErrorFor([
                    'base_rent',
                    'rent_type',
                    'is_at_source',
                    'increments',
                    'deposits',
                    'security_deposit_total',
                    'security_deposit_absorbable',
                    'security_deposit_non_absorbable',
                ])
            ) {
                $wizardErrorStep = 4;
            } elseif (
                $hasErrorFor([
                    'project_id',
                    'floor_label',
                    'floor_area_sft',
                    'car_parking',
                    'dg_space_sft',
                    'store_space_sft',
                    'premises_type',
                ])
            ) {
                $wizardErrorStep = 3;
            } elseif (
                $hasErrorFor([
                    'building_code',
                    'site_name',
                    'division',
                    'district',
                    'upazila',
                    'area',
                    'address',
                    'lat',
                    'long',
                ])
            ) {
                $wizardErrorStep = 2;
            }
        @endphp

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

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <div class="fw-semibold mb-1">Please review the highlighted fields.</div>
                            <ul class="mb-0 ps-3">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

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
                                    <input type="text" name="agreement_ref_no"
                                        class="form-control{{ $invalidClass('agreement_ref_no') }}" required
                                        value="{{ old('agreement_ref_no') }}">
                                    @error('agreement_ref_no')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="form-label">Agreement Date</label>
                                    <input type="date" name="agreement_date"
                                        class="form-control{{ $invalidClass('agreement_date') }}"
                                        value="{{ old('agreement_date') }}">
                                    @error('agreement_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="form-label">From Date</label>
                                    <input type="date" name="from_date"
                                        class="form-control{{ $invalidClass('from_date') }}"
                                        value="{{ old('from_date') }}">
                                    @error('from_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="form-label">To Date</label>
                                    <input type="date" name="to_date" class="form-control{{ $invalidClass('to_date') }}"
                                        value="{{ old('to_date') }}">
                                    @error('to_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="form-label">Status <span class="text-danger">*</span></label>
                                    <select name="agreement_status"
                                        class="form-select{{ $invalidClass('agreement_status') }}" required>
                                        <option value="1" {{ old('agreement_status', '1') == '1' ? 'selected' : '' }}>
                                            Active</option>
                                        <option value="0" {{ old('agreement_status') == '0' ? 'selected' : '' }}>
                                            Inactive</option>
                                    </select>
                                    @error('agreement_status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="form-label">Remarks</label>
                                    <textarea name="agreement_remarks" class="form-control{{ $invalidClass('agreement_remarks') }}" rows="1">{{ old('agreement_remarks') }}</textarea>
                                    @error('agreement_remarks')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
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
                                    <input type="text" name="building_code"
                                        class="form-control{{ $invalidClass('building_code') }}" required
                                        value="{{ old('building_code') }}">
                                    @error('building_code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="form-label">Site Name</label>
                                    <input type="text" name="site_name"
                                        class="form-control{{ $invalidClass('site_name') }}"
                                        value="{{ old('site_name') }}">
                                    @error('site_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4 mb-2">
                                    <label class="form-label">Division</label>
                                    <select class="form-select js-select2{{ $invalidClass('division') }}" id="division"
                                        name="division">
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
                                    @error('division')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4 mb-2">
                                    <label class="form-label">District</label>
                                    <select class="form-select js-select2{{ $invalidClass('district') }}" id="district"
                                        name="district">
                                        <option value="">Select District</option>
                                    </select>
                                    @error('district')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4 mb-2">
                                    <label class="form-label">Upazila</label>
                                    <select class="form-select js-select2{{ $invalidClass('upazila') }}" id="upazila"
                                        name="upazila"></select>
                                    @error('upazila')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4 mb-2">
                                    <label class="form-label">Area</label>
                                    <input type="text" name="area" class="form-control{{ $invalidClass('area') }}"
                                        value="{{ old('area') }}">
                                    @error('area')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4 mb-2">
                                    <label class="form-label">Latitude</label>
                                    <input type="text" name="lat" id="wizard_lat"
                                        class="form-control{{ $invalidClass('lat') }}" placeholder="23.8103"
                                        value="{{ old('lat') }}">
                                    @error('lat')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4 mb-2">
                                    <label class="form-label">Longitude</label>
                                    <input type="text" name="long" id="wizard_long"
                                        class="form-control{{ $invalidClass('long') }}" placeholder="90.4125"
                                        value="{{ old('long') }}">
                                    @error('long')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4 mb-2">
                                    <label class="form-label">Address</label>
                                    <input type="text" name="address" id="wizard_address"
                                        class="form-control{{ $invalidClass('address') }}" value="{{ old('address') }}">
                                    @error('address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
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
                                    <input type="text" name="floor_label"
                                        class="form-control{{ $invalidClass('floor_label') }}"
                                        placeholder="e.g. 1st Floor" value="{{ old('floor_label') }}">
                                    @error('floor_label')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-3 mb-2">
                                    <label class="form-label">Floor Area (sft)</label>
                                    <input type="number" step="0.01" name="floor_area_sft"
                                        class="form-control{{ $invalidClass('floor_area_sft') }}"
                                        value="{{ old('floor_area_sft') }}">
                                    @error('floor_area_sft')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-3 mb-2">
                                    <label class="form-label">Car Parking</label>
                                    <input type="number" name="car_parking"
                                        class="form-control{{ $invalidClass('car_parking') }}"
                                        value="{{ old('car_parking') }}">
                                    @error('car_parking')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-3 mb-2">
                                    <label class="form-label">DG Space (sft)</label>
                                    <input type="number" step="0.01" name="dg_space_sft"
                                        class="form-control{{ $invalidClass('dg_space_sft') }}"
                                        value="{{ old('dg_space_sft') }}">
                                    @error('dg_space_sft')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-3 mb-2">
                                    <label class="form-label">Store Space (sft)</label>
                                    <input type="number" step="0.01" name="store_space_sft"
                                        class="form-control{{ $invalidClass('store_space_sft') }}"
                                        value="{{ old('store_space_sft') }}">
                                    @error('store_space_sft')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-3 mb-2">
                                    <label class="form-label">Premises Type</label>
                                    <select class="form-select{{ $invalidClass('premises_type') }}" id="premises_type"
                                        name="premises_type">
                                        <option value="">Select Premises Type</option>
                                        @foreach (['Office Room', 'PoP Room', 'DG Room', 'Store Room', 'Power Room', 'Client Room'] as $premisesType)
                                            <option value="{{ $premisesType }}"
                                                {{ old('premises_type') === $premisesType ? 'selected' : '' }}>
                                                {{ $premisesType }}</option>
                                        @endforeach
                                    </select>
                                    @error('premises_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-3 mb-2">
                                    <label class="form-label" for="project">Project</label>
                                    <select class="form-select{{ $invalidClass('project_id') }}" id="project"
                                        name="project_id">
                                        <option value="">Select project</option>
                                        @foreach ($projects as $project)
                                            <option value="{{ $project->id }}"
                                                {{ old('project_id') == $project->id ? 'selected' : '' }}>
                                                {{ $project->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('project_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
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
                                            class="form-control{{ $invalidClass('base_rent') }}" required
                                            value="{{ old('base_rent') }}">
                                        @error('base_rent')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <label class="form-label">Rent Type</label>
                                        <select class="form-select{{ $invalidClass('rent_type') }}" name="rent_type">
                                            <option value="Monthly" {{ old('rent_type') == 'Monthly' ? 'selected' : '' }}>
                                                Monthly</option>
                                            <option value="Quarterly"
                                                {{ old('rent_type') == 'Quarterly' ? 'selected' : '' }}>Quarterly</option>
                                            <option value="Half Yearly"
                                                {{ old('rent_type') == 'Half Yearly' ? 'selected' : '' }}>Half Yearly
                                            </option>
                                            <option value="Yearly" {{ old('rent_type') == 'Yearly' ? 'selected' : '' }}>
                                                Yearly</option>
                                        </select>
                                        @error('rent_type')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <label class="form-label">Is At Source? <span class="text-danger">*</span></label>
                                        <select class="form-select{{ $invalidClass('is_at_source') }}"
                                            name="is_at_source" required>
                                            <option value="">Select</option>
                                            <option value="1" {{ old('is_at_source') == '1' ? 'selected' : '' }}>Yes
                                            </option>
                                            <option value="0" {{ old('is_at_source') == '0' ? 'selected' : '' }}>No
                                            </option>
                                        </select>
                                        @error('is_at_source')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </section>

                            <section class="wizard-finance-panel">
                                <h5 class="mb-3">Utilities & Service Charges</h5>
                                <div class="table-responsive mb-3">
                                    <table class="table table-bordered table-sm wizard-table" id="utilitiesTable">
                                        <thead>
                                            <tr>
                                                <th>Utility Type</th>
                                                <th>Monthly Amount</th>
                                                <th>Disburse with Rent</th>
                                                <th style="width: 80px;" class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Dynamic rows will go here -->
                                        </tbody>
                                    </table>
                                </div>
                                <div class="row g-2 align-items-center">
                                    <div class="col-md-4 col-sm-6">
                                        <select id="utility_type_selector" class="form-select">
                                            <option value="">Choose Utility...</option>
                                            @foreach ($utilityTypes as $type)
                                                <option value="{{ $type->id }}" data-name="{{ $type->name }}">{{ $type->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2 col-sm-6">
                                        <button type="button" class="btn btn-alt-primary" id="addUtilityRowBtn">
                                            <i class="fa fa-plus me-1"></i> Add Utility
                                        </button>
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
                                                <th>Years</th> {{-- NEW FIELD HEADER --}}
                                                <th>End Date</th>
                                                <th>Amount</th>
                                                <th>%</th>
                                                <th>Method Desc</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @if (old('increments'))
                                                @foreach (old('increments') as $index => $increment)
                                                    <tr>
                                                        <td><input type="date"
                                                                name="increments[{{ $index }}][increment_start_date]"
                                                                class="form-control inc-start-date{{ $invalidClass("increments.$index.increment_start_date") }}"
                                                                value="{{ $increment['increment_start_date'] ?? '' }}"
                                                                required>
                                                            @error("increments.$index.increment_start_date")
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        </td>
                                                        <td><input type="number"
                                                                name="increments[{{ $index }}][years]"
                                                                class="form-control inc-years{{ $invalidClass("increments.$index.years") }}"
                                                                min="1" value="{{ $increment['years'] ?? 1 }}"
                                                                required>
                                                            @error("increments.$index.years")
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        </td>
                                                        <td><input type="date"
                                                                name="increments[{{ $index }}][increment_end_date]"
                                                                class="form-control inc-end-date{{ $invalidClass("increments.$index.increment_end_date") }}"
                                                                value="{{ $increment['increment_end_date'] ?? '' }}"
                                                                required>
                                                            @error("increments.$index.increment_end_date")
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        </td>
                                                        <td><input type="number" step="0.01"
                                                                name="increments[{{ $index }}][increment_amount]"
                                                                class="form-control inc-amount{{ $invalidClass("increments.$index.increment_amount") }}"
                                                                value="{{ $increment['increment_amount'] ?? '' }}"
                                                                required>
                                                            @error("increments.$index.increment_amount")
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        </td>
                                                        <td><input type="number" step="0.01"
                                                                name="increments[{{ $index }}][increment_percentage]"
                                                                class="form-control inc-percent{{ $invalidClass("increments.$index.increment_percentage") }}"
                                                                value="{{ $increment['increment_percentage'] ?? '' }}">
                                                            @error("increments.$index.increment_percentage")
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        </td>
                                                        <td><input type="text"
                                                                name="increments[{{ $index }}][method_description]"
                                                                class="form-control{{ $invalidClass("increments.$index.method_description") }}"
                                                                value="{{ $increment['method_description'] ?? '' }}">
                                                            @error("increments.$index.method_description")
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        </td>
                                                        <td class="text-center"><button type="button"
                                                                class="btn btn-alt-danger btn-sm remove-increment"><i
                                                                    class="fa fa-times"></i></button></td>
                                                    </tr>
                                                @endforeach
                                            @endif
                                        </tbody>
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
                                <div id="depositValidationError"
                                    class="alert alert-danger py-2 {{ $errors->has('deposits') ? '' : 'd-none' }}">
                                    {{ $errors->first('deposits') }}
                                </div>
                                <div class="row g-4 mb-3">
                                    <div class="col-md-4"><label class="form-label">Total</label><input type="number"
                                            step="0.01" name="security_deposit_total"
                                            class="form-control{{ $invalidClass('security_deposit_total') }}"
                                            value="{{ old('security_deposit_total') }}">
                                        @error('security_deposit_total')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4"><label class="form-label">Adjustable</label><input
                                            type="number" step="0.01" name="security_deposit_absorbable"
                                            class="form-control{{ $invalidClass('security_deposit_absorbable') }}"
                                            value="{{ old('security_deposit_absorbable') }}">
                                        @error('security_deposit_absorbable')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4"><label class="form-label">Non-Adjustable</label><input
                                            type="number" step="0.01" name="security_deposit_non_absorbable"
                                            class="form-control{{ $invalidClass('security_deposit_non_absorbable') }}"
                                            value="{{ old('security_deposit_non_absorbable') }}">
                                        @error('security_deposit_non_absorbable')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm wizard-table" id="depositsTable">
                                        <thead>
                                            <tr>
                                                <th>Adjust Amount</th>
                                                <th>Month Interval</th>
                                                <th>Adjust / Month</th>
                                                <th>Adjust Start</th>
                                                <th>Adjust End</th>
                                                <th>Method Desc</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @if (old('deposits'))
                                                @foreach (old('deposits') as $index => $deposit)
                                                    <tr>
                                                        <td><input type="number" step="0.01"
                                                                name="deposits[{{ $index }}][absorb_amount]"
                                                                class="form-control abs-amount{{ $invalidClass("deposits.$index.absorb_amount") }}"
                                                                value="{{ $deposit['absorb_amount'] ?? '' }}">
                                                            @error("deposits.$index.absorb_amount")
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        </td>
                                                        <td><input type="number"
                                                                name="deposits[{{ $index }}][month_interval]"
                                                                class="form-control dep-months{{ $invalidClass("deposits.$index.month_interval") }}"
                                                                min="1"
                                                                value="{{ $deposit['month_interval'] ?? ($deposit['years'] ?? 1) }}"
                                                                required>
                                                            @error("deposits.$index.month_interval")
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        </td>
                                                        <td><input type="number" step="0.01"
                                                                name="deposits[{{ $index }}][adjust_per_month]"
                                                                class="form-control dep-per-month{{ $invalidClass("deposits.$index.adjust_per_month") }}"
                                                                value="{{ $deposit['adjust_per_month'] ?? '' }}"
                                                                readonly>
                                                            @error("deposits.$index.adjust_per_month")
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        </td>
                                                        <td><input type="date"
                                                                name="deposits[{{ $index }}][absorb_start_date]"
                                                                class="form-control dep-start-date{{ $invalidClass("deposits.$index.absorb_start_date") }}"
                                                                value="{{ $deposit['absorb_start_date'] ?? '' }}">
                                                            @error("deposits.$index.absorb_start_date")
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        </td>
                                                        <td><input type="date"
                                                                name="deposits[{{ $index }}][absorb_end_date]"
                                                                class="form-control dep-end-date{{ $invalidClass("deposits.$index.absorb_end_date") }}"
                                                                value="{{ $deposit['absorb_end_date'] ?? '' }}" required>
                                                            @error("deposits.$index.absorb_end_date")
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        </td>
                                                        <td><input type="text"
                                                                name="deposits[{{ $index }}][method_description]"
                                                                class="form-control{{ $invalidClass("deposits.$index.method_description") }}"
                                                                value="{{ $deposit['method_description'] ?? '' }}">
                                                            @error("deposits.$index.method_description")
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        </td>
                                                        <td class="text-center"><button type="button"
                                                                class="btn btn-alt-danger btn-sm remove-deposit"><i
                                                                    class="fa fa-times"></i></button></td>
                                                    </tr>
                                                @endforeach
                                            @endif
                                        </tbody>
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
        let currentStep = @json($wizardErrorStep);
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
            let firstInvalidLabel = '';
            const fieldLabel = function(input) {
                const wrapper = input.closest('.mb-2, td, .col-md-4, .col-md-6, .col-md-3');
                const label = wrapper ? wrapper.querySelector('label') : null;
                return label ? label.textContent.replace('*', '').trim() : (input.name || 'This field');
            };

            inputs.forEach(input => {
                // For date fields, check if empty or invalid
                if (input.type === 'date') {
                    if (!input.value) {
                        input.classList.add('is-invalid');
                        valid = false;
                        firstInvalidLabel ||= fieldLabel(input);
                    } else {
                        input.classList.remove('is-invalid');
                    }
                }
                // For select2 fields
                else if ($(input).hasClass('js-select2')) {
                    if (!$(input).val()) {
                        $(input).next('.select2-container').find('.select2-selection').addClass('is-invalid');
                        valid = false;
                        firstInvalidLabel ||= fieldLabel(input);
                    } else {
                        $(input).next('.select2-container').find('.select2-selection').removeClass('is-invalid');
                    }
                }
                // For other required inputs
                else if (!input.value) {
                    input.classList.add('is-invalid');
                    valid = false;
                    firstInvalidLabel ||= fieldLabel(input);
                } else {
                    input.classList.remove('is-invalid');
                }
            });
            if (!valid) {
                One.helpers('jq-notify', {
                    type: 'danger',
                    icon: 'fa fa-times me-1',
                    message: `${firstInvalidLabel} is required.`
                });
            }
            return valid;
        }

        // --- Date Calculation Functions (Renamed for generality) ---
        function calculateEndDate(startDateStr, years) {
            if (!startDateStr || !years || years <= 0) {
                return '';
            }
            const startDate = new Date(startDateStr);
            // Ensure date is valid and adjust for timezone issues if any by getting UTC components
            const startYear = startDate.getFullYear();
            const startMonth = startDate.getMonth();
            const startDay = startDate.getDate();

            if (isNaN(startDate.getTime())) {
                return ''; // Invalid date
            }

            const endDate = new Date(startYear + parseInt(years, 10), startMonth, startDay);
            endDate.setDate(endDate.getDate() - 1); // Subtract one day

            // Format date to YYYY-MM-DD
            const year = endDate.getFullYear();
            const month = String(endDate.getMonth() + 1).padStart(2, '0');
            const day = String(endDate.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        }

        function calculateMonthEndDate(startDateStr, months) {
            if (!startDateStr || !months || months <= 0) {
                return '';
            }

            const startDate = new Date(startDateStr);
            if (isNaN(startDate.getTime())) {
                return '';
            }

            const endDate = new Date(startDate.getFullYear(), startDate.getMonth() + parseInt(months, 10), startDate
                .getDate());
            endDate.setDate(endDate.getDate() - 1);

            const year = endDate.getFullYear();
            const month = String(endDate.getMonth() + 1).padStart(2, '0');
            const day = String(endDate.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        }

        function calculateNextStartDate(endDateStr) {
            if (!endDateStr) {
                return '';
            }
            const endDate = new Date(endDateStr);
            // Ensure date is valid and adjust for timezone issues if any by getting UTC components
            const endYear = endDate.getFullYear();
            const endMonth = endDate.getMonth();
            const endDay = endDate.getDate();

            if (isNaN(endDate.getTime())) {
                return ''; // Invalid date
            }
            const nextStartDate = new Date(endYear, endMonth, endDay + 1); // Add one day

            const year = nextStartDate.getFullYear();
            const month = String(nextStartDate.getMonth() + 1).padStart(2, '0');
            const day = String(nextStartDate.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        }

        // --- Rent Increment specific functions ---
        function updateSubsequentIncrements(changedRow) {
            let currentRow = changedRow;
            let currentEndDate = changedRow.find('.inc-end-date').val();

            while (currentRow.length) {
                const nextRow = currentRow.next('tr');
                if (!nextRow.length) break; // No more rows to update

                const nextStartDateInput = nextRow.find('.inc-start-date');
                const nextYearsInput = nextRow.find('.inc-years');
                const nextEndDateInput = nextRow.find('.inc-end-date');

                const newNextStartDate = calculateNextStartDate(currentEndDate);

                // Only update start date if it's different to prevent unnecessary DOM writes
                if (nextStartDateInput.val() !== newNextStartDate) {
                    nextStartDateInput.val(newNextStartDate);
                }

                const nextYears = nextYearsInput.val();
                const newNextEndDate = calculateEndDate(newNextStartDate, nextYears);

                // Only update end date if it's different
                if (nextEndDateInput.val() !== newNextEndDate) {
                    nextEndDateInput.val(newNextEndDate);
                }

                currentEndDate = newNextEndDate; // Propagate for the next iteration
                currentRow = nextRow; // Move to the next row
                if (!currentEndDate) { // If a row in the chain results in an invalid end date, stop propagating
                    break;
                }
            }
        }

        // --- Security Deposit specific functions ---
        function updateSubsequentDeposits(changedRow) {
            let currentRow = changedRow;
            let currentEndDate = changedRow.find('.dep-end-date').val();

            while (currentRow.length) {
                const nextRow = currentRow.next('tr');
                if (!nextRow.length) break; // No more rows to update

                const nextStartDateInput = nextRow.find('.dep-start-date');
                const nextMonthsInput = nextRow.find('.dep-months');
                const nextEndDateInput = nextRow.find('.dep-end-date');

                const newNextStartDate = calculateNextStartDate(currentEndDate);

                if (nextStartDateInput.val() !== newNextStartDate) {
                    nextStartDateInput.val(newNextStartDate);
                }

                const nextMonths = nextMonthsInput.val();
                const newNextEndDate = calculateMonthEndDate(newNextStartDate, nextMonths);

                if (nextEndDateInput.val() !== newNextEndDate) {
                    nextEndDateInput.val(newNextEndDate);
                }

                currentEndDate = newNextEndDate;
                currentRow = nextRow;
                updateDepositPerMonth(nextRow);
                if (!currentEndDate) {
                    break;
                }
            }
        }

        function updateDepositPerMonth(row) {
            const amount = parseFloat(row.find('.abs-amount').val()) || 0;
            const months = parseInt(row.find('.dep-months').val(), 10) || 0;
            const perMonthInput = row.find('.dep-per-month');

            perMonthInput.val(amount > 0 && months > 0 ? (amount / months).toFixed(2) : '');
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

            // Handle initial load for division/district/upazila if old data exists
            if (selectedDivision) {
                $('#division').val(selectedDivision).trigger('change.select2');
                // loadDistricts is called by the change event above
                if (selectedDistrict) {
                    loadDistricts(selectedDivision, selectedDistrict);
                    // loadUpazilas is called by loadDistricts's trigger('change')
                    if (selectedUpazila) {
                        loadUpazilas(selectedDistrict, selectedUpazila);
                    }
                }
            }


            // --- Dynamic Utilities Logic ---
            const utilitySelector = $('#utility_type_selector');
            const utilitiesTableBody = $('#utilitiesTable tbody');

            // Add Row
            $('#addUtilityRowBtn').click(function() {
                const selectedOption = utilitySelector.find('option:selected');
                const id = selectedOption.val();
                const name = selectedOption.data('name');

                if (!id) {
                    alert('Please select a utility type.');
                    return;
                }

                const row = `
                    <tr data-id="${id}">
                        <td class="align-middle fw-semibold">
                            ${name}
                            <input type="hidden" name="utilities[${id}][id]" value="${id}">
                        </td>
                        <td>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">৳</span>
                                <input type="number" step="0.01" class="form-control form-control-sm" 
                                       name="utilities[${id}][amount]" placeholder="0.00" required>
                            </div>
                        </td>
                        <td class="align-middle">
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input" type="checkbox" 
                                       name="utilities[${id}][disburse_with_rent]" value="1" checked>
                                <label class="form-check-label fs-xs">Disburse with Rent</label>
                            </div>
                        </td>
                        <td class="text-center align-middle">
                            <button type="button" class="btn btn-sm btn-alt-danger remove-utility-row">
                                <i class="fa fa-times"></i>
                            </button>
                        </td>
                    </tr>
                `;
                utilitiesTableBody.append(row);

                selectedOption.prop('disabled', true);
                utilitySelector.val('');
            });

            // Remove Row
            utilitiesTableBody.on('click', '.remove-utility-row', function() {
                const row = $(this).closest('tr');
                const id = row.data('id');

                utilitySelector.find(`option[value="${id}"]`).prop('disabled', false);
                row.remove();
            });

            // --- Rent Increment Logic ---
            let incIdx = {{ count(old('increments', [])) }}; // Initialize index based on old data

            $('#addIncrement').click(function() {
                const lastRow = $('#incrementsTable tbody tr').last();
                let newStartDate = '';

                if (lastRow.length) {
                    const prevEndDate = lastRow.find('.inc-end-date').val();
                    if (prevEndDate) {
                        newStartDate = calculateNextStartDate(prevEndDate);
                    }
                }

                $('#incrementsTable tbody').append(`
                    <tr>
                        <td><input type="date" name="increments[${incIdx}][increment_start_date]" class="form-control inc-start-date" value="${newStartDate}" required></td>
                        <td><input type="number" name="increments[${incIdx}][years]" class="form-control inc-years" min="1" value="1" required></td>
                        <td><input type="date" name="increments[${incIdx}][increment_end_date]" class="form-control inc-end-date" required></td>
                        <td><input type="number" step="0.01" name="increments[${incIdx}][increment_amount]" class="form-control inc-amount" required></td>
                        <td><input type="number" step="0.01" name="increments[${incIdx}][increment_percentage]" class="form-control inc-percent"></td>
                        <td><input type="text" name="increments[${incIdx}][method_description]" class="form-control"></td>
                        <td class="text-center"><button type="button" class="btn btn-alt-danger btn-sm remove-increment"><i class="fa fa-times"></i></button></td>
                    </tr>
                `);

                const newRow = $('#incrementsTable tbody tr').last();
                const startDateInput = newRow.find('.inc-start-date');
                const yearsInput = newRow.find('.inc-years');
                if (startDateInput.val() && yearsInput.val()) {
                    const endDate = calculateEndDate(startDateInput.val(), yearsInput.val());
                    newRow.find('.inc-end-date').val(endDate);
                    updateSubsequentIncrements(newRow);
                }

                incIdx++;
            });

            // Delegated event listener for removing increment rows
            $(document).on('click', '.remove-increment', function() {
                const removedRow = $(this).closest('tr');
                const prevRow = removedRow.prev('tr');
                removedRow.remove();

                if (prevRow.length) {
                    updateSubsequentIncrements(prevRow);
                } else {
                    const firstRemainingRow = $('#incrementsTable tbody tr').first();
                    if (firstRemainingRow.length) {
                        firstRemainingRow.find('.inc-start-date').val('');
                        firstRemainingRow.find('.inc-end-date').val('');
                        firstRemainingRow.find('.inc-years').trigger('change');
                    }
                }
            });

            // Handle changes to start date or years for any increment row
            $(document).on('change', '.inc-start-date, .inc-years', function() {
                const currentRow = $(this).closest('tr');
                const startDateStr = currentRow.find('.inc-start-date').val();
                const years = currentRow.find('.inc-years').val();
                const endDateInput = currentRow.find('.inc-end-date');

                const newEndDate = calculateEndDate(startDateStr, years);
                endDateInput.val(newEndDate);

                updateSubsequentIncrements(currentRow);
            });

            // Initialize/recalculate dates for increments when the page loads (e.g., after validation error)
            $('#incrementsTable tbody tr').each(function() {
                const currentRow = $(this);
                const startDateInput = currentRow.find('.inc-start-date');
                const yearsInput = currentRow.find('.inc-years');
                const endDateInput = currentRow.find('.inc-end-date');

                if (startDateInput.val() && yearsInput.val()) {
                    const newEndDate = calculateEndDate(startDateInput.val(), yearsInput.val());
                    if (endDateInput.val() !== newEndDate) {
                        endDateInput.val(newEndDate);
                    }
                }
            });
            const firstIncrementRow = $('#incrementsTable tbody tr').first();
            if (firstIncrementRow.length) {
                updateSubsequentIncrements(firstIncrementRow);
            }


            // --- Security Deposits Logic ---
            let depIdx = {{ count(old('deposits', [])) }}; // Initialize index based on old data

            $('#addDeposit').click(function() {
                const lastRow = $('#depositsTable tbody tr').last();
                let newStartDate = '';

                if (lastRow.length) {
                    const prevEndDate = lastRow.find('.dep-end-date').val();
                    if (prevEndDate) {
                        newStartDate = calculateNextStartDate(prevEndDate);
                    }
                }

                $('#depositsTable tbody').append(`
                    <tr>
                        <td><input type="number" step="0.01" name="deposits[${depIdx}][absorb_amount]" class="form-control abs-amount"></td>
                        <td><input type="number" name="deposits[${depIdx}][month_interval]" class="form-control dep-months" min="1" value="1" required></td>
                        <td><input type="number" step="0.01" name="deposits[${depIdx}][adjust_per_month]" class="form-control dep-per-month" readonly></td>
                        <td><input type="date" name="deposits[${depIdx}][absorb_start_date]" class="form-control dep-start-date" value="${newStartDate}"></td>
                        <td><input type="date" name="deposits[${depIdx}][absorb_end_date]" class="form-control dep-end-date" required></td>
                        <td><input type="text" name="deposits[${depIdx}][method_description]" class="form-control"></td>
                        <td class="text-center"><button type="button" class="btn btn-alt-danger btn-sm remove-deposit"><i class="fa fa-times"></i></button></td>
                    </tr>
                `);

                const newRow = $('#depositsTable tbody tr').last();
                const startDateInput = newRow.find('.dep-start-date');
                const monthsInput = newRow.find('.dep-months');
                if (startDateInput.val() && monthsInput.val()) {
                    const endDate = calculateMonthEndDate(startDateInput.val(), monthsInput.val());
                    newRow.find('.dep-end-date').val(endDate);
                    updateDepositPerMonth(newRow);
                    updateSubsequentDeposits(newRow);
                }

                depIdx++;
            });

            // Delegated event listener for removing deposit rows
            $(document).on('click', '.remove-deposit', function() {
                const removedRow = $(this).closest('tr');
                const prevRow = removedRow.prev('tr');
                removedRow.remove();

                if (prevRow.length) {
                    updateSubsequentDeposits(prevRow);
                } else {
                    const firstRemainingRow = $('#depositsTable tbody tr').first();
                    if (firstRemainingRow.length) {
                        firstRemainingRow.find('.dep-start-date').val('');
                        firstRemainingRow.find('.dep-end-date').val('');
                        firstRemainingRow.find('.dep-months').trigger('change');
                    }
                }
            });

            // Handle changes to start date or month interval for any deposit row
            $(document).on('change input', '.dep-start-date, .dep-months, .abs-amount', function() {
                const currentRow = $(this).closest('tr');
                const startDateStr = currentRow.find('.dep-start-date').val();
                const months = currentRow.find('.dep-months').val();
                const endDateInput = currentRow.find('.dep-end-date');

                const newEndDate = calculateMonthEndDate(startDateStr, months);
                endDateInput.val(newEndDate);
                updateDepositPerMonth(currentRow);

                updateSubsequentDeposits(currentRow);
            });

            // Initialize/recalculate dates for deposits when the page loads (e.g., after validation error)
            $('#depositsTable tbody tr').each(function() {
                const currentRow = $(this);
                const startDateInput = currentRow.find('.dep-start-date');
                const monthsInput = currentRow.find('.dep-months');
                const endDateInput = currentRow.find('.dep-end-date');

                updateDepositPerMonth(currentRow);

                if (startDateInput.val() && monthsInput.val()) {
                    const newEndDate = calculateMonthEndDate(startDateInput.val(), monthsInput.val());
                    if (endDateInput.val() !== newEndDate) {
                        endDateInput.val(newEndDate);
                    }
                }
            });
            const firstDepositRow = $('#depositsTable tbody tr').first();
            if (firstDepositRow.length) {
                updateSubsequentDeposits(firstDepositRow);
            }


            // --- Form Submission Validation for Deposits ---
            $('#wizardForm').on('submit', function(e) {
                const depositValidationError = $('#depositValidationError');
                depositValidationError.addClass('d-none').text('');

                // Perform step 4 validation here
                if (currentStep === totalSteps) { // Only validate step 4 on submit
                    const absorbable = parseFloat($('[name="security_deposit_absorbable"]').val()) || 0;
                    const nonAbsorbable = parseFloat($('[name="security_deposit_non_absorbable"]').val()) ||
                        0;
                    const depositRows = $('#depositsTable tbody tr').filter(function() {
                        return $(this).find('input').filter(function() {
                            return $(this).hasClass('abs-amount') && $(this).val() !== '' ||
                                $(this).hasClass('dep-start-date') && $(this).val() !==
                                '' ||
                                ($(this).hasClass('dep-months') && $(this).val() !== '1' &&
                                    $(this).val() !== '');
                        }).length > 0;
                    }).length;

                    if ((absorbable > 0 || nonAbsorbable > 0) && depositRows === 0) {
                        e.preventDefault();
                        const message =
                            'Please add at least one deposit schedule row when Adjustable or Non-Adjustable amount is entered.';

                        depositValidationError.removeClass('d-none').text(message);
                        One.helpers('jq-notify', {
                            type: 'danger',
                            icon: 'fa fa-times me-1',
                            message: message
                        });
                        document.getElementById('depositValidationError').scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });
                        return; // Stop form submission
                    }
                }
            });


            function getIncrementBaseForRow(row) {
                let runningRent = parseFloat($('#base_rent').val()) || 0;

                row.prevAll('tr').each(function() {
                    runningRent += parseFloat($(this).find('.inc-amount').val()) || 0;
                });

                return runningRent;
            }

            function refreshIncrementPercentages() {
                let runningRent = parseFloat($('#base_rent').val()) || 0;

                $('#incrementsTable tbody tr').each(function() {
                    const row = $(this);
                    const amount = parseFloat(row.find('.inc-amount').val()) || 0;

                    if (runningRent > 0 && amount > 0) {
                        row.find('.inc-percent').val(((amount / runningRent) * 100).toFixed(2));
                    }

                    runningRent += amount;
                });
            }

            function refreshIncrementAmountsFromPercentages() {
                let runningRent = parseFloat($('#base_rent').val()) || 0;

                $('#incrementsTable tbody tr').each(function() {
                    const row = $(this);
                    const percent = parseFloat(row.find('.inc-percent').val()) || 0;

                    if (runningRent > 0 && percent > 0) {
                        row.find('.inc-amount').val(((percent / 100) * runningRent).toFixed(2));
                    }

                    runningRent += parseFloat(row.find('.inc-amount').val()) || 0;
                });
            }

            // Logic to calculate percentages for increments using cumulative rent.
            $(document).on('input', '.inc-amount', function() {
                let base = getIncrementBaseForRow($(this).closest('tr'));
                let amt = parseFloat($(this).val()) || 0;
                if (base > 0) $(this).closest('tr').find('.inc-percent').val(((amt / base) * 100).toFixed(
                    2));
                refreshIncrementPercentages();
            });

            $(document).on('input', '.inc-percent', function() {
                let base = getIncrementBaseForRow($(this).closest('tr'));
                let percent = parseFloat($(this).val()) || 0;
                if (base > 0) $(this).closest('tr').find('.inc-amount').val(((percent / 100) * base)
                    .toFixed(2));
                refreshIncrementAmountsFromPercentages();
            });

            $('#base_rent').on('input', refreshIncrementAmountsFromPercentages);
            refreshIncrementPercentages();
        });
    </script>
@endsection
