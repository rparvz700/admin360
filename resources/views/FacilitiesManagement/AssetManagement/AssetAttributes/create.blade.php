@extends('Partials.app', ['activeMenu' => 'asset-attributes'])

@section('title')
    {{ env('APP_NAME') }}
@endsection

@section('page_title')
    Add Asset Attribute
@endsection

@section('content')
    <div class="content">
        <div class="block block-rounded">
            <div class="block-header block-header-default">
                <h3 class="block-title">Add Asset Attribute</h3>
                <a href="{{ route('asset-attributes.index') }}" class="btn btn-sm btn-secondary">Back to List</a>
            </div>
            <div class="block-content">
                <form action="{{ route('asset-attributes.store') }}" method="POST" autocomplete="off">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label" for="category_id">Category<span class="text-danger">*</span></label>
                        <select class="form-control" id="category_id" name="category_id" required>
                            <option value="">Select Category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>{{ $category->category_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="attribute_name">Name<span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="attribute_name" name="attribute_name" value="{{ old('attribute_name') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="attribute_type">Type<span class="text-danger">*</span></label>
                        <select class="form-control" id="attribute_type" name="attribute_type" required>
                            <option value="">Select Type</option>
                            <option value="string" {{ old('attribute_type') == 'string' ? 'selected' : '' }}>Text</option>
                            <option value="number" {{ old('attribute_type') == 'number' ? 'selected' : '' }}>Number</option>
                            <option value="date" {{ old('attribute_type') == 'date' ? 'selected' : '' }}>Date</option>
                            <option value="boolean" {{ old('attribute_type') == 'boolean' ? 'selected' : '' }}>Boolean</option>
                            <option value="select" {{ old('attribute_type') == 'select' ? 'selected' : '' }}>Select</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="options">Options <small>(comma separated, for select type only)</small></label>
                        <input type="text" class="form-control" id="options" name="options" value="{{ old('options') }}">
                    </div>
                    <button type="submit" class="btn btn-primary">Save</button>
                </form>
            </div>
        </div>
    </div>
@endsection
