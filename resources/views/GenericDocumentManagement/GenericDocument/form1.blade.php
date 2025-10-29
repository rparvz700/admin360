<!-- Step 1: Choose Entity Type -->
<div class="mb-3">
    <label class="form-label" for="documentable_type">Documentable<span class="text-danger">*</span></label>
    <select name="documentable_type" id="documentable_type" class="form-control"  required>
        <option value="">-- Select Document Owner Type --</option>
        @foreach($documentableTypes as $label => $class)
            <option value="{{ $label }}">{{ ucfirst($label) }}</option>
        @endforeach
    </select>
</div>
<!-- Step 2: Choose Specific Record -->
<div class="mb-3" id="documentable-id-wrapper">
    <label class="form-label" for="documentable_id">Select Record <span class="text-danger">*</span></label>
    <select class="form-select select2" id="documentable_id" name="documentable_id" style="width:100%;" required>
        <option value="">Select record</option>
        <!-- Options will be loaded dynamically -->
    </select>
</div>
<div class="mb-3">
    <label class="form-label" for="category_id">Category<span class="text-danger">*</span></label>
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
