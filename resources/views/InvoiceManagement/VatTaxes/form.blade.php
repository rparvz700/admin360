<div class="mb-3">
    <label class="form-label" for="type">Type <span class="text-danger">*</span></label>
    <input type="text" class="form-control @error('type') is-invalid @enderror" id="type" name="type" value="{{ old('type', $vatTax->type ?? '') }}" required>
    @error('type')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="row">
    <div class="col-md-6">
        <div class="mb-3">
            <label class="form-label" for="vat">VAT</label>
            <input type="number" step="0.01" min="0" max="999999.99" class="form-control @error('vat') is-invalid @enderror" id="vat" name="vat" value="{{ old('vat', $vatTax->vat ?? '') }}">
            @error('vat')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="mb-3">
            <label class="form-label" for="tax">TAX</label>
            <input type="number" step="0.01" min="0" max="999999.99" class="form-control @error('tax') is-invalid @enderror" id="tax" name="tax" value="{{ old('tax', $vatTax->tax ?? '') }}">
            @error('tax')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<div class="mb-3">
    <label class="form-label" for="status">Status <span class="text-danger">*</span></label>
    <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
        <option value="1" {{ (string) old('status', isset($vatTax) ? (int) $vatTax->status : 1) === '1' ? 'selected' : '' }}>Active</option>
        <option value="0" {{ (string) old('status', isset($vatTax) ? (int) $vatTax->status : 1) === '0' ? 'selected' : '' }}>Inactive</option>
    </select>
    @error('status')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
