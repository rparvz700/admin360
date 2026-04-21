@extends('Partials.app', ['activeMenu' => 'vehicles'])
@section('title') Vendors @endsection
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
            <h3 class="block-title">Vendors</h3>
            <a href="{{ route('maintenance.vendors.create') }}" class="btn btn-primary btn-sm float-end">Add Vendor</a>
        </div>
        <div class="block-content fs-sm data-content">
            <div class="table-responsive">
                <table class="table table-sm table-bordered table-striped table-vcenter js-dataTable-full table-hover js-dataTable-responsive" id="vendors-table">
                    <thead>
                        <tr>
                            <th class="text-center all">Code</th>
                            <th class="all">Name</th>
                            <th class="all">Type</th>
                            <th class="all">Contact Person</th>
                            <th class="all">Phone</th>
                            <th class="all">Email</th>
                            <th class="all">Rating</th>
                            <th class="all">Services Count</th>
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
            $('#vendors-table').DataTable({
                processing: true,
                serverSide: true,
                pagingType: "full_numbers",
                pageLength: 10,
                lengthMenu: [
                    [5, 10, 15, 20],
                    [5, 10, 15, 20],
                ],
                order: [
                    [0, 'desc']
                ],
                autoWidth: !1,
                responsive: !0,
                ajax: '{{ route('maintenance.vendors.index') }}',
                columns: [
                    { data: 'vendor_code' },
                    { data: 'name' },
                    { data: 'vendor_type' },
                    { data: 'contact_person' },
                    { data: 'phone' },
                    { data: 'email' },
                    { data: 'rating' },
                    { data: 'maintenances_count' },
                    { data: 'total_cost' },
                    { data: 'is_active', render: function(data) {
                        return data ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>';
                    }},
                    { data: 'actions', orderable: false, searchable: false },
                ]
            });
        });
    </script>
@endsection