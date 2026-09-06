@if ($errors->any())
    <div class="alert alert-danger d-flex align-items-start gap-2 mb-4" role="alert">
        <i class="fa fa-exclamation-circle mt-1"></i>
        <div>
            <div class="fw-semibold">Please review the vehicle information:</div>
            <ul class="mb-0 small ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif

<div class="row g-4">
    <!-- Section 1: Classification & Core Identity -->
    <div class="col-12">
        <div class="block block-rounded block-bordered mb-0 shadow-sm">
            <div class="block-header block-header-default bg-body-light py-2">
                <h3 class="block-title fs-sm fw-bold text-uppercase">
                    <i class="fa fa-id-card me-1 text-primary"></i> 1. Vehicle Identity & Classification
                </h3>
            </div>
            <div class="block-content pt-3 pb-2">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="vehicle_type_id">Vehicle Type <span class="text-danger">*</span></label>
                        <select class="form-select" id="vehicle_type_id" name="vehicle_type_id" required>
                            <option value="">Select Type</option>
                            @foreach($vehicleTypes as $type)
                                <option value="{{ $type->id }}" {{ old('vehicle_type_id', $vehicle->vehicle_type_id ?? '') == $type->id ? 'selected' : '' }}>{{ $type->type_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="registration_number">Registration Number <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="registration_number" name="registration_number" value="{{ old('registration_number', $vehicle->registration_number ?? '') }}" placeholder="e.g. Dhaka Metro-GA-11-2233" required>
                    </div>

                    @php
                        $brands = \App\Models\Vehicle::getBrands();
                        $currentBrand = old('brand', isset($vehicle) ? ($vehicle->brand ?? '') : '');
                        if (!empty($currentBrand) && !in_array($currentBrand, $brands)) {
                            $brands[] = $currentBrand;
                        }
                    @endphp
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="brand">Brand</label>
                        <select class="js-select2 form-select" id="brand" name="brand" style="width: 100%;" data-placeholder="Select Brand">
                            <option></option>
                            @foreach($brands as $brandOption)
                                <option value="{{ $brandOption }}" {{ $currentBrand == $brandOption ? 'selected' : '' }}>{{ $brandOption }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="model">Model</label>
                        <input type="text" class="form-control" id="model" name="model" value="{{ old('model', $vehicle->model ?? '') }}" placeholder="e.g. Corolla, Noah, Hilux">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="manufacture_year">Manufacture Year</label>
                        <input type="number" class="form-control" id="manufacture_year" name="manufacture_year" value="{{ old('manufacture_year', $vehicle->manufacture_year ?? '') }}" min="1900" max="{{ date('Y') + 1 }}" placeholder="e.g. 2020">
                    </div>
                    @php
                        $colors = \App\Models\Vehicle::getColors();
                        $currentColor = old('color', isset($vehicle) ? ($vehicle->color ?? '') : '');
                        if (!empty($currentColor) && !in_array($currentColor, $colors)) {
                            $colors[] = $currentColor;
                        }
                    @endphp
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="color">Color</label>
                        <select class="js-select2 form-select" id="color" name="color" style="width: 100%;" data-placeholder="Select Color">
                            <option></option>
                            @foreach($colors as $colorOption)
                                <option value="{{ $colorOption }}" {{ $currentColor == $colorOption ? 'selected' : '' }}>{{ $colorOption }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Section 2: Technical Specifications -->
    <div class="col-12">
        <div class="block block-rounded block-bordered mb-0 shadow-sm">
            <div class="block-header block-header-default bg-body-light py-2">
                <h3 class="block-title fs-sm fw-bold text-uppercase">
                    <i class="fa fa-cogs me-1 text-primary"></i> 2. Technical Specifications
                </h3>
            </div>
            <div class="block-content pt-3 pb-2">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="engine_cc">CC (Engine Displacement)</label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="engine_cc" name="engine_cc" value="{{ old('engine_cc', $vehicle->engine_cc ?? ($vehicle->cc ?? '')) }}" placeholder="e.g. 1500">
                            <span class="input-group-text">cc</span>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="seating_capacity">Seating Capacity</label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="seating_capacity" name="seating_capacity" value="{{ old('seating_capacity', $vehicle->seating_capacity ?? '') }}" min="1" placeholder="e.g. 5">
                            <span class="input-group-text"><i class="fa fa-users"></i></span>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="engine_number">Engine Number</label>
                        <input type="text" class="form-control" id="engine_number" name="engine_number" value="{{ old('engine_number', $vehicle->engine_number ?? '') }}" placeholder="Enter engine serial #">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="chassis_number">Chassis Number</label>
                        <input type="text" class="form-control" id="chassis_number" name="chassis_number" value="{{ old('chassis_number', $vehicle->chassis_number ?? '') }}" placeholder="Enter chassis / VIN #">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Section 3: Operations & Usage -->
    <div class="col-12">
        <div class="block block-rounded block-bordered mb-0 shadow-sm">
            <div class="block-header block-header-default bg-body-light py-2">
                <h3 class="block-title fs-sm fw-bold text-uppercase">
                    <i class="fa fa-route me-1 text-primary"></i> 3. Operations & Usage
                </h3>
            </div>
            <div class="block-content pt-3 pb-2">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="use_purpose">Use Purpose</label>
                        <input type="text" class="form-control" id="use_purpose" name="use_purpose" value="{{ old('use_purpose', $vehicle->use_purpose ?? '') }}" placeholder="e.g. Executive Pool, Field Operations">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="use_company">Use Company</label>
                        <input type="text" class="form-control" id="use_company" name="use_company" value="{{ old('use_company', $vehicle->use_company ?? '') }}" placeholder="e.g. SComm, Summit Communications">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="isRented">Ownership Type</label>
                        <select class="form-select" id="isRented" name="isRented">
                            <option value="0" {{ old('isRented', $vehicle->isRented ?? 0) == 0 ? 'selected' : '' }}>Company Owned</option>
                            <option value="1" {{ old('isRented', $vehicle->isRented ?? 0) == 1 ? 'selected' : '' }}>Rented / Vendor Vehicle</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Section 4: Purchase & Lifecycle Status -->
    <div class="col-12">
        <div class="block block-rounded block-bordered mb-0 shadow-sm">
            <div class="block-header block-header-default bg-body-light py-2">
                <h3 class="block-title fs-sm fw-bold text-uppercase">
                    <i class="fa fa-receipt me-1 text-primary"></i> 4. Purchase & Lifecycle Status
                </h3>
            </div>
            <div class="block-content pt-3 pb-2">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="purchase_price">Purchase Price</label>
                        <div class="input-group">
                            <span class="input-group-text">৳</span>
                            <input type="number" step="0.01" class="form-control" id="purchase_price" name="purchase_price" value="{{ old('purchase_price', $vehicle->purchase_price ?? '') }}" placeholder="0.00">
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="purchase_date">Purchase Date</label>
                        <input type="date" class="form-control" id="purchase_date" name="purchase_date" value="{{ old('purchase_date', $vehicle->purchase_date ?? '') }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="status">Lifecycle Status <span class="text-danger">*</span></label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="active" {{ old('status', $vehicle->status ?? 'active') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status', $vehicle->status ?? '') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            <option value="scrapped" {{ old('status', $vehicle->status ?? '') == 'scrapped' ? 'selected' : '' }}>Scrapped</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


