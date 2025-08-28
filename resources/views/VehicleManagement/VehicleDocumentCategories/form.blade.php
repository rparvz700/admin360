<div class="mb-3">
    <label class="form-label" for="category_name">Category Name <span class="text-danger">*</span></label>
    <input type="text" class="form-control" id="category_name" name="category_name" value="{{ old('category_name', $category->category_name ?? '') }}" required>
</div>
<div class="mb-3">
    <label class="form-label" for="description">Description</label>
    <textarea class="form-control" id="description" name="description">{{ old('description', $category->description ?? '') }}</textarea>
</div>
