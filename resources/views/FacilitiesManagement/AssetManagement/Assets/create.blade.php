@extends('Partials.app', ['activeMenu' => 'assets'])

@section('title')
    {{ env('APP_NAME') }}
@endsection

@section('page_title')
    Add Asset
@endsection

@section('content')
    <div class="content">
        <div class="block block-rounded">
            <div class="block-header block-header-default">
                <h3 class="block-title">Add Asset</h3>
                <a href="{{ route('assets.index') }}" class="btn btn-sm btn-secondary">Back to List</a>
            </div>
            <div class="block-content">
                <form action="{{ route('assets.store') }}" method="POST" autocomplete="off">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label" for="asset_tag">Asset Tag<span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="asset_tag" name="asset_tag" value="{{ old('asset_tag') }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="asset_name">Asset Name<span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="asset_name" name="asset_name" value="{{ old('asset_name') }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="category_id">Category<span class="text-danger">*</span></label>
                                <select class="form-control select2" id="category_id" name="category_id" required>
                                    <option value="">Select Category</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>{{ $category->category_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="brand">Brand</label>
                                <input type="text" class="form-control" id="brand" name="brand" value="{{ old('brand') }}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="model">Model</label>
                                <input type="text" class="form-control" id="model" name="model" value="{{ old('model') }}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="serial_number">Serial Number</label>
                                <input type="text" class="form-control" id="serial_number" name="serial_number" value="{{ old('serial_number') }}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="purchase_date">Purchase Date</label>
                                <input type="date" class="form-control" id="purchase_date" name="purchase_date" value="{{ old('purchase_date') }}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="warranty_expiry">Warranty Expiry</label>
                                <input type="date" class="form-control" id="warranty_expiry" name="warranty_expiry" value="{{ old('warranty_expiry') }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label" for="floor_id">Floor</label>
                                <select class="form-control select2" id="floor_id" name="floor_id">
                                    <option value="">Select Floor</option>
                                    @foreach($floors as $floor)
                                        <option value="{{ $floor->id }}" {{ old('floor_id') == $floor->id ? 'selected' : '' }}>
                                            {{ ($floor->building && $floor->building->site_name ? $floor->building->site_name : 'Building') }}, {{ $floor->floor_label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="location_within_floor">Location Within Floor</label>
                                <input type="text" class="form-control" id="location_within_floor" name="location_within_floor" value="{{ old('location_within_floor') }}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="parent_id">Parent Asset</label>
                                <select class="form-control select2" id="parent_id" name="parent_id">
                                    <option value="">Select Parent Asset</option>
                                    @foreach($assets as $parentAsset)
                                        <option value="{{ $parentAsset->id }}" {{ old('parent_id') == $parentAsset->id ? 'selected' : '' }}>{{ $parentAsset->asset_tag }} - {{ $parentAsset->asset_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="status">Status<span class="text-danger">*</span></label>
                                <select class="form-control" id="status" name="status" required>
                                    <option value="">Select Status</option>
                                    <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>
                            <hr>
                            <h5>Attribute Values</h5>
                            <div id="attribute-fields">
                                @if(old('category_id'))
                                    @php
                                        $categoryAttributes = $attributes->where('category_id', old('category_id'));
                                    @endphp
                                @elseif(isset($categories[0]))
                                    @php
                                        $categoryAttributes = $attributes->where('category_id', $categories[0]->id);
                                    @endphp
                                @else
                                    @php $categoryAttributes = collect(); @endphp
                                @endif
                                @foreach($categoryAttributes as $attribute)
                                    <div class="mb-3">
                                        <label class="form-label" for="attribute_{{ $attribute->id }}">{{ $attribute->attribute_name }}
                                            @if($attribute->attribute_type == 'select')<small>(Select)</small>@endif
                                        </label>
                                        @if($attribute->attribute_type == 'string')
                                            <input type="text" class="form-control" id="attribute_{{ $attribute->id }}" name="attributes[{{ $attribute->id }}]" value="{{ old('attributes.'.$attribute->id) }}">
                                        @elseif($attribute->attribute_type == 'number')
                                            <input type="number" class="form-control" id="attribute_{{ $attribute->id }}" name="attributes[{{ $attribute->id }}]" value="{{ old('attributes.'.$attribute->id) }}">
                                        @elseif($attribute->attribute_type == 'date')
                                            <input type="date" class="form-control" id="attribute_{{ $attribute->id }}" name="attributes[{{ $attribute->id }}]" value="{{ old('attributes.'.$attribute->id) }}">
                                        @elseif($attribute->attribute_type == 'boolean')
                                            <div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="attributes[{{ $attribute->id }}]" id="attribute_{{ $attribute->id }}_yes" value="1" {{ old('attributes.'.$attribute->id) == '1' ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="attribute_{{ $attribute->id }}_yes">Yes</label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="attributes[{{ $attribute->id }}]" id="attribute_{{ $attribute->id }}_no" value="0" {{ old('attributes.'.$attribute->id) == '0' ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="attribute_{{ $attribute->id }}_no">No</label>
                                                </div>
                                            </div>
                                        @elseif($attribute->attribute_type == 'select')
                                            <select class="form-control select2" id="attribute_{{ $attribute->id }}" name="attributes[{{ $attribute->id }}]">
                                                <option value="">Select</option>
                                                @foreach($attribute->options ?? [] as $option)
                                                    <option value="{{ $option }}" {{ old('attributes.'.$attribute->id) == $option ? 'selected' : '' }}>{{ $option }}</option>
                                                @endforeach
                                            </select>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Save</button>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <link href="{{ asset('js/plugins/select2/css/select2.min.css') }}" rel="stylesheet" />
    <script src="{{ asset('js/plugins/select2/js/select2.full.min.js') }}"></script>
    <script>
        window.allAttributes = @json($attributes);
    </script>
    <script src="{{ asset('js/asset-attribute-fields.js') }}"></script>
@endsection
