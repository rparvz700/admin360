@extends('Partials.app', ['activeMenu' => 'vehicle-document-attributes'])
@section('title') Vehicle Document Attribute Details @endsection
@section('content')
<div class="content">
    <div class="block block-rounded">
        <div class="block-header block-header-default">
            <h3 class="block-title">Vehicle Document Attribute Details</h3>
            <a href="{{ route('vehicle-document-attributes.index') }}" class="btn btn-secondary btn-sm float-end">Back to List</a>
        </div>
        <div class="block-content">
            <table class="table table-bordered">
                <tbody>
                    <tr><th>ID</th><td>{{ $attribute->id }}</td></tr>
                    <tr><th>Category</th><td>{{ $attribute->category->category_name ?? '' }}</td></tr>
                    <tr><th>Attribute Name</th><td>{{ $attribute->attribute_name }}</td></tr>
                    <tr><th>Type</th><td>{{ $attribute->attribute_type }}</td></tr>
                    <tr><th>Options</th><td>{{ $attribute->options }}</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
