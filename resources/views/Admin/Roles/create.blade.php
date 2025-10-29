@extends('Partials.app', ['activeMenu' => $activeMenu])

@section('title')
    {{ config('app.name') }} 
@endsection


@section('page_title')
    Create Role
@endsection

@section('styles')
@endsection

@section('content')
    <!-- Hero -->
    <div class="content">
        <div class="block block-rounded">
            <div class="block-header block-header-default">
                <h3 class="block-title">Create a Role</h3>
            </div>
            <div class="block-content fs-sm data-content">
                {{-- Form --}}
                <form class="mb-4" action="{{ route('roles.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 col-sm-12 mb-4">
                            <label class="form-label" for="role-name">Role Name<span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="role-name" name="name"
                                placeholder="Role Name">
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
                            <button type="submit" class="btn btn-sm btn-success">Add Role</button>
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
