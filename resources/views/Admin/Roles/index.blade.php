@extends('Partials.app', ['activeMenu' => $activeMenu])

@section('title')
    {{ env('APP_NAME') }}
@endsection


@section('page_title')
    Role List
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
                <h3 class="block-title">Roles</h3>
                @can('create-role')
                    <a href="{{ route('roles.create') }}" class="btn btn-sm btn-primary">Add Role</a>
                @endcan
            </div>
            <div class="block-content fs-sm data-content">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered table-striped table-vcenter js-dataTable-full table-hover"
                        id="ticket-datatable">
                        <thead>
                            <tr>
                                <th class="text-center">SI</th>
                                <th>Role Name</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody class="">
                            @foreach ($roles as $key => $role)
                                <tr>
                                    <td class="text-center">{{ $key + 1 }}</td>
                                    <td>{{ $role->name }}</td>
                                    <td>
                                        @if ($role->name != 'Super Admin')
                                            <div class="d-flex" style="justify-content: start;">
                                                @can('edit-role')
                                                    <a href="{{ route('roles.edit', $role->id) }}" style="margin-right: 3px;"
                                                        class="btn btn-sm btn-primary p-1 py-0">
                                                        <i class="fa fa-pen-to-square"></i>
                                                    </a>
                                                @endcan

                                                @can('delete-role')
                                                    @if ($role->name != Auth::user()->hasRole($role->name))
                                                        <form action="{{ route('roles.destroy', $role->id) }}" method="post">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-danger btn-sm p-1 py-0"
                                                                onclick="return confirm('Do you want to delete this role?');"><i
                                                                    class="fa fa-trash-can"></i></button>
                                                        </form>
                                                    @endif
                                                @endcan
                                            </div>
                                        @endif
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
