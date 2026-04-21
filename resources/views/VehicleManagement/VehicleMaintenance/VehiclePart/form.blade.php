<div class="mb-3">
    <label class="form-label" for="part_name">Part Name <span class="text-danger">*</span></label>
    <input type="text" class="form-control" id="part_name" name="part_name" value="{{ old('part_name', $part->part_name ?? '') }}" required>
</div>

<div class="mb-3">
    <label class="form-label" for="category">Category <span class="text-danger">*</span></label>
    <select class="form-select" id="category" name="category" required>
        <option value="">Select Category</option>
        <option value="engine" {{ old('category', $part->category ?? '') == 'engine' ? 'selected' : '' }}>Engine</option>
        <option value="tyre" {{ old('category', $part->category ?? '') == 'tyre' ? 'selected' : '' }}>Tyre</option>
        <option value="battery" {{ old('category', $part->category ?? '') == 'battery' ? 'selected' : '' }}>Battery</option>
        <option value="oil" {{ old('category', $part->category ?? '') == 'oil' ? 'selected' : '' }}>Oil / Lubricant</option>
        <option value="brake" {{ old('category', $part->category ?? '') == 'brake' ? 'selected' : '' }}>Brake System</option>
        <option value="body" {{ old('category', $part->category ?? '') == 'body' ? 'selected' : '' }}>Body / Cover</option>
        <option value="transmission" {{ old('category', $part->category ?? '') == 'transmission' ? 'selected' : '' }}>Transmission / Gear</option>
        <option value="electrical" {{ old('category', $part->category ?? '') == 'electrical' ? 'selected' : '' }}>Electrical</option>
        <option value="other" {{ old('category', $part->category ?? '') == 'other' ? 'selected' : '' }}>Other</option>
    </select>
</div>

<div class="mb-3">
    <label class="form-label" for="description">Description</label>
    <textarea class="form-control" id="description" name="description" rows="3">{{ old('description', $part->description ?? '') }}</textarea>
</div>

<div class="mb-3">
    <label class="form-label" for="typical_lifespan_km">Typical Lifespan (KM)</label>
    <input type="number" class="form-control" id="typical_lifespan_km" name="typical_lifespan_km" value="{{ old('typical_lifespan_km', $part->typical_lifespan_km ?? '') }}" min="0">
    <small class="form-text text-muted">Expected lifespan in kilometers</small>
</div>

<div class="mb-3">
    <label class="form-label" for="typical_lifespan_months">Typical Lifespan (Months)</label>
    <input type="number" class="form-control" id="typical_lifespan_months" name="typical_lifespan_months" value="{{ old('typical_lifespan_months', $part->typical_lifespan_months ?? '') }}" min="0">
    <small class="form-text text-muted">Expected lifespan in months</small>
</div>

<div class="mb-3">
    <label class="form-label" for="is_active">Status <span class="text-danger">*</span></label>
    <select class="form-select" id="is_active" name="is_active" required>
        <option value="1" {{ old('is_active', $part->is_active ?? 1) == 1 ? 'selected' : '' }}>Active</option>
        <option value="0" {{ old('is_active', $part->is_active ?? 1) == 0 ? 'selected' : '' }}>Inactive</option>
    </select>
</div>