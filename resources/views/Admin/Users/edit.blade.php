@extends('Partials.app', ['activeMenu' => $activeMenu])

@section('title')
    {{ config('app.name') }} 
@endsection


@section('page_title')
    Edir User
@endsection

@section('styles')
    <link rel="stylesheet" href="{{ asset('js/plugins/select2/css/select2.min.css') }}">
@endsection

@section('content')
    <!-- Hero -->
    <div class="content">
        <div class="block block-rounded">
            <div class="block-header block-header-default">
                <h3 class="block-title">Update {{ $user->name }} User</h3>
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
                <form class="mb-4" action="{{ route('users.update', $user->id) }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-md-6 col-sm-12 mb-4">
                            <label class="form-label" for="full-name">Full Name<span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="full-name" name="name"
                                value="{{ $user->name }}" placeholder="Full Name">
                            @if ($errors->has('name'))
                                <div class="text-danger">
                                    <small>{{ $errors->first('name') }}</small>
                                </div>
                            @endif
                        </div>
                        <div class="col-md-6 col-sm-12 mb-4">
                            <label class="form-label" for="email">Email<span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" name="email"
                                value="{{ $user->email }}" placeholder="Email" readonly>
                            @if ($errors->has('email'))
                                <div class="text-danger">
                                    <small>{{ $errors->first('email') }}</small>
                                </div>
                            @endif
                        </div>
                        <div class="col-md-6 col-sm-12 mb-4">
                            <label class="form-label" for="hr_id">HR ID</label>
                            <input type="text" class="form-control" id="hr_id" name="hr_id"
                                value="{{ old('hr_id', $user->hr_id) }}" placeholder="HR ID">
                            @if ($errors->has('hr_id'))
                                <div class="text-danger">
                                    <small>{{ $errors->first('hr_id') }}</small>
                                </div>
                            @endif
                        </div>
                        <div class="col-md-6 col-sm-12 mb-4">
                            <label class="form-label" for="company">Company</label>
                            <select class="form-select" id="company" name="company">
                                <option value="">-- Select Company --</option>
                                <option value="SComm" {{ old('company', $user->company) === 'SComm' ? 'selected' : '' }}>SComm</option>
                                <option value="STL" {{ old('company', $user->company) === 'STL' ? 'selected' : '' }}>STL</option>
                                <option value="SCOMM_EZONE" {{ old('company', $user->company) === 'SCOMM_EZONE' ? 'selected' : '' }}>SCOMM_EZONE</option>
                                <option value="STL_EZONE" {{ old('company', $user->company) === 'STL_EZONE' ? 'selected' : '' }}>STL_EZONE</option>
                                <option value="RSL" {{ old('company', $user->company) === 'RSL' ? 'selected' : '' }}>RSL</option>
                            </select>
                            @if ($errors->has('company'))
                                <div class="text-danger">
                                    <small>{{ $errors->first('company') }}</small>
                                </div>
                            @endif
                        </div>
                        <div class="col-md-6 col-sm-12 mb-4">
                            <label class="form-label" for="role">Role<span class="text-danger">*</span></label>
                            <select class="js-select2 form-select" id="role" name="role" style="width: 100%;"
                                data-placeholder="Choose Role...">
                                <option></option>
                                @foreach ($roles as $role)
                                    @if (
                                        $role->name != 'Super Admin' ||
                                            auth()->user()->hasRole('Super Admin'))
                                        <option value="{{ $role->name }}"
                                            {{ $role->name == $user->getRoleNames()[0] ? 'selected' : '' }}>
                                            {{ $role->name }}</option>
                                    @endif
                                @endforeach
                            </select>
                            @if ($errors->has('role'))
                                <div class="text-danger">
                                    <small>{{ $errors->first('role') }}</small>
                                </div>
                            @endif
                        </div>
                        <div class="col-md-6 col-sm-12 mb-4">
                            <label class="form-label">Status</label>
                            <div class="space-x-2">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" id="active" name="status"
                                        value="1" {{ $user->status == 1 ? 'checked' : '' }}>
                                    <label class="form-check-label" for="active">Active</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" id="inactive" name="status"
                                        value="0" {{ $user->status == 0 ? 'checked' : '' }}>
                                    <label class="form-check-label" for="inactive">Inactive</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 text-end"> <!-- Use the text-end class to move the button to the right -->
                            <button type="submit" class="btn btn-sm btn-success">Update User</button>
                        </div>
                    </div>
                </form>
                {{-- End Form --}}
            </div>
        </div>

        <!-- Reset Password Block -->
        <div class="block block-rounded mt-4">
            <div class="block-header block-header-default">
                <h3 class="block-title"><i class="fa fa-lock text-warning me-2"></i> Reset {{ $user->name }}'s Password</h3>
            </div>
            <div class="block-content fs-sm pb-4">
                <form action="{{ route('users.reset-password', $user->id) }}" method="POST" autocomplete="off">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="admin_password">New Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="admin_password" name="password" placeholder="Enter new password (min. 8 characters)" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="admin_password_confirmation">Confirm New Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="admin_password_confirmation" name="password_confirmation" placeholder="Confirm new password" required>
                        </div>
                    </div>
                    <div class="text-end">
                        <button type="submit" class="btn btn-sm btn-warning" onclick="return confirm('Are you sure you want to reset this user\'s password?')">
                            <i class="fa fa-key me-1"></i> Reset Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- END Page Content -->
@endsection

@section('scripts')
    <script src="{{ asset('js/lib/jquery.min.js') }}"></script>
    <script src="{{ asset('js/plugins/select2/js/select2.full.js') }}"></script>
    <script>
        One.helpersOnLoad(['jq-select2']);
    </script>
@endsection
