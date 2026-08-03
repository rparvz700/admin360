@extends('Partials.app', ['activeMenu' => 'vendors'])
@section('title') Edit Vendor @endsection
@section('content')
<div class="content">
    <div class="block block-rounded">
        <div class="block-header block-header-default">
            <h3 class="block-title">Edit Vendor</h3>
            <a href="{{ route('maintenance.vendors.index') }}" class="btn btn-secondary btn-sm float-end">Back to List</a>
        </div>
        <div class="block-content">
            <form action="{{ route('maintenance.vendors.update', $vendor->id) }}" method="POST" autocomplete="off">
                @csrf
                @method('PUT')
                @include('VehicleManagement.VehicleMaintenance.Vendor.form', ['vendor' => $vendor])
                <button type="submit" class="btn btn-primary">Update</button>
            </form>
        </div>
    </div>
</div>
@endsection