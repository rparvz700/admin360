@extends('Partials.app', ['activeMenu' => 'drivers'])
@section('title') Add Driver @endsection
@section('content')
<div class="content">
    <div class="block block-rounded">
        <div class="block-header block-header-default">
            <h3 class="block-title">Add Driver</h3>
            <a href="{{ route('drivers.index') }}" class="btn btn-secondary btn-sm float-end">Back to List</a>
        </div>
        <div class="block-content">
            <form action="{{ route('drivers.store') }}" method="POST" autocomplete="off">
                @csrf
                @include('VehicleManagement.Drivers.form', ['driver' => null])
                <button type="submit" class="btn btn-primary">Save</button>
            </form>
        </div>
    </div>
</div>
@endsection
