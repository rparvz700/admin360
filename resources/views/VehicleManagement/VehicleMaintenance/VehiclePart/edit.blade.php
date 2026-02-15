@extends('Partials.app', ['activeMenu' => 'maintenance-parts'])
@section('title') Edit Vehicle Part @endsection
@section('content')
<div class="content">
    <div class="block block-rounded">
        <div class="block-header block-header-default">
            <h3 class="block-title">Edit Vehicle Part</h3>
            <a href="{{ route('maintenance.parts.index') }}" class="btn btn-secondary btn-sm float-end">Back to List</a>
        </div>
        <div class="block-content">
            <form action="{{ route('maintenance.parts.update', $part->id) }}" method="POST" autocomplete="off">
                @csrf
                @method('PUT')
                @include('VehicleManagement.VehicleMaintenance.VehiclePart.form', ['part' => $part])
                <button type="submit" class="btn btn-primary">Update</button>
            </form>
        </div>
    </div>
</div>
@endsection