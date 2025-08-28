@extends('Partials.app', ['activeMenu' => 'vehicle-document-categories'])
@section('title') Edit Vehicle Document Category @endsection
@section('content')
<div class="content">
    <div class="block block-rounded">
        <div class="block-header block-header-default">
            <h3 class="block-title">Edit Vehicle Document Category</h3>
            <a href="{{ route('vehicle-document-categories.index') }}" class="btn btn-secondary btn-sm float-end">Back to List</a>
        </div>
        <div class="block-content">
            <form action="{{ route('vehicle-document-categories.update', $category->id) }}" method="POST" autocomplete="off">
                @csrf
                @method('PUT')
                @include('VehicleManagement.VehicleDocumentCategories.form', ['category' => $category])
                <button type="submit" class="btn btn-primary">Update</button>
            </form>
        </div>
    </div>
</div>
@endsection
