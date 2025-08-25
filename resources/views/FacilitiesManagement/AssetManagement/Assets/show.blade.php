@extends('Partials.app', ['activeMenu' => 'assets'])

@section('title')
    {{ env('APP_NAME') }}
@endsection

@section('page_title')
    Asset Details
@endsection

@section('content')
    <div class="content">
        <div class="block block-rounded">
            <div class="block-header block-header-default">
                <h3 class="block-title">Asset Details</h3>
                <a href="{{ route('assets.index') }}" class="btn btn-sm btn-secondary">Back to List</a>
            </div>
            <div class="block-content">
                <table class="table table-bordered">
                    <tbody>
                        <tr><th>Asset Tag</th><td>{{ $asset->asset_tag }}</td></tr>
                        <tr><th>Asset Name</th><td>{{ $asset->asset_name }}</td></tr>
                        <tr><th>Category</th><td>{{ $asset->category ? $asset->category->category_name : '' }}</td></tr>
                        <tr><th>Brand</th><td>{{ $asset->brand }}</td></tr>
                        <tr><th>Model</th><td>{{ $asset->model }}</td></tr>
                        <tr><th>Serial Number</th><td>{{ $asset->serial_number }}</td></tr>
                        <tr><th>Purchase Date</th><td>{{ $asset->purchase_date }}</td></tr>
                        <tr><th>Warranty Expiry</th><td>{{ $asset->warranty_expiry }}</td></tr>
                        <tr><th>Floor</th><td>{{ $asset->floor ? ($asset->floor->building ? $asset->floor->building->site_name : 'Building') . ', ' . $asset->floor->floor_label : '' }}</td></tr>
                        <tr><th>Location Within Floor</th><td>{{ $asset->location_within_floor }}</td></tr>
                        <tr><th>Parent Asset</th><td>{{ $asset->parent ? $asset->parent->asset_tag . ' - ' . $asset->parent->asset_name : '' }}</td></tr>
                        <tr><th>Status</th><td>{{ ucfirst($asset->status) }}</td></tr>
                    </tbody>
                </table>
                <h5 class="mt-4">Attribute Values</h5>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Attribute</th>
                            <th>Type</th>
                            <th>Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($asset->attributeValues as $attrValue)
                            <tr>
                                <td>{{ $attrValue->attribute->attribute_name ?? '' }}</td>
                                <td>{{ $attrValue->attribute->attribute_type ?? '' }}</td>
                                <td>
                                    @if(($attrValue->attribute->attribute_type ?? '') === 'boolean')
                                        {{ $attrValue->value == '1' ? 'Yes' : 'No' }}
                                    @else
                                        {{ $attrValue->value }}
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
