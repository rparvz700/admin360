@extends('Partials.app', ['activeMenu' => 'electricity-meters'])

@section('title') Meters Master @endsection

@section('styles')
    <link rel="stylesheet" href="{{ asset('js/plugins/datatables-bs5/css/dataTables.bootstrap5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('js/plugins/datatables-responsive-bs5/css/responsive.bootstrap5.min.css') }}">
@endsection

@section('content')
<div class="content">
    <div class="block block-rounded">
        <div class="block-header block-header-default">
            <h3 class="block-title"><i class="fa fa-tachometer-alt text-warning me-2"></i> Electricity Meters Master</h3>
            <div class="block-options">
                <a href="{{ route('electricity.meters.create') }}" class="btn btn-sm btn-primary">
                    <i class="fa fa-plus me-1"></i> Register New Meter
                </a>
            </div>
        </div>
        <div class="block-content block-content-full">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fa fa-check-circle me-1"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-sm table-vcenter table-hover js-dataTable-full js-dataTable-responsive w-100" id="meters-table">
                    <thead>
                        <tr>
                            <th class="text-nowrap">Meter No</th>
                            <th class="text-nowrap">Consumer No</th>
                            <th class="text-nowrap">Meter Type</th>
                            <th class="text-nowrap">Provider</th>
                            <th class="text-nowrap">Site / POP</th>
                            <th class="text-nowrap">RIO Zone</th>
                            <th class="text-nowrap">Assigned Entity</th>
                            <th class="text-center text-nowrap">Status</th>
                            <th class="text-center text-nowrap">Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    <script src="{{ asset('js/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('js/plugins/datatables-bs5/js/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('js/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('js/plugins/datatables-responsive-bs5/js/responsive.bootstrap5.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            $('#meters-table').DataTable({
                processing: true,
                serverSide: true,
                autoWidth: false,
                responsive: true,
                pagingType: "full_numbers",
                pageLength: 10,
                lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                     "<'row'<'col-sm-12'tr>>" +
                     "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search meters...",
                    lengthMenu: "_MENU_ per page",
                    emptyTable: '<div class="text-center py-4 text-muted"><i class="fa fa-tachometer-alt fa-2x mb-2 d-block opacity-50"></i>No meters registered yet</div>'
                },
                ajax: "{{ route('electricity.meters.index') }}",
                columns: [
                    { data: 'meter_number', name: 'meter_number' },
                    { data: 'consumer_no', name: 'consumer_no', defaultContent: 'N/A' },
                    { data: 'meter_type_badge', name: 'meter_type_badge' },
                    { data: 'provider_name', name: 'provider_name' },
                    { data: 'site', name: 'site' },
                    { data: 'rio', name: 'rio' },
                    { data: 'assigned_to', name: 'assigned_to' },
                    { data: 'is_active', name: 'is_active', className: 'text-center' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-center text-nowrap' }
                ]
            });
        });
    </script>
@endsection
