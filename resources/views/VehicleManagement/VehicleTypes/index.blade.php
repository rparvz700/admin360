@extends('Partials.app', ['activeMenu' => 'vehicle-types'])
@section('title') Vehicle Types @endsection

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
                        <i class="fa fa-tags me-2 text-primary"></i> Vehicle Types
                    </h1>
                    <h2 class="fs-base lh-base fw-medium text-muted mb-0">
                        Classify fleet assets into standard categories (Sedan, Microbus, SUV, Pickup, etc.)
                    </h2>
                </div>
                <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-alt">
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="javascript:void(0)">Vehicle Management</a>
                        </li>
                        <li class="breadcrumb-item" aria-current="page">
                            Vehicle Types
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
            <div class="col-sm-6 col-lg-4">
                <div class="block block-rounded d-flex flex-column h-100 mb-0 border-start border-4 border-primary stat-card shadow-sm">
                    <div class="block-content block-content-full d-flex align-items-center justify-content-between">
                        <div>
                            <div class="fs-sm fw-semibold text-uppercase text-muted">Total Categories</div>
                            <div class="fs-2 fw-bold text-dark">{{ $stats['total_types'] ?? 0 }}</div>
                        </div>
                        <div class="item item-circle bg-primary-light text-primary">
                            <i class="fa fa-tags fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-4">
                <div class="block block-rounded d-flex flex-column h-100 mb-0 border-start border-4 border-success stat-card shadow-sm">
                    <div class="block-content block-content-full d-flex align-items-center justify-content-between">
                        <div>
                            <div class="fs-sm fw-semibold text-uppercase text-muted">Classified Vehicles</div>
                            <div class="fs-2 fw-bold text-success">{{ $stats['total_vehicles'] ?? 0 }}</div>
                        </div>
                        <div class="item item-circle bg-success-light text-success">
                            <i class="fa fa-car fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-12 col-lg-4">
                <div class="block block-rounded d-flex flex-column h-100 mb-0 border-start border-4 border-info stat-card shadow-sm">
                    <div class="block-content block-content-full d-flex align-items-center justify-content-between">
                        <div>
                            <div class="fs-sm fw-semibold text-uppercase text-muted">Most Common Type</div>
                            <div class="fs-4 fw-bold text-info text-truncate" style="max-width: 220px;">
                                {{ $stats['most_common']->type_name ?? 'N/A' }}
                            </div>
                            @if(isset($stats['most_common']))
                                <div class="fs-xs text-muted">{{ $stats['most_common']->vehicles_count }} vehicles assigned</div>
                            @endif
                        </div>
                        <div class="item item-circle bg-info-light text-info">
                            <i class="fa fa-award fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Vehicle Types Table Block -->
        <div class="block block-rounded shadow-sm">
            <div class="block-header block-header-default">
                <h3 class="block-title">
                    <i class="fa fa-list me-1 text-muted"></i> Vehicle Type Categories
                </h3>
                <div class="block-options">
                    @can('create-vehicle-type')
                        <a href="{{ route('vehicle-types.create') }}" class="btn btn-primary btn-sm">
                            <i class="fa fa-plus me-1"></i> Add Vehicle Type
                        </a>
                    @endcan
                </div>
            </div>
            <div class="block-content fs-sm data-content pb-4">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered table-striped table-vcenter js-dataTable-full table-hover js-dataTable-responsive" id="vehicle-types-table">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width: 70px;">ID</th>
                                <th>Category / Type Name</th>
                                <th class="text-center" style="width: 180px;">Assigned Vehicles</th>
                                <th class="text-center" style="width: 120px;">Actions</th>
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
            var table = $('#vehicle-types-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('vehicle-types.list') }}',
                pageLength: 15,
                lengthMenu: [[10, 15, 25, 50], [10, 15, 25, 50]],
                order: [[0, 'desc']],
                columns: [
                    { data: 'id', className: 'text-center' },
                    { data: 'type_name' },
                    { data: 'vehicles_count', className: 'text-center' },
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
