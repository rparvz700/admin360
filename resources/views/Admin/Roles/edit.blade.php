@extends('Partials.app', ['activeMenu' => $activeMenu])

@section('title')
    {{ config('app.name') }} 
@endsection


@section('page_title')
    Edit Role
@endsection

@section('styles')
@endsection

@section('content')
    <!-- Hero -->
    <div class="content">
        <div class="block block-rounded">
            <div class="block-header block-header-default">
                <h3 class="block-title">Edit {{ $role->name }} Role</h3>
            </div>
            <div class="block-content fs-sm data-content">
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
                {{-- Form --}}
                <form class="mb-4" action="{{ route('roles.update', $role->id) }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-md-6 col-sm-12 mb-4">
                            <label class="form-label" for="role-name">Role Name<span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="role-name" name="name"
                                value="{{ $role->name }}" placeholder="Role Name">
                            @if ($errors->has('name'))
                                <div class="text-danger">
                                    <small>{{ $errors->first('name') }}</small>
                                </div>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="role-permission">Permissions<span
                                    class="text-danger">*</span></label>

                            <div class="space-y-2 mb-4">
                                <div class="row">
                                    @foreach ($permissions as $permission)
                                        <div class="col-md-6 col-sm-12">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox"
                                                    value="{{ $permission->id }}" id="example-checkbox-default1"
                                                    {{ in_array($permission->id, $rolePermissions) ? 'checked' : '' }}
                                                    name="permissions[]">
                                                <label class="form-check-label"
                                                    for="example-checkbox-default1">{{ $permission->name }}</label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            @if ($errors->has('permissions'))
                                <div class="text-danger">
                                    <small>{{ $errors->first('permissions') }}</small>
                                </div>
                            @endif


                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 text-end"> <!-- Use the text-end class to move the button to the right -->
                            <button type="submit" class="btn btn-sm btn-success">Update Role</button>
                        </div>
                    </div>
                </form>
                {{-- End Form --}}
            </div>
        </div>
    </div>
    <!-- END Page Content -->
@endsection

@section('scripts')
@endsection
