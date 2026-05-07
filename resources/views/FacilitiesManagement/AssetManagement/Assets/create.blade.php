@extends('Partials.app', ['activeMenu' => 'assets'])

@section('title')
    {{ config('app.name') }}
@endsection

@section('page_title')
    Add Asset
@endsection

@section('styles')
    <link rel="stylesheet" href="{{ asset('js/plugins/select2/css/select2.min.css') }}">
@endsection

@section('content')
    <div class="content">
        <div class="asset-page-header">
            <div>
                <div class="asset-eyebrow">Facilities Management</div>
                <h2>Create Asset</h2>
                <p>Register an asset with category, ownership, placement, and category-specific attributes.</p>
            </div>
            <a href="{{ route('assets.index') }}" class="btn btn-alt-secondary">
                <i class="fa fa-arrow-left me-1"></i> Back
            </a>
        </div>

        <div class="block block-rounded asset-shell">
            <div class="block-header block-header-default asset-block-header">
                <div>
                    <h3 class="block-title">Asset Profile</h3>
                    <div class="text-muted fs-sm">Fields marked with <span class="text-danger">*</span> are required.</div>
                </div>
            </div>
            <div class="block-content fs-sm data-content">
                <form action="{{ route('assets.store') }}" method="POST" autocomplete="off">
                    @csrf
                    <div class="row g-4">
                        <div class="col-xl-6">
                            <div class="asset-form-section">
                                <div class="asset-section-heading">
                                    <span class="asset-section-icon"><i class="fa fa-barcode"></i></span>
                                    <div>
                                        <h4>Asset Identity</h4>
                                        <p>Core identification, category, manufacturer, and lifecycle dates.</p>
                                    </div>
                                </div>
                            <div class="mb-3">
                                <label class="form-label" for="asset_tag">Asset Tag / Unique Code<span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="asset_tag" name="asset_tag"
                                    value="{{ old('asset_tag') }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="asset_name">Asset Description<span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="asset_name" name="asset_name"
                                    value="{{ old('asset_name') }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="category_id">Category<span
                                        class="text-danger">*</span></label>
                                <select class="form-select js-select2" id="category_id" name="category_id" required>
                                    <option value="">Select Category</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}"
                                            {{ old('category_id', $preselectedCategoryId) == $category->id ? 'selected' : '' }}>
                                            {{ $category->category_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="brand">Brand</label>
                                <input type="text" class="form-control" id="brand" name="brand"
                                    value="{{ old('brand') }}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="model">Model</label>
                                <input type="text" class="form-control" id="model" name="model"
                                    value="{{ old('model') }}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="serial_number">Serial Number</label>
                                <input type="text" class="form-control" id="serial_number" name="serial_number"
                                    value="{{ old('serial_number') }}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="purchase_date">Purchase Date</label>
                                <input type="date" class="form-control" id="purchase_date" name="purchase_date"
                                    value="{{ old('purchase_date') }}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="warranty_expiry">Warranty Expiry</label>
                                <input type="date" class="form-control" id="warranty_expiry" name="warranty_expiry"
                                    value="{{ old('warranty_expiry') }}">
                            </div>
                            </div>
                        </div>
                        <div class="col-xl-6">
                            <div class="asset-form-section">
                                <div class="asset-section-heading">
                                    <span class="asset-section-icon"><i class="fa fa-map-marker-alt"></i></span>
                                    <div>
                                        <h4>Assignment and Placement</h4>
                                        <p>Place the asset on a floor, project, parent asset, and operational status.</p>
                                    </div>
                                </div>
                            <div class="mb-3">
                                <label class="form-label" for="floor_id">Floor</label>
                                <select class="form-select js-select2" id="floor_id" name="floor_id">
                                    <option value="">Select Floor</option>
                                    @foreach ($floors as $floor)
                                        <option value="{{ $floor->id }}"
                                            {{ old('floor_id') == $floor->id ? 'selected' : '' }}>
                                            {{ $floor->building && $floor->building->site_name ? $floor->building->site_name : 'Building' }},
                                            {{ $floor->floor_label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="location_within_floor">Location Within Floor</label>
                                <input type="text" class="form-control" id="location_within_floor"
                                    name="location_within_floor" value="{{ old('location_within_floor') }}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="parent_id">Parent Asset</label>
                                <select class="form-select js-select2" id="parent_id" name="parent_id">
                                    <option value="">Select Parent Asset</option>
                                    @foreach ($assets as $parentAsset)
                                        <option value="{{ $parentAsset->id }}"
                                            {{ old('parent_id') == $parentAsset->id ? 'selected' : '' }}>
                                            {{ $parentAsset->asset_tag }} - {{ $parentAsset->asset_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="status">Status<span class="text-danger">*</span></label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="">Select Status</option>
                                    <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active
                                    </option>
                                    <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive
                                    </option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="project">Project</label>
                                <select class="form-select" id="project" name="project_id" required>
                                    <option value="">Select project</option>
                                    @foreach ($projects as $project)
                                        <option value="{{ $project->id }}">{{ $project->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="asset-attribute-panel mt-4">
                            <h5><i class="fa fa-sliders-h me-1"></i> Attribute Values</h5>
                            <div id="attribute-fields">
                                @if (old('category_id'))
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
                                @foreach ($categoryAttributes as $attribute)
                                    <div class="mb-3">
                                        <label class="form-label"
                                            for="attribute_{{ $attribute->id }}">{{ $attribute->attribute_name }}
                                            @if ($attribute->attribute_type == 'select')
                                                <small>(Select)</small>
                                            @endif
                                        </label>
                                        @if ($attribute->attribute_type == 'string')
                                            <input type="text" class="form-control"
                                                id="attribute_{{ $attribute->id }}"
                                                name="attributes[{{ $attribute->id }}]"
                                                value="{{ old('attributes.' . $attribute->id) }}">
                                        @elseif($attribute->attribute_type == 'number')
                                            <input type="number" class="form-control"
                                                id="attribute_{{ $attribute->id }}"
                                                name="attributes[{{ $attribute->id }}]"
                                                value="{{ old('attributes.' . $attribute->id) }}">
                                        @elseif($attribute->attribute_type == 'date')
                                            <input type="date" class="form-control"
                                                id="attribute_{{ $attribute->id }}"
                                                name="attributes[{{ $attribute->id }}]"
                                                value="{{ old('attributes.' . $attribute->id) }}">
                                        @elseif($attribute->attribute_type == 'boolean')
                                            <div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio"
                                                        name="attributes[{{ $attribute->id }}]"
                                                        id="attribute_{{ $attribute->id }}_yes" value="1"
                                                        {{ old('attributes.' . $attribute->id) == '1' ? 'checked' : '' }}>
                                                    <label class="form-check-label"
                                                        for="attribute_{{ $attribute->id }}_yes">Yes</label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio"
                                                        name="attributes[{{ $attribute->id }}]"
                                                        id="attribute_{{ $attribute->id }}_no" value="0"
                                                        {{ old('attributes.' . $attribute->id) == '0' ? 'checked' : '' }}>
                                                    <label class="form-check-label"
                                                        for="attribute_{{ $attribute->id }}_no">No</label>
                                                </div>
                                            </div>
                                        @elseif($attribute->attribute_type == 'select')
                                            <select class="form-select select2" id="attribute_{{ $attribute->id }}"
                                                name="attributes[{{ $attribute->id }}]">
                                                <option value="">Select</option>
                                                @foreach ($attribute->options ?? [] as $option)
                                                    <option value="{{ $option }}"
                                                        {{ old('attributes.' . $attribute->id) == $option ? 'selected' : '' }}>
                                                        {{ $option }}</option>
                                                @endforeach
                                            </select>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                            </div>
                            </div>
                        </div>
                    </div>
                    <div class="asset-action-bar">
                        <a href="{{ route('assets.index') }}" class="btn btn-alt-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-check me-1"></i> Save Asset
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('js/plugins/select2/js/select2.full.min.js') }}"></script>
    <script>
        One.helpersOnLoad(['jq-select2']);
        window.allAttributes = @json($attributes);
    </script>
    <script src="{{ asset('js/asset-attribute-fields.js') }}"></script>
@endsection
