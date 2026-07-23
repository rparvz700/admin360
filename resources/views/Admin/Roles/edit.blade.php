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
                        <div class="col-md-12 mb-4">
                            <label class="form-label" for="role-name">Role Name<span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="role-name" name="name"
                                value="{{ $role->name }}" placeholder="Role Name">
                            @if ($errors->has('name'))
                                <div class="text-danger">
                                    <small>{{ $errors->first('name') }}</small>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Permissions<span class="text-danger">*</span></label>
                            @if ($errors->has('permissions'))
                                <div class="text-danger mb-2">
                                    <small>{{ $errors->first('permissions') }}</small>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="row">
                        @foreach ($groupedPermissions as $groupName => $groupPermissions)
                            <div class="col-md-6 col-xxl-4 mb-4">
                                <div class="card h-100 border">
                                    <div class="card-header bg-light d-flex justify-content-between align-items-center py-2 px-3">
                                        <span class="fw-semibold">{{ $groupName }}</span>
                                        <div class="form-check mb-0">
                                            <input class="form-check-input group-select-all" type="checkbox" id="select-all-{{ $loop->index }}">
                                            <label class="form-check-label fs-sm" for="select-all-{{ $loop->index }}">Select All</label>
                                        </div>
                                    </div>
                                    <div class="card-body p-3">
                                        <div class="row g-2">
                                            @foreach ($groupPermissions as $permission)
                                                <div class="col-12">
                                                    <div class="form-check">
                                                        <input class="form-check-input permission-checkbox" type="checkbox"
                                                            value="{{ $permission->id }}" id="perm-{{ $permission->id }}"
                                                            {{ in_array($permission->id, $rolePermissions) ? 'checked' : '' }}
                                                            name="permissions[]">
                                                        <label class="form-check-label fs-sm text-break" for="perm-{{ $permission->id }}">
                                                            {{ $permission->name }}
                                                        </label>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="row mt-3">
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
    <script>
        $(document).ready(function() {
            // Group select-all change handler
            $('.group-select-all').on('change', function() {
                $(this).closest('.card').find('.permission-checkbox').prop('checked', this.checked);
            });

            // Individual checkbox change handler
            $('.permission-checkbox').on('change', function() {
                var card = $(this).closest('.card');
                var total = card.find('.permission-checkbox').length;
                var checked = card.find('.permission-checkbox:checked').length;
                card.find('.group-select-all').prop('checked', total === checked);
            });

            // Initialize select-all checkboxes state
            $('.group-select-all').each(function() {
                var card = $(this).closest('.card');
                var total = card.find('.permission-checkbox').length;
                var checked = card.find('.permission-checkbox:checked').length;
                $(this).prop('checked', total === checked && total > 0);
            });
        });
    </script>
@endsection
