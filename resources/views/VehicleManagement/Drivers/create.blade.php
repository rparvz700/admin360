@extends('Partials.app', ['activeMenu' => 'drivers'])
@section('title') Add Driver @endsection

@section('content')
    <!-- Hero -->
    <div class="bg-body-light">
        <div class="content content-full">
            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
                <div class="flex-grow-1">
                    <h1 class="h3 fw-bold mb-1">
                        <i class="fa fa-user-plus me-2 text-primary"></i> Add Driver
                    </h1>
                    <h2 class="fs-base lh-base fw-medium text-muted mb-0">
                        Enroll a new driver into the fleet management roster
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
                            Add Driver
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
    <!-- END Hero -->

    <!-- Page Content -->
    <div class="content">
        <form action="{{ route('drivers.store') }}" method="POST" autocomplete="off">
            @csrf
            @include('VehicleManagement.Drivers.form', ['driver' => null])

            <!-- Form Actions -->
            <div class="block block-rounded shadow-sm mt-4">
                <div class="block-content block-content-full bg-body-light d-flex justify-content-between align-items-center">
                    <a href="{{ route('drivers.index') }}" class="btn btn-alt-secondary">
                        <i class="fa fa-arrow-left me-1"></i> Back to Roster
                    </a>
                    <div class="d-flex gap-2">
                        <button type="reset" class="btn btn-alt-secondary">
                            <i class="fa fa-undo me-1"></i> Reset
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-check me-1"></i> Save Driver
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection
