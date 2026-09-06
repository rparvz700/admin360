@extends('Partials.app', ['activeMenu' => 'vehicle-types'])
@section('title') Vehicle Type - {{ $type->type_name }} @endsection

@section('content')
    <!-- Hero -->
    <div class="bg-body-light">
        <div class="content content-full">
            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
                <div class="flex-grow-1">
                    <h1 class="h3 fw-bold mb-1">
                        <i class="fa fa-tag me-2 text-primary"></i> {{ $type->type_name }}
                    </h1>
                    <h2 class="fs-base lh-base fw-medium text-muted mb-0">
                        Classification details and associated fleet inventory
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
                            Details
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
    <!-- END Hero -->

    <!-- Page Content -->
    <div class="content">
        <!-- Type Summary Banner -->
        <div class="block block-rounded shadow-sm mb-4">
            <div class="block-content block-content-full">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="item item-2x item-circle bg-primary-light text-primary flex-shrink-0">
                            <i class="fa fa-tag fs-2"></i>
                        </div>
                        <div>
                            <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                                <h2 class="h4 fw-bold mb-0">{{ $type->type_name }}</h2>
                                <span class="badge bg-primary-light text-primary fw-semibold">
                                    <i class="fa fa-car me-1"></i> {{ $type->vehicles_count ?? $type->vehicles->count() }} Vehicles Assigned
                                </span>
                            </div>
                            <div class="text-muted fs-sm">
                                Record ID: <span class="fw-semibold text-dark">#{{ $type->id }}</span>
                                &bull; Created: <span class="text-dark">{{ $type->created_at ? $type->created_at->format('d M, Y') : 'N/A' }}</span>
                                &bull; Last Updated: <span class="text-dark">{{ $type->updated_at ? $type->updated_at->format('d M, Y') : 'N/A' }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex gap-2 flex-shrink-0">
                        <a href="{{ route('vehicle-types.index') }}" class="btn btn-alt-secondary">
                            <i class="fa fa-arrow-left me-1"></i> Back to Categories
                        </a>
                        @can('edit-vehicle-type')
                            <a href="{{ route('vehicle-types.edit', $type->id) }}" class="btn btn-primary">
                                <i class="fa fa-pencil-alt me-1"></i> Edit Type
                            </a>
                        @endcan
                    </div>
                </div>
            </div>
        </div>

        <!-- Associated Vehicles List -->
        <div class="block block-rounded shadow-sm">
            <div class="block-header block-header-default">
                <h3 class="block-title">
                    <i class="fa fa-car me-1 text-muted"></i> Vehicles in this Category
                </h3>
                <div class="block-options">
                    <span class="badge bg-secondary">
                        Showing {{ min(10, $type->vehicles->count()) }} of {{ $type->vehicles_count ?? $type->vehicles->count() }}
                    </span>
                </div>
            </div>
            <div class="block-content p-0">
                @if($type->vehicles->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped table-vcenter fs-sm mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 80px;">ID</th>
                                    <th>Registration Number</th>
                                    <th>Brand / Model</th>
                                    <th class="text-center">Engine CC</th>
                                    <th class="text-center">Year</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center" style="width: 100px;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($type->vehicles as $v)
                                    <tr>
                                        <td>#{{ $v->id }}</td>
                                        <td class="fw-semibold text-dark">
                                            <a href="{{ route('vehicles.show', $v->id) }}" class="text-dark">
                                                <i class="fa fa-car-side me-1 text-primary"></i> {{ $v->registration_number }}
                                            </a>
                                        </td>
                                        <td>{{ $v->brand ?? 'N/A' }} {{ $v->model ?? '' }}</td>
                                        <td class="text-center">{{ $v->engine_cc ? number_format($v->engine_cc) . ' cc' : '-' }}</td>
                                        <td class="text-center">{{ $v->manufacture_year ?? '-' }}</td>
                                        <td class="text-center">
                                            @if(strtolower($v->status) === 'active')
                                                <span class="badge bg-success-light text-success">Active</span>
                                            @elseif(strtolower($v->status) === 'inactive')
                                                <span class="badge bg-warning-light text-warning">Inactive</span>
                                            @else
                                                <span class="badge bg-danger-light text-danger">{{ ucfirst($v->status) }}</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('vehicles.show', $v->id) }}" class="btn btn-sm btn-alt-info" data-bs-toggle="tooltip" title="View Vehicle">
                                                <i class="fa fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <div class="item item-circle bg-body-light text-muted mx-auto mb-3">
                            <i class="fa fa-car fs-3"></i>
                        </div>
                        <h4 class="h5 fw-semibold text-dark mb-1">No vehicles assigned</h4>
                        <p class="text-muted fs-sm mb-3">There are currently no fleet vehicles classified under "{{ $type->type_name }}".</p>
                        @can('create-vehicle')
                            <a href="{{ route('vehicles.create') }}" class="btn btn-sm btn-alt-primary">
                                <i class="fa fa-plus me-1"></i> Add Vehicle Under This Type
                            </a>
                        @endcan
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
