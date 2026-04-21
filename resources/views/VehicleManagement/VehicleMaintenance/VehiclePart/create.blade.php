@extends('Partials.app', ['activeMenu' => 'maintenance-parts'])
@section('title') Add Vehicle Part @endsection
@section('content')
<div class="content">
    <div class="block block-rounded">
        <div class="block-header block-header-default">
            <h3 class="block-title">Add Vehicle Part</h3>
            <a href="{{ route('maintenance.parts.index') }}" class="btn btn-secondary btn-sm float-end">Back to List</a>
        </div>
        <div class="block-content">
            <form action="{{ route('maintenance.parts.store') }}" method="POST" autocomplete="off">
                @csrf
                @include('VehicleManagement.VehicleMaintenance.VehiclePart.form', ['part' => null])
                <button type="submit" class="btn btn-primary">Save</button>
            </form>
        </div>
    </div>
</div>
@endsection