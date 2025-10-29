<div class="mb-3">
    <label class="form-label" for="category_id">Category <span class="text-danger">*</span></label>
    <select class="form-select" id="category_id" name="category_id" required>
        <option value="">Select Category</option>
        @foreach($categories as $cat)
            <option value="{{ $cat->id }}" {{ old('category_id', $attribute->category_id ?? '') == $cat->id ? 'selected' : '' }}>{{ $cat->category_name }}</option>
        @endforeach
    </select>
</div>
<div class="mb-3">
    <label class="form-label" for="attribute_name">Attribute Name <span class="text-danger">*</span></label>
    <input type="text" class="form-control" id="attribute_name" name="attribute_name" value="{{ old('attribute_name', $attribute->attribute_name ?? '') }}" required>
</div>
<div class="mb-3">
    <label class="form-label" for="attribute_type">Type <span class="text-danger">*</span></label>
    <select class="form-select" id="attribute_type" name="attribute_type" required>
        <option value="">Select Type</option>
        <option value="string" {{ old('attribute_type', $attribute->attribute_type ?? '') == 'string' ? 'selected' : '' }}>String</option>
        <option value="number" {{ old('attribute_type', $attribute->attribute_type ?? '') == 'number' ? 'selected' : '' }}>Number</option>
        <option value="date" {{ old('attribute_type', $attribute->attribute_type ?? '') == 'date' ? 'selected' : '' }}>Date</option>
        <option value="boolean" {{ old('attribute_type', $attribute->attribute_type ?? '') == 'boolean' ? 'selected' : '' }}>Boolean</option>
        <option value="select" {{ old('attribute_type', $attribute->attribute_type ?? '') == 'select' ? 'selected' : '' }}>Select</option>
    </select>
</div>
<div class="mb-3">
    <label class="form-label" for="options">Options (for Select type, comma separated or JSON)</label>
    <textarea class="form-control" id="options" name="options">{{ old('options', $attribute->options ?? '') }}</textarea>
</div>
