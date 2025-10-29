@extends('Partials.app', ['activeMenu' => 'vehicle-document-attributes'])
@section('title') Edit Vehicle Document Attribute @endsection
@section('content')
<div class="content">
    <div class="block block-rounded">
        <div class="block-header block-header-default">
            <h3 class="block-title">Edit Vehicle Document Attribute</h3>
            <a href="{{ route('vehicle-document-attributes.index') }}" class="btn btn-secondary btn-sm float-end">Back to List</a>
        </div>
        <div class="block-content">
            <form action="{{ route('vehicle-document-attributes.update', $attribute->id) }}" method="POST" autocomplete="off">
                @csrf
                @method('PUT')
                @include('VehicleManagement.VehicleDocumentAttributes.form', ['attribute' => $attribute, 'categories' => $categories])
                <button type="submit" class="btn btn-primary">Update</button>
            </form>
        </div>
    </div>
</div>
@endsection
