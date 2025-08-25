@extends('Partials.app', ['activeMenu' => $activeMenu])

@section('title')
    {{ env('APP_NAME') }}
@endsection


@section('page_title')
    Create Department
@endsection

@section('styles')
    <link rel="stylesheet" href="{{ asset('js/plugins/select2/css/select2.min.css') }}">
@endsection

@section('content')
    <!-- Hero -->
    <div class="content">
        <div class="block block-rounded">
            <div class="block-header block-header-default">
                <h3 class="block-title">Create a Department</h3>
            </div>
            <div class="block-content fs-sm data-content">
                {{-- Form --}}
                <form class="mb-4" action="{{ route('departments.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 col-sm-12 mb-4">
                            <label class="form-label" for="department-name">Department Name<span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="department-name" name="name"
                                placeholder="Department Name">
                            @if ($errors->has('name'))
                                <div class="text-danger">
                                    <small>{{ $errors->first('name') }}</small>
                                </div>
                            @endif
                        </div>
                        <div class="col-md-6 col-sm-12 mb-4">
                            <label class="form-label" for="example-select2">HOD</label>
                            <select class="js-select2 form-select" id="example-select2" name="hod" style="width: 100%;"
                                data-placeholder="Choose HOD..">
                                <option></option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 col-sm-12 mb-4">
                            <label class="form-label">Status</label>
                            <div class="space-x-2">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" id="active" name="status"
                                        value="1" checked>
                                    <label class="form-check-label" for="active">Active</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" id="inactive" name="status"
                                        value="0">
                                    <label class="form-check-label" for="inactive">Inactive</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 text-end"> <!-- Use the text-end class to move the button to the right -->
                            <button type="submit" class="btn btn-sm btn-success">Add Department</button>
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
