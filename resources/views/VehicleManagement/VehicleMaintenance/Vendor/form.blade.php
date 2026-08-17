<div class="mb-3">
    <label class="form-label" for="name">Vendor Name <span class="text-danger">*</span></label>
    <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $vendor->name ?? '') }}" required>
</div>

<div class="mb-3">
    <label class="form-label" for="vendor_type">Vendor Type</label>
    <select class="form-select" id="vendor_type" name="vendor_type">
        <option value="">Select Type</option>
        <option value="workshop" {{ old('vendor_type', $vendor->vendor_type ?? '') == 'workshop' ? 'selected' : '' }}>Workshop</option>
        <option value="parts_supplier" {{ old('vendor_type', $vendor->vendor_type ?? '') == 'parts_supplier' ? 'selected' : '' }}>Parts Supplier</option>
        <option value="both" {{ old('vendor_type', $vendor->vendor_type ?? '') == 'both' ? 'selected' : '' }}>Workshop & Parts</option>
    </select>
</div>

<div class="mb-3">
    <label class="form-label" for="contact_person">Contact Person</label>
    <input type="text" class="form-control" id="contact_person" name="contact_person" value="{{ old('contact_person', $vendor->contact_person ?? '') }}">
</div>

<div class="mb-3">
    <label class="form-label" for="phone">Phone <span class="text-danger">*</span></label>
    <input type="text" class="form-control" id="phone" name="phone" value="{{ old('phone', $vendor->phone ?? '') }}" required>
</div>

<div class="mb-3">
    <label class="form-label" for="email">Email</label>
    <input type="email" class="form-control" id="email" name="email" value="{{ old('email', $vendor->email ?? '') }}">
</div>

<div class="mb-3">
    <label class="form-label" for="address">Address</label>
    <textarea class="form-control" id="address" name="address" rows="3">{{ old('address', $vendor->address ?? '') }}</textarea>
</div>

<div class="mb-3">
    <label class="form-label">Services Offered</label>
    @php
        $servicesOffered = old('services_offered', $vendor->services_offered ?? []);
    @endphp
    <div class="form-check">
        <input class="form-check-input" type="checkbox" name="services_offered[]" value="oil_change" 
               id="service1" {{ in_array('oil_change', $servicesOffered) ? 'checked' : '' }}>
        <label class="form-check-label" for="service1">Oil Change</label>
    </div>
    <div class="form-check">
        <input class="form-check-input" type="checkbox" name="services_offered[]" value="tyre_service" 
               id="service2" {{ in_array('tyre_service', $servicesOffered) ? 'checked' : '' }}>
        <label class="form-check-label" for="service2">Tyre Service</label>
    </div>
    <div class="form-check">
        <input class="form-check-input" type="checkbox" name="services_offered[]" value="engine_repair" 
               id="service3" {{ in_array('engine_repair', $servicesOffered) ? 'checked' : '' }}>
        <label class="form-check-label" for="service3">Engine Repair</label>
    </div>
    <div class="form-check">
        <input class="form-check-input" type="checkbox" name="services_offered[]" value="body_work" 
               id="service4" {{ in_array('body_work', $servicesOffered) ? 'checked' : '' }}>
        <label class="form-check-label" for="service4">Body Work</label>
    </div>
</div>

<div class="mb-3">
    <label class="form-label" for="rating">Rating (0-5)</label>
    <input type="number" step="0.1" min="0" max="5" class="form-control" id="rating" name="rating" value="{{ old('rating', $vendor->rating ?? '') }}">
</div>

<div class="mb-3">
    <label class="form-label" for="is_active">Status <span class="text-danger">*</span></label>
    <select class="form-select" id="is_active" name="is_active" required>
        <option value="1" {{ old('is_active', $vendor->is_active ?? 1) == 1 ? 'selected' : '' }}>Active</option>
        <option value="0" {{ old('is_active', $vendor->is_active ?? 1) == 0 ? 'selected' : '' }}>Inactive</option>
    </select>
</div>