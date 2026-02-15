@extends('Partials.app', ['activeMenu' => 'maintenance'])
@section('title') Edit Maintenance Record @endsection
@section('content')
<div class="content">
    <div class="block block-rounded">
        <div class="block-header block-header-default">
            <h3 class="block-title">Edit Maintenance Record</h3>
            <a href="{{ route('maintenance.maintenances.index') }}" class="btn btn-secondary btn-sm float-end">Back to List</a>
        </div>
        <div class="block-content">
            <form action="{{ route('maintenance.maintenances.update', $maintenance->id) }}" method="POST" autocomplete="off">
                @csrf
                @method('PUT')
                @include('VehicleManagement.VehicleMaintenance.form', ['maintenance' => $maintenance, 'vehicles' => $vehicles, 'vendors' => $vendors, 'parts' => $parts])
                <button type="submit" class="btn btn-primary">Update</button>
            </form>
        </div>
    </div>
</div>
@endsection