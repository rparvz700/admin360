@extends('Partials.app', ['activeMenu' => 'tickets'])

@section('title')
    All Tickets - {{ config('app.name') }}
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
        .stat-block {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .stat-block:hover {
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
                        All Tickets
                    </h1>
                    <h2 class="fs-base lh-base fw-medium text-muted mb-0">
                        Admin dashboard to assign resources, track trip progress, and resolve tickets
                    </h2>
                </div>
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

        <!-- Quick Stats Overview -->
        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-xxl-3">
                <div class="block block-rounded d-flex flex-column h-100 mb-0 stat-block border-start border-4 border-warning">
                    <div class="block-content block-content-full d-flex align-items-center justify-content-between">
                        <div>
                            <div class="fs-sm fw-semibold text-uppercase text-muted">Pending Tickets</div>
                            <div class="fs-2 fw-bold text-dark">{{ \App\Models\Ticket::pending()->count() }}</div>
                        </div>
                        <div class="item item-circle bg-warning-light text-warning">
                            <i class="fa fa-clock fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xxl-3">
                <div class="block block-rounded d-flex flex-column h-100 mb-0 stat-block border-start border-4 border-info">
                    <div class="block-content block-content-full d-flex align-items-center justify-content-between">
                        <div>
                            <div class="fs-sm fw-semibold text-uppercase text-muted">Assigned</div>
                            <div class="fs-2 fw-bold text-dark">{{ \App\Models\Ticket::assigned()->count() }}</div>
                        </div>
                        <div class="item item-circle bg-info-light text-info">
                            <i class="fa fa-user-check fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xxl-3">
                <div class="block block-rounded d-flex flex-column h-100 mb-0 stat-block border-start border-4 border-primary">
                    <div class="block-content block-content-full d-flex align-items-center justify-content-between">
                        <div>
                            <div class="fs-sm fw-semibold text-uppercase text-muted">In Progress</div>
                            <div class="fs-2 fw-bold text-dark">{{ \App\Models\Ticket::where('status', 'in_progress')->count() }}</div>
                        </div>
                        <div class="item item-circle bg-primary-light text-primary">
                            <i class="fa fa-route fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xxl-3">
                <div class="block block-rounded d-flex flex-column h-100 mb-0 stat-block border-start border-4 border-success">
                    <div class="block-content block-content-full d-flex align-items-center justify-content-between">
                        <div>
                            <div class="fs-sm fw-semibold text-uppercase text-muted">Completed</div>
                            <div class="fs-2 fw-bold text-dark">{{ \App\Models\Ticket::where('status', 'completed')->count() }}</div>
                        </div>
                        <div class="item item-circle bg-success-light text-success">
                            <i class="fa fa-check-double fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- END Quick Stats -->

        <!-- Main Ticket Block -->
        <div class="block block-rounded">
            <div class="block-header block-header-default">
                <h3 class="block-title">
                    <i class="fa fa-list-check text-primary me-1"></i> Ticket Master List
                </h3>
                <div class="block-options">
                    <button type="button" class="btn-block-option" data-toggle="block-option" data-action="fullscreen_toggle"></button>
                    <button type="button" class="btn-block-option" data-toggle="block-option" data-action="content_toggle"></button>
                </div>
            </div>

            <!-- Filter Toolbar -->
            <div class="block-content filter-box p-3">
                <form id="admin-ticket-filter-form" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label fs-sm fw-semibold">Search</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="fa fa-search"></i></span>
                            <input type="text" name="search" class="form-control form-control-sm" placeholder="Ticket #, Title, User...">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fs-sm fw-semibold">Ticket Type</label>
                        <select name="ticket_type" class="form-select form-select-sm">
                            <option value="">All Types</option>
                            <option value="vehicle_support">Vehicle Support</option>
                            <option value="asset_request">Asset Request</option>
                            <option value="asset_repair">Asset Repair</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fs-sm fw-semibold">Status</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="">All Statuses</option>
                            <option value="pending">Pending</option>
                            <option value="assigned">Assigned</option>
                            <option value="in_progress">In Progress</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex">
                        <button type="submit" class="btn btn-sm btn-primary me-2">
                            <i class="fa fa-filter me-1"></i> Filter
                        </button>
                        <button type="button" id="reset-admin-filters" class="btn btn-sm btn-alt-secondary">
                            <i class="fa fa-rotate-left me-1"></i> Reset
                        </button>
                    </div>
                </form>
            </div>

            <!-- Table Content -->
            <div class="block-content fs-sm data-content">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered table-striped table-vcenter js-dataTable-full table-hover js-dataTable-responsive" id="admin-tickets-table">
                        <thead>
                            <tr>
                                <th class="text-center control" style="width: 25px;"></th>
                                <th class="all text-nowrap" style="width: 140px;">Ticket #</th>
                                <th class="min-tablet">Type</th>
                                <th class="min-tablet">Requester</th>
                                <th class="min-desktop">Title</th>
                                <th class="min-tablet">Priority</th>
                                <th class="all">Status</th>
                                <th class="min-desktop">Assigned To</th>
                                <th class="min-desktop">Created</th>
                                <th class="text-center all" style="width: 90px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- DataTables will populate this -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <!-- END Main Block -->
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
            var adminTicketsTable = $('#admin-tickets-table').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                autoWidth: false,
                pageLength: 10,
                lengthMenu: [[5, 10, 25, 50], [5, 10, 25, 50]],
                ajax: {
                    url: '{{ route('admin.tickets.index') }}',
                    data: function(d) {
                        d.search = $('input[name="search"]').val();
                        d.ticket_type = $('select[name="ticket_type"]').val();
                        d.status = $('select[name="status"]').val();
                    }
                },
                columns: [
                    { data: null, defaultContent: '', orderable: false, searchable: false },
                    { data: 'ticket_number_link', name: 'ticket_number', className: 'fw-semibold text-nowrap' },
                    { data: 'ticket_type_badge', name: 'ticket_type' },
                    { data: 'user_name', name: 'user.name' },
                    { data: 'title', name: 'title' },
                    { data: 'priority_badge', name: 'priority' },
                    { data: 'status_badge', name: 'status' },
                    { data: 'assigned_to_name', name: 'assignedTo.name' },
                    { data: 'created_at', name: 'created_at' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-center' },
                ],
                order: [
                    [8, 'desc']
                ],
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Quick search...",
                    lengthMenu: "_MENU_",
                    paginate: {
                        first: '<i class="fa fa-angle-double-left"></i>',
                        previous: '<i class="fa fa-angle-left"></i>',
                        next: '<i class="fa fa-angle-right"></i>',
                        last: '<i class="fa fa-angle-double-right"></i>'
                    }
                }
            });

            $('#admin-ticket-filter-form').on('submit', function(e) {
                e.preventDefault();
                adminTicketsTable.draw();
            });

            $('#reset-admin-filters').on('click', function() {
                $('input[name="search"]').val('');
                $('select[name="ticket_type"]').val('');
                $('select[name="status"]').val('');
                adminTicketsTable.draw();
            });
        });
    </script>
@endsection
