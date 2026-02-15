@extends('Partials.app', ['activeMenu' => 'vehicles'])
@section('title') Add Vendor @endsection
@section('content')
<div class="content">
    <div class="block block-rounded">
        <div class="block-header block-header-default">
            <h3 class="block-title">Add Vendor</h3>
            <a href="{{ route('maintenance.vendors.index') }}" class="btn btn-secondary btn-sm float-end">Back to List</a>
        </div>
        <div class="block-content">
            <form action="{{ route('maintenance.vendors.store') }}" method="POST" autocomplete="off">
                @csrf
                @include('VehicleManagement.VehicleMaintenance.Vendor.form', ['vendor' => null])
                <button type="submit" class="btn btn-primary">Save</button>
            </form>
        </div>
    </div>
</div>
@endsection