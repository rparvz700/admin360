@extends('Partials.app', ['activeMenu' => 'vehicles'])
@section('title') Vehicles @endsection
@section('styles')
    <link rel="stylesheet" href="{{ asset('js/plugins/datatables-bs5/css/dataTables.bootstrap5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('js/plugins/datatables-buttons-bs5/css/buttons.bootstrap5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('js/plugins/datatables-responsive-bs5/css/responsive.bootstrap5.min.css') }}">
    <style>
        .stat-card {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }
    </style>
@endsection

@section('content')
    <!-- Hero -->
    <div class="bg-body-light">
        <div class="content content-full">
            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
                <div class="flex-grow-1">
                    <h1 class="h3 fw-bold mb-1">
                        <i class="fa fa-car me-2 text-primary"></i> Vehicles
                    </h1>
                    <h2 class="fs-base lh-base fw-medium text-muted mb-0">
                        Manage corporate fleet, vehicle classifications, technical details, and lifecycle status
                    </h2>
                </div>
                <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-alt">
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="javascript:void(0)">Vehicle Management</a>
                        </li>
                        <li class="breadcrumb-item" aria-current="page">
                            Vehicles
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
    <!-- END Hero -->

    <!-- Page Content -->
    <div class="content">
        @if (Session::has('success'))
            <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2" role="alert">
                <i class="fa fa-check-circle"></i>
                <div class="flex-grow-1">{{ Session::get('success') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if (Session::has('error'))
            <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2" role="alert">
                <i class="fa fa-exclamation-circle"></i>
                <div class="flex-grow-1">{{ Session::get('error') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Quick Stats Overview -->
        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-xxl-3">
                <div class="block block-rounded d-flex flex-column h-100 mb-0 border-start border-4 border-primary stat-card shadow-sm">
                    <div class="block-content block-content-full d-flex align-items-center justify-content-between">
                        <div>
                            <div class="fs-sm fw-semibold text-uppercase text-muted">Total Fleet</div>
                            <div class="fs-2 fw-bold text-dark">{{ $stats['total'] ?? 0 }}</div>
                        </div>
                        <div class="item item-circle bg-primary-light text-primary">
                            <i class="fa fa-car fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xxl-3">
                <div class="block block-rounded d-flex flex-column h-100 mb-0 border-start border-4 border-success stat-card shadow-sm">
                    <div class="block-content block-content-full d-flex align-items-center justify-content-between">
                        <div>
                            <div class="fs-sm fw-semibold text-uppercase text-muted">Active Vehicles</div>
                            <div class="fs-2 fw-bold text-success">{{ $stats['active'] ?? 0 }}</div>
                        </div>
                        <div class="item item-circle bg-success-light text-success">
                            <i class="fa fa-check-circle fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xxl-3">
                <div class="block block-rounded d-flex flex-column h-100 mb-0 border-start border-4 border-info stat-card shadow-sm">
                    <div class="block-content block-content-full d-flex align-items-center justify-content-between">
                        <div>
                            <div class="fs-sm fw-semibold text-uppercase text-muted">Company Owned</div>
                            <div class="fs-2 fw-bold text-info">{{ $stats['owned'] ?? 0 }}</div>
                        </div>
                        <div class="item item-circle bg-info-light text-info">
                            <i class="fa fa-shield-alt fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xxl-3">
                <div class="block block-rounded d-flex flex-column h-100 mb-0 border-start border-4 border-warning stat-card shadow-sm">
                    <div class="block-content block-content-full d-flex align-items-center justify-content-between">
                        <div>
                            <div class="fs-sm fw-semibold text-uppercase text-muted">Rented Fleet</div>
                            <div class="fs-2 fw-bold text-warning">{{ $stats['rented'] ?? 0 }}</div>
                        </div>
                        <div class="item item-circle bg-warning-light text-warning">
                            <i class="fa fa-handshake fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Vehicles Table Block -->
        <div class="block block-rounded shadow-sm">
            <div class="block-header block-header-default">
                <h3 class="block-title">
                    <i class="fa fa-list me-1 text-muted"></i> Vehicle Registry
                </h3>
                <div class="block-options">
                    @can('create-vehicle')
                        <a href="{{ route('vehicles.create') }}" class="btn btn-primary btn-sm">
                            <i class="fa fa-plus me-1"></i> Add Vehicle
                        </a>
                    @endcan
                </div>
            </div>
            <div class="block-content fs-sm data-content pb-4">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered table-striped table-vcenter js-dataTable-full table-hover js-dataTable-responsive" id="vehicles-table">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width: 60px;">ID</th>
                                <th>Registration #</th>
                                <th>Type</th>
                                <th>Brand</th>
                                <th>Model</th>
                                <th>CC</th>
                                <th>Year</th>
                                <th class="text-center">Ownership</th>
                                <th class="text-center">Status</th>
                                <th class="text-center" style="width: 100px;">Actions</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('js/lib/jquery.min.js') }}"></script>
    <script src="{{ asset('js/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('js/plugins/datatables-bs5/js/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('js/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('js/plugins/datatables-responsive-bs5/js/responsive.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('js/plugins/datatables-buttons/dataTables.buttons.min.js') }}"></script>
    <script>
        $(function() {
            var table = $('#vehicles-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('vehicles.list') }}',
                pageLength: 15,
                lengthMenu: [[10, 15, 25, 50, 100], [10, 15, 25, 50, 100]],
                order: [[0, 'desc']],
                columns: [
                    { data: 'id', className: 'text-center' },
                    { data: 'registration_number' },
                    { data: 'vehicle_type' },
                    { data: 'brand' },
                    { data: 'model' },
                    { data: 'engine_cc' },
                    { data: 'manufacture_year' },
                    { data: 'ownership', className: 'text-center' },
                    { data: 'status', className: 'text-center' },
                    { data: 'actions', orderable: false, searchable: false, className: 'text-center' },
                ],
                drawCallback: function() {
                    // Initialize Bootstrap tooltips for action buttons
                    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                    tooltipTriggerList.map(function(tooltipTriggerEl) {
                        return new bootstrap.Tooltip(tooltipTriggerEl);
                    });
                }
            });
        });
    </script>
@endsection

