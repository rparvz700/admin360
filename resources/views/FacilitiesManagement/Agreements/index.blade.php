@extends('Partials.app', ['activeMenu' => 'agreements'])

@section('title')
    {{ env('APP_NAME') }}
@endsection

@section('page_title')
    Agreement List
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
                <h3 class="block-title">Agreements</h3>
                <a href="{{ route('agreements.create') }}" class="btn btn-sm btn-primary">Add Agreement</a>
            </div>
            <div class="block-content fs-sm data-content">
                <div class="table-responsive">
                    <table id="agreements-table" class="table table-sm table-bordered table-striped table-vcenter js-dataTable-full table-hover js-dataTable-responsive">
                        <thead>
                            <tr>
                                <th class="text-center all">ID</th>
                                <th class="all">Reference No</th>
                                <th class="all">Agreement Date</th>
                                <th class="all">From Date</th>
                                <th class="all">To Date</th>
                                <th class="all">Status</th>
                                <th class="all">Remarks</th>
                                <th class="all">Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="{{ asset('js/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('js/plugins/datatables-bs5/js/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('js/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('js/plugins/datatables-responsive-bs5/js/responsive.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('js/plugins/datatables-buttons/dataTables.buttons.min.js') }}"></script>
    <script src="{{ asset('js/plugins/datatables-buttons-bs5/js/buttons.bootstrap5.min.js') }}"></script>
    <script>
        $(function () {
            $('#agreements-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('agreements.index') }}',
                columns: [
                    { data: 'id', name: 'id', className: 'text-center' },
                    { data: 'agreement_ref_no', name: 'agreement_ref_no' },
                    { data: 'agreement_date', name: 'agreement_date' },
                    { data: 'from_date', name: 'from_date' },
                    { data: 'to_date', name: 'to_date' },
                    { data: 'status', name: 'status' },
                    { data: 'remarks', name: 'remarks' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false },
                ]
            });
        });
    </script>
@endsection
