@extends('Partials.app', ['activeMenu' => 'floors'])

@section('title')
    {{ env('APP_NAME') }}
@endsection

@section('page_title')
    Add Floor
@endsection

@section('content')
    <div class="content">
        <div class="block block-rounded">
            <div class="block-header block-header-default">
                <h3 class="block-title">Add Floor</h3>
            </div>
            <div class="block-content fs-sm data-content">
                <form class="mb-4" action="{{ route('floors.store') }}" method="POST" autocomplete="off">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 col-sm-12 mb-4">
                            <label class="form-label" for="building_id">Building<span class="text-danger">*</span></label>
                            <select class="form-control select2" id="building_id" name="building_id" required>
                                <option value="">Select Building</option>
                                @foreach($buildings as $building)
                                    <option value="{{ $building->id }}">{{ $building->site_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 col-sm-12 mb-4">
                            <label class="form-label" for="agreement_id">Agreement</label>
                            <select class="form-control select2" id="agreement_id" name="agreement_id">
                                <option value="">Select Agreement</option>
                                @foreach($agreements as $agreement)
                                    <option value="{{ $agreement->id }}">{{ $agreement->agreement_ref_no }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 col-sm-12 mb-4">
                            <label class="form-label" for="floor_label">Floor Label</label>
                            <input type="text" class="form-control" id="floor_label" name="floor_label" value="{{ old('floor_label') }}">
                        </div>
                        <div class="col-md-6 col-sm-12 mb-4">
                            <label class="form-label" for="floor_area_sft">Floor Area (sft)</label>
                            <input type="number" step="0.01" class="form-control" id="floor_area_sft" name="floor_area_sft" value="{{ old('floor_area_sft') }}">
                        </div>
                        <div class="col-md-6 col-sm-12 mb-4">
                            <label class="form-label" for="premises_type">Premises Type</label>
                            <input type="text" class="form-control" id="premises_type" name="premises_type" value="{{ old('premises_type') }}">
                        </div>
                        <div class="col-md-6 col-sm-12 mb-4">
                            <label class="form-label" for="car_parking">Car Parking</label>
                            <input type="number" class="form-control" id="car_parking" name="car_parking" value="{{ old('car_parking') }}">
                        </div>
                        <div class="col-md-6 col-sm-12 mb-4">
                            <label class="form-label" for="dg_space_sft">DG Space (sft)</label>
                            <input type="number" step="0.01" class="form-control" id="dg_space_sft" name="dg_space_sft" value="{{ old('dg_space_sft') }}">
                        </div>
                        <div class="col-md-6 col-sm-12 mb-4">
                            <label class="form-label" for="store_space_sft">Store Space (sft)</label>
                            <input type="number" step="0.01" class="form-control" id="store_space_sft" name="store_space_sft" value="{{ old('store_space_sft') }}">
                        </div>
                        <div class="col-md-6 col-sm-12 mb-4">
                            <label class="form-label" for="project_name">Project Name</label>
                            <input type="text" class="form-control" id="project_name" name="project_name" value="{{ old('project_name') }}">
                        </div>
                        <div class="col-md-6 col-sm-12 mb-4">
                            <label class="form-label" for="status">Status</label>
                            <select class="form-control select2" id="status" name="status">
                                <option value="Active" {{ old('status') == 'Active' ? 'selected' : '' }}>Active</option>
                                <option value="Cancelled" {{ old('status') == 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Save</button>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        /* Fix select2 border to match Bootstrap form-control */
        .select2-container--default .select2-selection--single {
            border: 1.5px solid #495057 !important;
            border-radius: 0.375rem !important;
            height: 38px !important;
            padding: 0.375rem 0.75rem !important;
            background-color: #fff !important;
            box-shadow: none !important;
            transition: border-color .15s ease-in-out,box-shadow .15s ease-in-out !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 26px !important;
            color: #212529 !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px !important;
            right: 10px !important;
        }
        .select2-container--default.select2-container--focus .select2-selection--single {
            border-color: #86b7fe !important;
            outline: 0 !important;
            box-shadow: 0 0 0 0.25rem rgba(13,110,253,.25) !important;
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.select2').select2({
                width: '100%',
                theme: 'bootstrap-5'
            });
        });
    </script>
@endsection
