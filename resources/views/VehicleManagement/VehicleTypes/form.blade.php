<div class="mb-3">
    <label class="form-label" for="type_name">Type Name <span class="text-danger">*</span></label>
    <input type="text" class="form-control" id="type_name" name="type_name" value="{{ old('type_name', $type->type_name ?? '') }}" required>
</div>
