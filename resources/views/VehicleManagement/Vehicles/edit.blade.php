@extends('Partials.app', ['activeMenu' => 'vehicles'])
@section('title') Edit Vehicle @endsection
@section('content')
<div class="content">
    <div class="block block-rounded">
        <div class="block-header block-header-default">
            <h3 class="block-title">Edit Vehicle</h3>
            <a href="{{ route('vehicles.index') }}" class="btn btn-secondary btn-sm float-end">Back to List</a>
        </div>
        <div class="block-content">
            <form action="{{ route('vehicles.update', $vehicle->id) }}" method="POST" autocomplete="off">
                @csrf
                @method('PUT')
                @include('VehicleManagement.Vehicles.form', ['vehicle' => $vehicle, 'vehicleTypes' => $vehicleTypes])
                <button type="submit" class="btn btn-primary">Update</button>
            </form>
        </div>
    </div>
</div>
@endsection
