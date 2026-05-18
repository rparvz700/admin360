@php
    $statusValue = old('status', $floor->status ?? 'Active');
    $premisesTypeValue = old('premises_type', $floor->premises_type ?? '');
@endphp

@if ($errors->any())
    <div class="alert alert-danger d-flex align-items-start gap-2" role="alert">
        <i class="fa fa-exclamation-circle mt-1"></i>
        <div>
            <div class="fw-semibold">Please review the floor information.</div>
            <div class="small">{{ $errors->first() }}</div>
        </div>
    </div>
@endif

<div class="floor-form-section">
    <div class="floor-section-heading">
        <span class="floor-section-icon"><i class="fa fa-layer-group"></i></span>
        <div>
            <h4>Core Details</h4>
            <p>Connect this floor with building, agreement, and operating status.</p>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-6">
            <label class="form-label" for="building_id">Building <span class="text-danger">*</span></label>
            <select class="form-select js-select2 @error('building_id') is-invalid @enderror" id="building_id"
                name="building_id" required>
                <option value="">Select Building</option>
                @foreach ($buildings as $building)
                    <option value="{{ $building->id }}"
                        {{ (string) old('building_id', $floor->building_id ?? '') === (string) $building->id ? 'selected' : '' }}>
                        {{ $building->site_name ?: $building->code }}
                    </option>
                @endforeach
            </select>
            @error('building_id')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-lg-6">
            <label class="form-label" for="agreement_id">Agreement</label>
            <select class="form-select js-select2 @error('agreement_id') is-invalid @enderror" id="agreement_id"
                name="agreement_id">
                <option value="">Select Agreement</option>
                @foreach ($agreements as $agreement)
                    <option value="{{ $agreement->id }}"
                        {{ (string) old('agreement_id', $floor->agreement_id ?? '') === (string) $agreement->id ? 'selected' : '' }}>
                        {{ $agreement->agreement_ref_no }}
                    </option>
                @endforeach
            </select>
            @error('agreement_id')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-lg-4">
            <label class="form-label" for="floor_label">Floor Label</label>
            <input type="text" class="form-control @error('floor_label') is-invalid @enderror" id="floor_label"
                name="floor_label" value="{{ old('floor_label', $floor->floor_label ?? '') }}">
            @error('floor_label')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-lg-4">
            <label class="form-label" for="project">Project</label>
            <select class="form-select js-select2 @error('project_id') is-invalid @enderror" id="project"
                name="project_id">
                <option value="">Select Project</option>
                @foreach ($projects as $project)
                    <option value="{{ $project->id }}"
                        {{ (string) old('project_id', $floor->project_id ?? '') === (string) $project->id ? 'selected' : '' }}>
                        {{ $project->name }}
                    </option>
                @endforeach
            </select>
            @error('project_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-lg-4">
            <label class="form-label" for="status">Status</label>
            <select class="form-select js-select2 @error('status') is-invalid @enderror" id="status" name="status">
                <option value="Active" {{ $statusValue === 'Active' ? 'selected' : '' }}>Active</option>
                <option value="Cancelled" {{ $statusValue === 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>
            @error('status')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<div class="floor-form-section">
    <div class="floor-section-heading">
        <span class="floor-section-icon"><i class="fa fa-ruler-combined"></i></span>
        <div>
            <h4>Area & Type</h4>
            <p>Specify area measurements and floor usage profile.</p>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-4">
            <label class="form-label" for="floor_area_sft">Floor Area (sft)</label>
            <input type="number" step="0.01" class="form-control @error('floor_area_sft') is-invalid @enderror"
                id="floor_area_sft" name="floor_area_sft"
                value="{{ old('floor_area_sft', $floor->floor_area_sft ?? '') }}">
            @error('floor_area_sft')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-lg-4">
            <label class="form-label" for="dg_space_sft">DG Space (sft)</label>
            <input type="number" step="0.01" class="form-control @error('dg_space_sft') is-invalid @enderror"
                id="dg_space_sft" name="dg_space_sft" value="{{ old('dg_space_sft', $floor->dg_space_sft ?? '') }}">
            @error('dg_space_sft')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-lg-4">
            <label class="form-label" for="store_space_sft">Store Space (sft)</label>
            <input type="number" step="0.01" class="form-control @error('store_space_sft') is-invalid @enderror"
                id="store_space_sft" name="store_space_sft"
                value="{{ old('store_space_sft', $floor->store_space_sft ?? '') }}">
            @error('store_space_sft')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-lg-6">
            <label class="form-label" for="premises_type">Premises Type</label>
            <select class="form-select js-select2 @error('premises_type') is-invalid @enderror" id="premises_type"
                name="premises_type">
                <option value="">Select Premises Type</option>
                <option value="Office Room" {{ $premisesTypeValue === 'Office Room' ? 'selected' : '' }}>Office Room
                </option>
                <option value="PoP Room" {{ $premisesTypeValue === 'PoP Room' ? 'selected' : '' }}>PoP Room</option>
                <option value="DG Room" {{ $premisesTypeValue === 'DG Room' ? 'selected' : '' }}>DG Room</option>
                <option value="Store Room" {{ $premisesTypeValue === 'Store Room' ? 'selected' : '' }}>Store Room
                </option>
                <option value="Power Room" {{ $premisesTypeValue === 'Power Room' ? 'selected' : '' }}>Power Room
                </option>
                <option value="Client Room" {{ $premisesTypeValue === 'Client Room' ? 'selected' : '' }}>Client Room
                </option>
            </select>
            @error('premises_type')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-lg-6">
            <label class="form-label" for="car_parking">Car Parking (sft)</label>
            <input type="number" class="form-control @error('car_parking') is-invalid @enderror" id="car_parking"
                name="car_parking" value="{{ old('car_parking', $floor->car_parking ?? '') }}">
            @error('car_parking')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>
