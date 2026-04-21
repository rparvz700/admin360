@extends('Partials.app', ['activeMenu' => 'maintenance-parts'])
@section('title') Vehicle Parts @endsection
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
            <h3 class="block-title">Vehicle Parts</h3>
            <a href="{{ route('maintenance.parts.create') }}" class="btn btn-primary btn-sm float-end">Add Part</a>
        </div>
        <div class="block-content fs-sm data-content">
            <div class="table-responsive">
                <table class="table table-sm table-bordered table-striped table-vcenter js-dataTable-full table-hover js-dataTable-responsive" id="parts-table">
                    <thead>
                        <tr>
                            <th class="text-center all">Code</th>
                            <th class="all">Part Name</th>
                            <th class="all">Category</th>
                            <th class="all">Lifespan (KM)</th>
                            <th class="all">Lifespan (Months)</th>
                            <th class="all">Usage Count</th>
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
            $('#parts-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('maintenance.parts.index') }}',
                columns: [
                    { data: 'part_code' },
                    { data: 'part_name' },
                    { data: 'category' },
                    { data: 'typical_lifespan_km' },
                    { data: 'typical_lifespan_months' },
                    { data: 'usage_count' },
                    { data: 'is_active' },
                    { data: 'actions', orderable: false, searchable: false },
                ]
            });
        });
    </script>
@endsection