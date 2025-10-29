@extends('Partials.app', ['activeMenu' => 'floors'])

@section('title')
    {{ config('app.name') }} 
@endsection

@section('page_title')
    Edit Floor
@endsection

@section('content')
    <div class="content">
        <div class="block block-rounded">
            <div class="block-header block-header-default">
                <h3 class="block-title">Edit Floor</h3>
            </div>
            <div class="block-content fs-sm data-content">
                <form class="mb-4" action="{{ route('floors.update', $floor->id) }}" method="POST" autocomplete="off">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-md-6 col-sm-12 mb-4">
                            <label class="form-label" for="building_id">Building<span class="text-danger">*</span></label>
                            <select class="form-control" id="building_id" name="building_id" required>
                                <option value="">Select Building</option>
                                @foreach($buildings as $building)
                                    <option value="{{ $building->id }}" {{ $floor->building_id == $building->id ? 'selected' : '' }}>{{ $building->code }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 col-sm-12 mb-4">
                            <label class="form-label" for="agreement_id">Agreement</label>
                            <select class="form-control" id="agreement_id" name="agreement_id">
                                <option value="">Select Agreement</option>
                                @foreach($agreements as $agreement)
                                    <option value="{{ $agreement->id }}" {{ $floor->agreement_id == $agreement->id ? 'selected' : '' }}>{{ $agreement->agreement_ref_no }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 col-sm-12 mb-4">
                            <label class="form-label" for="floor_label">Floor Label</label>
                            <input type="text" class="form-control" id="floor_label" name="floor_label" value="{{ old('floor_label', $floor->floor_label) }}">
                        </div>
                        <div class="col-md-6 col-sm-12 mb-4">
                            <label class="form-label" for="floor_area_sft">Floor Area (sft)</label>
                            <input type="number" step="0.01" class="form-control" id="floor_area_sft" name="floor_area_sft" value="{{ old('floor_area_sft', $floor->floor_area_sft) }}">
                        </div>
                        <div class="col-md-6 col-sm-12 mb-4">
                            <label class="form-label" for="premises_type">Premises Type</label>
                            <input type="text" class="form-control" id="premises_type" name="premises_type" value="{{ old('premises_type', $floor->premises_type) }}">
                        </div>
                        <div class="col-md-6 col-sm-12 mb-4">
                            <label class="form-label" for="car_parking">Car Parking</label>
                            <input type="number" class="form-control" id="car_parking" name="car_parking" value="{{ old('car_parking', $floor->car_parking) }}">
                        </div>
                        <div class="col-md-6 col-sm-12 mb-4">
                            <label class="form-label" for="dg_space_sft">DG Space (sft)</label>
                            <input type="number" step="0.01" class="form-control" id="dg_space_sft" name="dg_space_sft" value="{{ old('dg_space_sft', $floor->dg_space_sft) }}">
                        </div>
                        <div class="col-md-6 col-sm-12 mb-4">
                            <label class="form-label" for="store_space_sft">Store Space (sft)</label>
                            <input type="number" step="0.01" class="form-control" id="store_space_sft" name="store_space_sft" value="{{ old('store_space_sft', $floor->store_space_sft) }}">
                        </div>
                        <div class="col-md-6 col-sm-12 mb-4">
                            <label class="form-label" for="project_name">Project Name</label>
                            <input type="text" class="form-control" id="project_name" name="project_name" value="{{ old('project_name', $floor->project_name) }}">
                        </div>
                        <div class="col-md-6 col-sm-12 mb-4">
                            <label class="form-label" for="status">Status</label>
                            <input type="text" class="form-control" id="status" name="status" value="{{ old('status', $floor->status) }}">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Update</button>
                </form>
            </div>
        </div>
    </div>
@endsection
