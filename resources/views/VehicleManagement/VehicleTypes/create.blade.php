@extends('Partials.app', ['activeMenu' => 'vehicle-types'])
@section('title') Add Vehicle Type @endsection
@section('content')
<div class="content">
    <div class="block block-rounded">
        <div class="block-header block-header-default">
            <h3 class="block-title">Add Vehicle Type</h3>
            <a href="{{ route('vehicle-types.index') }}" class="btn btn-secondary btn-sm float-end">Back to List</a>
        </div>
        <div class="block-content">
            <form action="{{ route('vehicle-types.store') }}" method="POST" autocomplete="off">
                @csrf
                @include('VehicleManagement.VehicleTypes.form', ['type' => null])
                <button type="submit" class="btn btn-primary">Save</button>
            </form>
        </div>
    </div>
</div>
@endsection
