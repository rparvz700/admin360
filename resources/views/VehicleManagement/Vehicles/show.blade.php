@extends('Partials.app', ['activeMenu' => 'vehicles'])
@section('title') Vehicle Details @endsection
@section('content')
<div class="content">
    <div class="block block-rounded">
        <div class="block-header block-header-default">
            <h3 class="block-title">Vehicle Details</h3>
            <a href="{{ route('vehicles.index') }}" class="btn btn-secondary btn-sm float-end">Back to List</a>
        </div>
        <div class="block-content">
            <table class="table table-bordered">
                <tbody>
                    <tr><th>ID</th><td>{{ $vehicle->id }}</td></tr>
                    <tr><th>Registration #</th><td>{{ $vehicle->registration_number }}</td></tr>
                    <tr><th>Type</th><td>{{ $vehicle->vehicleType->type_name ?? '' }}</td></tr>
                    <tr><th>Brand</th><td>{{ $vehicle->brand }}</td></tr>
                    <tr><th>Model</th><td>{{ $vehicle->model }}</td></tr>
                    <tr><th>Year</th><td>{{ $vehicle->manufacture_year }}</td></tr>
                    <tr><th>Color</th><td>{{ $vehicle->color }}</td></tr>
                    <tr><th>Seating Capacity</th><td>{{ $vehicle->seating_capacity }}</td></tr>
                    <tr><th>Engine Number</th><td>{{ $vehicle->engine_number }}</td></tr>
                    <tr><th>Chassis Number</th><td>{{ $vehicle->chassis_number }}</td></tr>
                    <tr><th>Use Purpose</th><td>{{ $vehicle->use_purpose }}</td></tr>
                    <tr><th>Use Company</th><td>{{ $vehicle->use_company }}</td></tr>
                    <tr><th>Is Rented?</th><td>{{ $vehicle->isRented ? 'Yes' : 'No' }}</td></tr>
                    <tr><th>Purchase Price</th><td>{{ $vehicle->purchase_price }}</td></tr>
                    <tr><th>Purchase Date</th><td>{{ $vehicle->purchase_date }}</td></tr>
                    <tr><th>Status</th><td>{{ ucfirst($vehicle->status) }}</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
