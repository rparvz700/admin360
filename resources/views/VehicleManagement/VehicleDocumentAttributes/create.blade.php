@extends('Partials.app', ['activeMenu' => 'vehicle-document-attributes'])
@section('title') Add Vehicle Document Attribute @endsection
@section('content')
<div class="content">
    <div class="block block-rounded">
        <div class="block-header block-header-default">
            <h3 class="block-title">Add Vehicle Document Attribute</h3>
            <a href="{{ route('vehicle-document-attributes.index') }}" class="btn btn-secondary btn-sm float-end">Back to List</a>
        </div>
        <div class="block-content">
            <form action="{{ route('vehicle-document-attributes.store') }}" method="POST" autocomplete="off">
                @csrf
                @include('VehicleManagement.VehicleDocumentAttributes.form', ['attribute' => null, 'categories' => $categories])
                <button type="submit" class="btn btn-primary">Save</button>
            </form>
        </div>
    </div>
</div>
@endsection
