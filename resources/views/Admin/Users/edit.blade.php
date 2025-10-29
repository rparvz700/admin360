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
                        {{-- <div class="col-md-6 col-sm-12 mb-4">
                            <label class="form-label" for="phone">Phone<span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="phone" name="phone"
                                value="{{ $user->phone }}" placeholder="Phone">
                            @if ($errors->has('phone'))
                                <div class="text-danger">
                                    <small>{{ $errors->first('phone') }}</small>
                                </div>
                            @endif
                        </div>
                        <div class="col-md-6 col-sm-12 mb-4">
                            <label class="form-label" for="hr_id">HR ID<span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="hr_id" name="hr_id"
                                value="{{ $user->hr_id }}" placeholder="HR ID">
                            @if ($errors->has('hr_id'))
                                <div class="text-danger">
                                    <small>{{ $errors->first('hr_id') }}</small>
                                </div>
                            @endif
                        </div>
                        <div class="col-md-6 col-sm-12 mb-4">
                            <label class="form-label" for="designation">Designation<span
                                    class="text-danger">*</span></label>
                            <select class="js-select2 form-select" id="designation" name="designation" style="width: 100%;"
                                data-placeholder="Choose Designation...">
                                <option></option>
                                <option value="hod" {{ $user->designation == 'hod' ? 'selected' : '' }}>HOD</option>
                                <option value="executive" {{ $user->designation == 'executive' ? 'selected' : '' }}>
                                    Executive</option>
                                <option value="senior executive"
                                    {{ $user->designation == 'senior executive' ? 'selected' : '' }}>Senior Executive
                                </option>
                                <option value="manager" {{ $user->designation == 'manager' ? 'selected' : '' }}>Manager
                                </option>
                                <option value="senior manager"
                                    {{ $user->designation == 'senior manager' ? 'selected' : '' }}>Senior Manager
                                </option>
                            </select>
                            @if ($errors->has('designation'))
                                <div class="text-danger">
                                    <small>{{ $errors->first('designation') }}</small>
                                </div>
                            @endif
                        </div>
                        <div class="col-md-6 col-sm-12 mb-4">
                            <label class="form-label" for="department">Department<span class="text-danger">*</span></label>
                            <select class="js-select2 form-select" id="department" name="department" style="width: 100%;"
                                data-placeholder="Choose Department...">
                                <option></option>
                                @foreach ($departments as $department)
                                    <option value="{{ $department->id }}"
                                        {{ $user->department_id == $department->id ? 'selected' : '' }}>
                                        {{ $department->name }}</option>
                                @endforeach
                            </select>
                            @if ($errors->has('department'))
                                <div class="text-danger">
                                    <small>{{ $errors->first('department') }}</small>
                                </div>
                            @endif
                        </div>
                        <div class="col-md-6 col-sm-12 mb-4">
                            <label class="form-label" for="subcenter">Subcenter</label>
                            <select class="js-select2 form-select" id="subcenter" name="subcenter" style="width: 100%;"
                                data-placeholder="Choose Subcenter...">
                                <option></option>
                                @foreach ($subcenters as $subcenter)
                                    <option value="{{ $subcenter->id }}"
                                        {{ $user->subcenter_id == $subcenter->id ? 'selected' : '' }}>
                                        {{ $subcenter->name }}</option>
                                @endforeach
                            </select>
                            @if ($errors->has('subcenter'))
                                <div class="text-danger">
                                    <small>{{ $errors->first('subcenter') }}</small>
                                </div>
                            @endif
                        </div> --}}
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
