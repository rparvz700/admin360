@extends('Partials.app', ['activeMenu' => 'tickets'])

@section('title')
    Tickets List
@endsection

@section('styles')
    <link rel="stylesheet" href="{{ asset('js/plugins/datatables-bs5/css/dataTables.bootstrap5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('js/plugins/datatables-buttons-bs5/css/buttons.bootstrap5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('js/plugins/datatables-responsive-bs5/css/responsive.bootstrap5.min.css') }}">
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-md-6">
                <h2>All Tickets</h2>
            </div>
        </div>

        <!-- Filters & Search -->
        <div class="card mb-4">
            <div class="card-body">
                <!-- IMPORTANT: Changed form action and method. We will handle filtering via DataTables AJAX 'data' function -->
                <form id="admin-ticket-filter-form" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Search</label>
                        <!-- Removed value="{{ request('search') }}" as DataTables will manage the search input -->
                        <input type="text" name="search" class="form-control" placeholder="Ticket #, Title, User...">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Ticket Type</label>
                        <select name="ticket_type" class="form-select">
                            <option value="">All Types</option>
                            <option value="vehicle_support">Vehicle Support</option>
                            <option value="asset_request">Asset Request</option>
                            <option value="asset_repair">Asset Repair</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="assigned">Assigned</option>
                            <option value="in_progress">In Progress</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">Filter</button>
                        <button type="button" id="reset-admin-filters" class="btn btn-secondary">Reset</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Statistics Cards - These can remain as static counts or you could make them dynamic with separate AJAX calls -->
        <!-- For simplicity, they are kept as static counts from the initial page load for now. -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card text-white bg-warning">
                    <div class="card-body">
                        <h5 class="card-title">Pending</h5>
                        <h2>{{ \App\Models\Ticket::pending()->count() }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-info">
                    <div class="card-body">
                        <h5 class="card-title">Assigned</h5>
                        <h2>{{ \App\Models\Ticket::assigned()->count() }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-primary">
                    <div class="card-body">
                        <h5 class="card-title">In Progress</h5>
                        <h2>{{ \App\Models\Ticket::where('status', 'in_progress')->count() }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-success">
                    <div class="card-body">
                        <h5 class="card-title">Completed</h5>
                        <h2>{{ \App\Models\Ticket::where('status', 'completed')->count() }}</h2>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tickets Table -->
        <div class="card">
            <div class="card-body">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <div class="table-responsive">
                    <table
                        class="table table-hover table-striped table-bordered table-vcenter js-dataTable-full js-dataTable-responsive"
                        id="admin-tickets-table">
                        <thead>
                            <tr>
                                <!-- Control column for expand/collapse -->
                                <th class="text-center control"></th>
                                <th class="all">Ticket #</th>
                                <th class="min-tablet">Type</th>
                                <th class="min-tablet">User</th>
                                <th class="min-desktop">Title</th>
                                <th class="min-tablet">Priority</th>
                                <th class="all">Status</th>
                                <th class="min-desktop">Assigned To</th>
                                <th class="min-desktop">Created</th>
                                <th class="all">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- DataTables will populate this -->
                        </tbody>
                    </table>
                </div>

                <!-- Laravel pagination links are no longer needed -->
                {{-- <div class="mt-3">
                {{ $tickets->links() }}
            </div> --}}
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
            var adminTicketsTable = $('#admin-tickets-table').DataTable({
                processing: true,
                serverSide: true,
                responsive: true, // Enable responsive behavior
                ajax: {
                    url: '{{ route('admin.tickets.index') }}',
                    data: function(d) {
                        // Pass filter values from the form to the AJAX request
                        d.search = $('input[name="search"]').val(); // Custom search input
                        d.ticket_type = $('select[name="ticket_type"]').val();
                        d.status = $('select[name="status"]').val();
                        // DataTables' built-in global search 'd.search.value' will also be sent
                        // You can choose to use one or combine them in your controller.
                        // Here, we're explicitly sending the custom 'search' input value.
                    }
                },
                columns: [{
                        data: null,
                        defaultContent: '',
                        orderable: false,
                        searchable: false
                    }, // Control column
                    {
                        data: 'ticket_number_link',
                        name: 'ticket_number'
                    },
                    {
                        data: 'ticket_type_badge',
                        name: 'ticket_type'
                    },
                    {
                        data: 'user_name',
                        name: 'user.name'
                    }, // For server-side sorting/searching by user name
                    {
                        data: 'title',
                        name: 'title'
                    },
                    {
                        data: 'priority_badge',
                        name: 'priority'
                    },
                    {
                        data: 'status_badge',
                        name: 'status'
                    },
                    {
                        data: 'assigned_to_name',
                        name: 'assignedTo.name'
                    }, // For server-side sorting/searching by assigned user name
                    {
                        data: 'created_at',
                        name: 'created_at'
                    },
                    {
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
                        searchable: false
                    },
                ],
                order: [
                    [8, 'desc']
                ] // Default sort by 'Created' column (index 8, considering the control column) descending
            });

            // Handle filter form submission
            $('#admin-ticket-filter-form').on('submit', function(e) {
                e.preventDefault(); // Prevent standard form submission
                adminTicketsTable.draw(); // Reload the DataTable with current filter values
            });

            // Handle reset button click
            $('#reset-admin-filters').on('click', function() {
                $('input[name="search"]').val(''); // Clear custom search input
                $('select[name="ticket_type"]').val(''); // Clear ticket type filter
                $('select[name="status"]').val(''); // Clear status filter
                adminTicketsTable.draw(); // Reload the DataTable
            });
        });
    </script>
@endsection
