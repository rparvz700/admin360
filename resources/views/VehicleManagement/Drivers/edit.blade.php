@extends('Partials.app', ['activeMenu' => 'drivers'])
@section('title') Edit Driver @endsection

@section('content')
    <!-- Hero -->
    <div class="bg-body-light">
        <div class="content content-full">
            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
                <div class="flex-grow-1">
                    <h1 class="h3 fw-bold mb-1">
                        <i class="fa fa-user-edit me-2 text-primary"></i> Edit Driver: {{ $driver->name }}
                    </h1>
                    <h2 class="fs-base lh-base fw-medium text-muted mb-0">
                        Update driver profile, contact info, placement, and supervisor assignments
                    </h2>
                </div>
                <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-alt">
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="javascript:void(0)">Vehicle Management</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('drivers.index') }}">Drivers</a>
                        </li>
                        <li class="breadcrumb-item" aria-current="page">
                            Edit Driver
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
    <!-- END Hero -->

    <!-- Page Content -->
    <div class="content">
        <form action="{{ route('drivers.update', $driver->id) }}" method="POST" autocomplete="off">
            @csrf
            @method('PUT')
            @include('VehicleManagement.Drivers.form', ['driver' => $driver])

            <!-- Form Actions -->
            <div class="block block-rounded shadow-sm mt-4">
                <div class="block-content block-content-full bg-body-light d-flex justify-content-between align-items-center">
                    <div class="d-flex gap-2">
                        <a href="{{ route('drivers.index') }}" class="btn btn-alt-secondary">
                            <i class="fa fa-arrow-left me-1"></i> Back to Roster
                        </a>
                        <a href="{{ route('drivers.show', $driver->id) }}" class="btn btn-alt-info">
                            <i class="fa fa-eye me-1"></i> View Profile
                        </a>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="reset" class="btn btn-alt-secondary">
                            <i class="fa fa-undo me-1"></i> Reset
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-check me-1"></i> Update Driver
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection
