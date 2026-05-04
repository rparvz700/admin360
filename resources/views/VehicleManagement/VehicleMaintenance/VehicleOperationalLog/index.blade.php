@extends('Partials.app', ['activeMenu' => 'operational-logs'])
@section('title')
    Vehicle Operational Logs
@endsection
@section('styles')
    <link rel="stylesheet" href="{{ asset('js/plugins/datatables-bs5/css/dataTables.bootstrap5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('js/plugins/datatables-buttons-bs5/css/buttons.bootstrap5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('js/plugins/datatables-responsive-bs5/css/responsive.bootstrap5.min.css') }}">
@endsection
@section('content')
    <div class="content">
        <div class="block block-rounded">
            @if (Session::has('success'))
                <div class="alert alert-success alert-dismissible" role="alert">
                    <small class="mb-0">
                        {{ Session::get('success') }}
                    </small>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if (Session::has('error'))
                <div class="alert alert-danger alert-dismissible" role="alert">
                    <small class="mb-0">
                        {{ Session::get('error') }}
                    </small>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            <div class="block-header block-header-default">
                <h3 class="block-title">Vehicle Operational Logs</h3>
                <a href="{{ route('maintenance.operational-logs.create') }}" class="btn btn-primary btn-sm float-end">Add
                    Log</a>
            </div>
            <div class="block-content fs-sm data-content">
                <div class="table-responsive">
                    <table
                        class="table table-sm table-bordered table-striped table-vcenter js-dataTable-full table-hover js-dataTable-responsive"
                        id="logs-table">
                        <thead>
                            <tr>
                                <th class="all">Vehicle</th>
                                <th class="all">Log Type</th>
                                <th class="all">Date & Time</th>
                                <th class="all">Meter Reading</th>
                                <th class="all">Vehicle Status</th>
                                <th class="all">Assigned To</th>
                                <th class="all">Logged By</th>
                                <th class="all">Actions</th>
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
            $('#logs-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('maintenance.operational-logs.index') }}',
                columns: [
                    // 'data' keys must match the addColumn/editColumn names in your controller
                    // 'name' keys tell Yajra which database column(s) to use for sorting/searching
                    {
                        data: 'vehicle',
                        name: 'vehicle.registration_number'
                    }, // For sorting/searching by vehicle's registration number
                    {
                        data: 'log_type',
                        name: 'log_type'
                    }, // Assuming 'log_type' is a direct DB column
                    {
                        data: 'logged_at',
                        name: 'logged_at'
                    },
                    {
                        data: 'meter_reading',
                        name: 'meter_reading'
                    },
                    {
                        data: 'vehicle_status',
                        name: 'vehicle_status'
                    }, // Assuming 'vehicle_status' is a direct DB column
                    {
                        data: 'assigned_to',
                        name: 'assigned_to',
                        orderable: false
                    }, // 'name' maps to the addColumn name for filtering, but sorting is complex for computed values. Set orderable: false if sorting logic isn't explicitly handled in controller.
                    {
                        data: 'logged_by',
                        name: 'logger.name'
                    }, // For sorting/searching by logger's name
                    {
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
                        searchable: false
                    },
                ],
                order: [
                    [2, 'desc']
                ] // Keep your default ordering by 'Date & Time' (index 2)
            });
        });
    </script>
@endsection
