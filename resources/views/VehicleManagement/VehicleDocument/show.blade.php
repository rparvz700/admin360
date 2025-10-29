@extends('Partials.app', ['activeMenu' => 'vehicle-documents'])

@section('title')
    {{ config('app.name') }} 
@endsection

@section('page_title')
    Vehicle Document Details
@endsection

@section('content')
    <div class="content">
        <div class="block block-rounded">
            <div class="block-header block-header-default">
                <h3 class="block-title">Vehicle Document Details</h3>
                <a href="{{ route('vehicle-documents.index') }}" class="btn btn-sm btn-secondary">Back to List</a>
            </div>
            <div class="block-content">
                <table class="table table-bordered">
                    <tbody>
                        <tr><th>Vehicle</th><td>{{ $doc->vehicle->registration_number ?? '' }}</td></tr>
                        <tr><th>Category</th><td>{{ $doc->category->category_name ?? '' }}</td></tr>
                        <tr><th>Issue Date</th><td>{{ $doc->issue_date }}</td></tr>
                        <tr><th>Expiry Date</th><td>{{ $doc->expiry_date }}</td></tr>
                        <tr><th>File</th><td>
                            @if($doc->file_path)
                                <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank">View File</a>
                            @endif
                        </td></tr>
                    </tbody>
                </table>
                <h5 class="mt-4">Custom Fields</h5>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Field Name</th>
                            <th>Type</th>
                            <th>Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($doc->attributeValues as $attrValue)
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
