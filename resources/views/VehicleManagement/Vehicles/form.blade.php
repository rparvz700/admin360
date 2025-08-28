<div class="mb-3">
    <label class="form-label" for="vehicle_type_id">Vehicle Type <span class="text-danger">*</span></label>
    <select class="form-select" id="vehicle_type_id" name="vehicle_type_id" required>
        <option value="">Select Type</option>
        @foreach($vehicleTypes as $type)
            <option value="{{ $type->id }}" {{ old('vehicle_type_id', $vehicle->vehicle_type_id ?? '') == $type->id ? 'selected' : '' }}>{{ $type->type_name }}</option>
        @endforeach
    </select>
</div>
<div class="mb-3">
    <label class="form-label" for="registration_number">Registration Number <span class="text-danger">*</span></label>
    <input type="text" class="form-control" id="registration_number" name="registration_number" value="{{ old('registration_number', $vehicle->registration_number ?? '') }}" required>
</div>
<div class="mb-3">
    <label class="form-label" for="brand">Brand</label>
    <input type="text" class="form-control" id="brand" name="brand" value="{{ old('brand', $vehicle->brand ?? '') }}">
</div>
<div class="mb-3">
    <label class="form-label" for="model">Model</label>
    <input type="text" class="form-control" id="model" name="model" value="{{ old('model', $vehicle->model ?? '') }}">
</div>
<div class="mb-3">
    <label class="form-label" for="manufacture_year">Manufacture Year</label>
    <input type="number" class="form-control" id="manufacture_year" name="manufacture_year" value="{{ old('manufacture_year', $vehicle->manufacture_year ?? '') }}" min="1900" max="{{ date('Y') }}">
</div>
<div class="mb-3">
    <label class="form-label" for="color">Color</label>
    <input type="text" class="form-control" id="color" name="color" value="{{ old('color', $vehicle->color ?? '') }}">
</div>
<div class="mb-3">
    <label class="form-label" for="seating_capacity">Seating Capacity</label>
    <input type="number" class="form-control" id="seating_capacity" name="seating_capacity" value="{{ old('seating_capacity', $vehicle->seating_capacity ?? '') }}">
</div>
<div class="mb-3">
    <label class="form-label" for="engine_number">Engine Number</label>
    <input type="text" class="form-control" id="engine_number" name="engine_number" value="{{ old('engine_number', $vehicle->engine_number ?? '') }}">
</div>
<div class="mb-3">
    <label class="form-label" for="chassis_number">Chassis Number</label>
    <input type="text" class="form-control" id="chassis_number" name="chassis_number" value="{{ old('chassis_number', $vehicle->chassis_number ?? '') }}">
</div>
<div class="mb-3">
    <label class="form-label" for="use_purpose">Use Purpose</label>
    <input type="text" class="form-control" id="use_purpose" name="use_purpose" value="{{ old('use_purpose', $vehicle->use_purpose ?? '') }}">
</div>
<div class="mb-3">
    <label class="form-label" for="use_company">Use Company</label>
    <input type="text" class="form-control" id="use_company" name="use_company" value="{{ old('use_company', $vehicle->use_company ?? '') }}">
</div>
<div class="mb-3">
    <label class="form-label" for="isRented">Is Rented?</label>
    <select class="form-select" id="isRented" name="isRented">
        <option value="0" {{ old('isRented', $vehicle->isRented ?? 0) == 0 ? 'selected' : '' }}>No</option>
        <option value="1" {{ old('isRented', $vehicle->isRented ?? 0) == 1 ? 'selected' : '' }}>Yes</option>
    </select>
</div>
<div class="mb-3">
    <label class="form-label" for="purchase_price">Purchase Price</label>
    <input type="number" step="0.01" class="form-control" id="purchase_price" name="purchase_price" value="{{ old('purchase_price', $vehicle->purchase_price ?? '') }}">
</div>
<div class="mb-3">
    <label class="form-label" for="purchase_date">Purchase Date</label>
    <input type="date" class="form-control" id="purchase_date" name="purchase_date" value="{{ old('purchase_date', $vehicle->purchase_date ?? '') }}">
</div>
<div class="mb-3">
    <label class="form-label" for="status">Status <span class="text-danger">*</span></label>
    <select class="form-select" id="status" name="status" required>
        <option value="active" {{ old('status', $vehicle->status ?? '') == 'active' ? 'selected' : '' }}>Active</option>
        <option value="inactive" {{ old('status', $vehicle->status ?? '') == 'inactive' ? 'selected' : '' }}>Inactive</option>
        <option value="scrapped" {{ old('status', $vehicle->status ?? '') == 'scrapped' ? 'selected' : '' }}>Scrapped</option>
    </select>
</div>
