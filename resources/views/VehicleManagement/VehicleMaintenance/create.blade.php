@extends('Partials.app', ['activeMenu' => 'maintenance'])
@section('title') Add Maintenance Record @endsection
@section('content')
<div class="content">
    <div class="block block-rounded">
        <div class="block-header block-header-default">
            <h3 class="block-title">Add Maintenance Record</h3>
            <a href="{{ route('maintenance.maintenances.index') }}" class="btn btn-secondary btn-sm float-end">Back to List</a>
        </div>
        <div class="block-content">
            <form action="{{ route('maintenance.maintenances.store') }}" method="POST" autocomplete="off">
                @csrf
                @include('VehicleManagement.VehicleMaintenance.form', ['maintenance' => null, 'vehicles' => $vehicles, 'vendors' => $vendors, 'parts' => $parts])
                <button type="submit" class="btn btn-primary">Save</button>
            </form>
        </div>
    </div>
</div>
@endsection