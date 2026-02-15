@extends('Partials.app', ['activeMenu' => 'operational-logs'])
@section('title') Add Operational Log @endsection
@section('content')
<div class="content">
    <div class="block block-rounded">
        <div class="block-header block-header-default">
            <h3 class="block-title">Add Operational Log</h3>
            <a href="{{ route('maintenance.operational-logs.index') }}" class="btn btn-secondary btn-sm float-end">Back to List</a>
        </div>
        <div class="block-content">
            <form action="{{ route('maintenance.operational-logs.store') }}" method="POST" autocomplete="off">
                @csrf
                @include('VehicleManagement.VehicleMaintenance.VehicleOperationalLog.form', ['log' => null, 'vehicles' => $vehicles, 'users' => $users])
                <button type="submit" class="btn btn-primary">Save</button>
            </form>
        </div>
    </div>
</div>
@endsection