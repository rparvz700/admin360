@extends('Partials.app', ['activeMenu' => 'vehicle-document-attributes'])
@section('title') Vehicle Document Attributes @endsection
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
        <div class="block-header block-header-default">
            <h3 class="block-title">Vehicle Document Attributes</h3>
            <a href="{{ route('vehicle-document-attributes.create') }}" class="btn btn-primary btn-sm float-end">Add Attribute</a>
        </div>
        <div class="block-content fs-sm data-content">
            <div class="table-responsive">
                <table class="table table-sm table-bordered table-striped table-vcenter js-dataTable-full table-hover js-dataTable-responsive" id="attributes-table">
                    <thead>
                        <tr>
                            <th class="text-center all">ID</th>
                            <th class="all">Category</th>
                            <th class="all">Attribute Name</th>
                            <th class="all">Type</th>
                            <th class="all">Options</th>
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
            $('#attributes-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('vehicle-document-attributes.list') }}',
                columns: [
                    { data: 'id' },
                    { data: 'category' },
                    { data: 'attribute_name' },
                    { data: 'attribute_type' },
                    { data: 'options' },
                    { data: 'actions', orderable: false, searchable: false },
                ]
            });
        });
    </script>
@endsection
