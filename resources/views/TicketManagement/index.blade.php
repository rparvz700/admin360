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
    <div class="container-fluid mt-4">
        <div class="row mb-4">
            <div class="col-md-6">
                <h2>My Tickets</h2>
            </div>
            <div class="col-md-6 text-end">
                <a href="{{ route('tickets.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Create New Ticket
                </a>
            </div>
        </div>

        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <!-- IMPORTANT: Changed form action and method to point to '#' and prevent default submit -->
                <!-- We will handle filtering via DataTables AJAX 'data' function -->
                <form id="ticket-filter-form" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Ticket Type</label>
                        <select name="ticket_type" class="form-select">
                            <option value="">All Types</option>
                            <option value="vehicle_support"
                                {{ request('ticket_type') == 'vehicle_support' ? 'selected' : '' }}>Vehicle Support</option>
                            <option value="asset_request"
                                {{ request('ticket_request') == 'asset_request' ? 'selected' : '' }}>Asset Request</option>
                            <option value="asset_repair" {{ request('asset_repair') == 'asset_repair' ? 'selected' : '' }}>
                                Asset Repair</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="assigned" {{ request('status') == 'assigned' ? 'selected' : '' }}>Assigned
                            </option>
                            <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In
                                Progress</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed
                            </option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled
                            </option>
                        </select>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">Filter</button>
                        <button type="button" id="reset-filters" class="btn btn-secondary">Reset</button>
                    </div>
                </form>
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
                        id="tickets-table">
                        <thead>
                            <tr>
                                <th>Ticket #</th>
                                <th>Type</th>
                                <th>Title</th>
                                <th>Priority</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
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
            var ticketsTable = $('#tickets-table').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: '{{ route('tickets.index') }}',
                    data: function(d) {
                        // Pass filter values from the form to the AJAX request
                        d.ticket_type = $('select[name="ticket_type"]').val();
                        d.status = $('select[name="status"]').val();
                        // d.search.value = $('input[type="search"]').val(); // DataTables handles global search automatically
                    }
                },
                columns: [
                    // Ensure these 'data' keys match the addColumn/editColumn names in your controller
                    {
                        data: 'ticket_number_link',
                        name: 'ticket_number'
                    }, // 'name' for server-side sorting/searching
                    {
                        data: 'ticket_type_badge',
                        name: 'ticket_type'
                    }, // 'name' for server-side sorting/searching
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
                    [5, 'desc']
                ] // Default sort by 'Created' column (index 5) descending
            });

            // Handle filter form submission
            $('#ticket-filter-form').on('submit', function(e) {
                e.preventDefault(); // Prevent standard form submission
                ticketsTable.draw(); // Reload the DataTable with current filter values
            });

            // Handle reset button click
            $('#reset-filters').on('click', function() {
                $('select[name="ticket_type"]').val(''); // Clear ticket type filter
                $('select[name="status"]').val(''); // Clear status filter
                ticketsTable.draw(); // Reload the DataTable
            });
        });
    </script>
@endsection
