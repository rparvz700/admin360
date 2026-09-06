@extends('Partials.app', ['activeMenu' => 'vehicle-types'])
@section('title') Add Vehicle Type @endsection

@section('content')
    <!-- Hero -->
    <div class="bg-body-light">
        <div class="content content-full">
            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
                <div class="flex-grow-1">
                    <h1 class="h3 fw-bold mb-1">
                        <i class="fa fa-plus-circle me-2 text-primary"></i> Add Vehicle Type
                    </h1>
                    <h2 class="fs-base lh-base fw-medium text-muted mb-0">
                        Define a new vehicle classification category
                    </h2>
                </div>
                <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-alt">
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="javascript:void(0)">Vehicle Management</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('vehicle-types.index') }}">Vehicle Types</a>
                        </li>
                        <li class="breadcrumb-item" aria-current="page">
                            Add Type
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
    <!-- END Hero -->

    <!-- Page Content -->
    <div class="content">
        <form action="{{ route('vehicle-types.store') }}" method="POST" autocomplete="off">
            @csrf
            @include('VehicleManagement.VehicleTypes.form', ['type' => null])

            <!-- Form Actions -->
            <div class="block block-rounded shadow-sm mt-4">
                <div class="block-content block-content-full bg-body-light d-flex justify-content-between align-items-center">
                    <a href="{{ route('vehicle-types.index') }}" class="btn btn-alt-secondary">
                        <i class="fa fa-arrow-left me-1"></i> Back to Categories
                    </a>
                    <div class="d-flex gap-2">
                        <button type="reset" class="btn btn-alt-secondary">
                            <i class="fa fa-undo me-1"></i> Reset
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-check me-1"></i> Save Vehicle Type
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection
