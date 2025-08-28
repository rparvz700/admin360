<div class="mb-3">
    <label class="form-label" for="vehicle_id">Vehicle <span class="text-danger">*</span></label>
    <select class="form-select select2" id="vehicle_id" name="vehicle_id" required>
        <option value="">Select Vehicle</option>
        @foreach($vehicles as $vehicle)
            <option value="{{ $vehicle->id }}" {{ old('vehicle_id', $doc->vehicle_id ?? '') == $vehicle->id ? 'selected' : '' }}>{{ $vehicle->registration_number }}</option>
        @endforeach
    </select>
</div>
<div class="mb-3">
    <label class="form-label" for="category_id">Category <span class="text-danger">*</span></label>
    <select class="form-select select2" id="category_id" name="category_id" required>
        <option value="">Select Category</option>
        @foreach($categories as $cat)
            <option value="{{ $cat->id }}" {{ old('category_id', $doc->category_id ?? '') == $cat->id ? 'selected' : '' }}>{{ $cat->category_name }}</option>
        @endforeach
    </select>
</div>
<div class="mb-3">
    <label class="form-label" for="issue_date">Issue Date <span class="text-danger">*</span></label>
    <input type="date" class="form-control" id="issue_date" name="issue_date" value="{{ old('issue_date', $doc->issue_date ?? '') }}" required>
</div>
<div class="mb-3">
    <label class="form-label" for="expiry_date">Expiry Date</label>
    <input type="date" class="form-control" id="expiry_date" name="expiry_date" value="{{ old('expiry_date', $doc->expiry_date ?? '') }}">
</div>

<hr>
<h5>Custom Fields</h5>
<div id="attribute-fields"></div>
