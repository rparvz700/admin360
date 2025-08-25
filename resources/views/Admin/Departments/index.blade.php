@extends('Partials.app', ['activeMenu' => $activeMenu])

@section('title')
    {{ env('APP_NAME') }}
@endsection


@section('page_title')
    Department List
@endsection

@section('styles')
    <!-- Page JS Plugins CSS for datatable -->
    <link rel="stylesheet" href="{{ asset('js/plugins/datatables-bs5/css/dataTables.bootstrap5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('js/plugins/datatables-buttons-bs5/css/buttons.bootstrap5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('js/plugins/datatables-responsive-bs5/css/responsive.bootstrap5.min.css') }}">
@endsection

@section('content')
    <!-- Hero -->
    <div class="content">
        <div class="block block-rounded">
            {{-- Response message --}}
            @if (Session::has('success'))
                <div class="alert alert-success alert-dismissible" role="alert">
                    <small class="mb-0">
                        {{ Session::get('success') }}
                    </small>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            {{-- End response message --}}
            <div class="block-header block-header-default">
                <h3 class="block-title">Departments</h3>
                @can('create-department')
                    <a href="{{ route('departments.create') }}" class="btn btn-sm btn-primary">Add Department</a>
                @endcan
            </div>
            <div class="block-content fs-sm data-content">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered table-striped table-vcenter js-dataTable-full table-hover"
                        id="ticket-datatable">
                        <thead>
                            <tr>
                                <th class="text-center">SI</th>
                                <th>Department Name</th>
                                <th>HOD</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody class="">
                            @foreach ($departments as $key => $department)
                                <tr>
                                    <td class="text-center">{{ $key + 1 }}</td>
                                    <td>{{ $department->name }}</td>
                                    <td>{{ isset($department->hod) ? $department->depHead->name : '' }}</td>
                                    <td>
                                        <span class="badge bg-{{ $department->status == 1 ? 'success' : 'danger' }}">
                                            {{ $department->status == 1 ? 'Active' : 'Inactive' }}
                                        </span>

                                    </td>
                                    <td>
                                        <div class="d-flex" style="justify-content: start;">
                                            @can('edit-department')
                                                <a href="{{ route('departments.edit', $department->id) }}"
                                                    style="margin-right: 3px;" class="btn btn-sm btn-primary p-1 py-0">
                                                    <i class="fa fa-pen-to-square"></i>
                                                </a>
                                            @endcan

                                            @can('delete-department')
                                                <form action="{{ route('departments.destroy', $department->id) }}"
                                                    method="post">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm p-1 py-0"
                                                        onclick="return confirm('Do you want to delete this department?');"><i
                                                            class="fa fa-trash-can"></i></button>
                                                </form>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <!-- END Page Content -->
@endsection

@section('scripts')
    <script src="{{ asset('js/lib/jquery.min.js') }}"></script>

    <!-- Page JS Plugins for datatable-->
    <script src="{{ asset('js/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('js/plugins/datatables-bs5/js/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('js/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('js/plugins/datatables-responsive-bs5/js/responsive.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('js/plugins/datatables-buttons/dataTables.buttons.min.js') }}"></script>

    {{-- <script src="{{ asset('js/pages/be_tables_datatables.min.js') }}"></script> --}}

    <script>
        $('#ticket-datatable').DataTable({
            order: [
                [0, 'asc']
            ]
        });
    </script>

    {{-- <script src="{{ asset('js/custom/create_ticket.js') }}"></script> --}}
@endsection
