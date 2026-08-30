@extends('Partials.app', ['activeMenu' => 'tickets'])

@section('title')
    My Tickets - {{ config('app.name') }}
@endsection

@section('styles')
    <link rel="stylesheet" href="{{ asset('js/plugins/datatables-bs5/css/dataTables.bootstrap5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('js/plugins/datatables-buttons-bs5/css/buttons.bootstrap5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('js/plugins/datatables-responsive-bs5/css/responsive.bootstrap5.min.css') }}">
    <style>
        .filter-box {
            background-color: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
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
                        My Tickets
                    </h1>
                    <h2 class="fs-base lh-base fw-medium text-muted mb-0">
                        View, track and manage your support tickets and vehicle requests
                    </h2>
                </div>
                <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                    <a href="{{ route('tickets.create') }}" class="btn btn-primary btn-sm">
                        <i class="fa fa-plus me-1"></i> Create New Ticket
                    </a>
                </nav>
            </div>
        </div>
    </div>
    <!-- END Hero -->

    <!-- Page Content -->
    <div class="content">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fa fa-check-circle me-1"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fa fa-exclamation-circle me-1"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Tickets Block -->
        <div class="block block-rounded">
            <div class="block-header block-header-default">
                <h3 class="block-title">
                    <i class="fa fa-ticket-alt text-primary me-1"></i> Ticket List
                </h3>
                <div class="block-options">
                    <button type="button" class="btn-block-option" data-toggle="block-option" data-action="fullscreen_toggle"></button>
                    <button type="button" class="btn-block-option" data-toggle="block-option" data-action="content_toggle"></button>
                </div>
            </div>

            <!-- Filter Toolbar -->
            <div class="block-content filter-box p-3">
                <form id="ticket-filter-form" class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label fs-sm fw-semibold">Ticket Type</label>
                        <select name="ticket_type" class="form-select form-select-sm">
                            <option value="">All Types</option>
                            <option value="vehicle_support" {{ request('ticket_type') == 'vehicle_support' ? 'selected' : '' }}>Vehicle Support</option>
                            <option value="asset_request" {{ request('ticket_request') == 'asset_request' ? 'selected' : '' }}>Asset Request</option>
                            <option value="asset_repair" {{ request('asset_repair') == 'asset_repair' ? 'selected' : '' }}>Asset Repair</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fs-sm fw-semibold">Status</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="">All Statuses</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="assigned" {{ request('status') == 'assigned' ? 'selected' : '' }}>Assigned</option>
                            <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-4 d-flex">
                        <button type="submit" class="btn btn-sm btn-primary me-2">
                            <i class="fa fa-filter me-1"></i> Filter
                        </button>
                        <button type="button" id="reset-filters" class="btn btn-sm btn-alt-secondary">
                            <i class="fa fa-rotate-left me-1"></i> Reset
                        </button>
                    </div>
                </form>
            </div>

            <!-- Table Content -->
            <div class="block-content fs-sm data-content">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered table-striped table-vcenter js-dataTable-full table-hover js-dataTable-responsive" id="tickets-table">
                        <thead>
                            <tr>
                                <th class="text-center text-nowrap" style="width: 140px;">Ticket #</th>
                                <th>Type</th>
                                <th>Title</th>
                                <th>Priority</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th class="text-center" style="width: 100px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- DataTables will populate this -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <!-- END Tickets Block -->
    </div>
    <!-- END Page Content -->
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
            var ticketsTable = $('#tickets-table').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                autoWidth: false,
                pageLength: 10,
                lengthMenu: [[5, 10, 25, 50], [5, 10, 25, 50]],
                ajax: {
                    url: '{{ route('tickets.index') }}',
                    data: function(d) {
                        d.ticket_type = $('select[name="ticket_type"]').val();
                        d.status = $('select[name="status"]').val();
                    }
                },
                columns: [
                    { data: 'ticket_number_link', name: 'ticket_number', className: 'fw-semibold text-center text-nowrap' },
                    { data: 'ticket_type_badge', name: 'ticket_type' },
                    { data: 'title', name: 'title' },
                    { data: 'priority_badge', name: 'priority' },
                    { data: 'status_badge', name: 'status' },
                    { data: 'created_at', name: 'created_at' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-center' },
                ],
                order: [
                    [5, 'desc']
                ],
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search tickets...",
                    lengthMenu: "_MENU_",
                    paginate: {
                        first: '<i class="fa fa-angle-double-left"></i>',
                        previous: '<i class="fa fa-angle-left"></i>',
                        next: '<i class="fa fa-angle-right"></i>',
                        last: '<i class="fa fa-angle-double-right"></i>'
                    }
                }
            });

            $('#ticket-filter-form').on('submit', function(e) {
                e.preventDefault();
                ticketsTable.draw();
            });

            $('#reset-filters').on('click', function() {
                $('select[name="ticket_type"]').val('');
                $('select[name="status"]').val('');
                ticketsTable.draw();
            });
        });
    </script>
@endsection
