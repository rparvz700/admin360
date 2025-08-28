@extends('Partials.app', ['activeMenu' => 'vehicles'])
@section('title') Add Vehicle @endsection
@section('content')
<div class="content">
    <div class="block block-rounded">
        <div class="block-header block-header-default">
            <h3 class="block-title">Add Vehicle</h3>
            <a href="{{ route('vehicles.index') }}" class="btn btn-secondary btn-sm float-end">Back to List</a>
        </div>
        <div class="block-content">
            <form action="{{ route('vehicles.store') }}" method="POST" autocomplete="off">
                @csrf
                @include('VehicleManagement.Vehicles.form', ['vehicle' => null, 'vehicleTypes' => $vehicleTypes])
                <button type="submit" class="btn btn-primary">Save</button>
            </form>
        </div>
    </div>
</div>
@endsection
