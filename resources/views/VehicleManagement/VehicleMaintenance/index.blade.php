@extends('Partials.app', ['activeMenu' => 'maintenance'])
@section('title')
    Vehicle Maintenance
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
                <h3 class="block-title">Vehicle Maintenance Records</h3>
                <a href="{{ route('maintenance.maintenances.create') }}" class="btn btn-primary btn-sm float-end">Add
                    Maintenance</a>
            </div>
            <div class="block-content fs-sm data-content">
                <div class="table-responsive">
                    <table
                        class="table table-sm table-bordered table-striped table-vcenter js-dataTable-full table-hover js-dataTable-responsive"
                        id="maintenances-table">
                        <thead>
                            <tr>
                                <th class="all">Vehicle</th>
                                <th class="all">Type</th>
                                <th class="all">Date</th>
                                <th class="all">Vendor</th>
                                <th class="all">Labor Cost</th>
                                <th class="all">Parts Cost</th>
                                <th class="all">Total Cost</th>
                                <th class="all">Status</th>
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
            $('#maintenances-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('maintenance.maintenances.index') }}',
                columns: [
                    // Ensure these 'data' keys match the addColumn/editColumn names in your controller
                    {
                        data: 'vehicle',
                        name: 'vehicle.registration_number'
                    }, // 'name' for server-side sorting/searching on relation
                    {
                        data: 'maintenance_type',
                        name: 'maintenance_type'
                    },
                    {
                        data: 'start_datetime',
                        name: 'start_datetime'
                    },
                    {
                        data: 'vendor',
                        name: 'vendor.name'
                    }, // 'name' for server-side sorting/searching on relation
                    {
                        data: 'labor_cost',
                        name: 'labor_cost'
                    },
                    {
                        data: 'parts_cost',
                        name: 'parts_cost'
                    },
                    {
                        data: 'total_cost',
                        name: 'total_cost',
                        searchable: false,
                        orderable: false
                    }, // Computed, so often not directly searchable/orderable
                    {
                        data: 'status',
                        name: 'status'
                    },
                    {
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
                        searchable: false
                    },
                ]
            });
        });
    </script>
@endsection
