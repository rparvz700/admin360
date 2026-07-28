@extends('Partials.app', ['activeMenu' => 'electricity-meters'])

@section('title') Edit Electricity Meter @endsection

@section('styles')
    <link rel="stylesheet" href="{{ asset('js/plugins/select2/css/select2.min.css') }}">
@endsection

@section('content')
<div class="content">
    <div class="block block-rounded">
        <div class="block-header block-header-default">
            <h3 class="block-title"><i class="fa fa-pencil-alt text-primary me-2"></i> Edit Electricity Meter: {{ $meter->meter_number }}</h3>
            <a href="{{ route('electricity.meters.index') }}" class="btn btn-secondary btn-sm float-end">Back to List</a>
        </div>
        <div class="block-content">
            <form action="{{ route('electricity.meters.update', $meter->id) }}" method="POST" autocomplete="off">
                @csrf
                @method('PUT')
                <div class="row">
                    <!-- Meter Number -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label" for="meter_number">Meter Number <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="meter_number" name="meter_number" value="{{ old('meter_number', $meter->meter_number) }}" required>
                    </div>

                    <!-- Consumer / Account No -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label" for="consumer_no">Consumer / Account No</label>
                        <input type="text" class="form-control" id="consumer_no" name="consumer_no" value="{{ old('consumer_no', $meter->consumer_no) }}">
                    </div>

                    <!-- Meter Type -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label" for="meter_type">Meter Type <span class="text-danger">*</span></label>
                        <select class="form-select select2" id="meter_type" name="meter_type" required style="width: 100%;">
                            <option value="postpaid_main" {{ old('meter_type', $meter->meter_type) == 'postpaid_main' ? 'selected' : '' }}>Postpaid Main Meter</option>
                            <option value="postpaid_sub"  {{ old('meter_type', $meter->meter_type) == 'postpaid_sub' ? 'selected' : '' }}>Postpaid Sub-Meter (House Owner)</option>
                            <option value="prepaid"       {{ old('meter_type', $meter->meter_type) == 'prepaid' ? 'selected' : '' }}>Prepaid Meter</option>
                        </select>
                    </div>

                    <!-- Utility Provider -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label" for="provider_name">Utility Provider</label>
                        <input type="text" class="form-control" id="provider_name" name="provider_name" value="{{ old('provider_name', $meter->provider_name) }}">
                    </div>

                    <!-- Site / Building -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label" for="building_id">Site / Building <span class="text-danger">*</span></label>
                        <select class="form-select select2" id="building_id" name="building_id" required style="width: 100%;">
                            @foreach($buildings as $building)
                                <option value="{{ $building->id }}" {{ old('building_id', $meter->building_id) == $building->id ? 'selected' : '' }}>
                                    {{ $building->site_name }} {{ ($building->code ?? $building->site_code) ? "(" . ($building->code ?? $building->site_code) . ")" : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Floor -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label" for="floor_id">Floor (Optional)</label>
                        <select class="form-select select2" id="floor_id" name="floor_id" style="width: 100%;">
                            <option value="">Select Floor</option>
                            @foreach($floors as $floor)
                                <option value="{{ $floor->id }}" {{ old('floor_id', $meter->floor_id) == $floor->id ? 'selected' : '' }}>
                                    {{ $floor->floor_label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Vendor / House Owner -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="vendor_id">Vendor / House Owner (Optional)</label>
                        <select class="form-select select2" id="vendor_id" name="vendor_id" style="width: 100%;">
                            <option value="">Select Vendor / House Owner</option>
                            @foreach($vendors as $vendor)
                                <option value="{{ $vendor->id }}" {{ old('vendor_id', $meter->vendor_id) == $vendor->id ? 'selected' : '' }}>
                                    {{ $vendor->name }} {{ $vendor->vendor_code ? "({$vendor->vendor_code})" : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Sanctioned Load (KW) -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="sanctioned_load_kw">Sanctioned Load (KW)</label>
                        <input type="number" step="0.01" class="form-control" id="sanctioned_load_kw" name="sanctioned_load_kw" value="{{ old('sanctioned_load_kw', $meter->sanctioned_load_kw) }}">
                    </div>

                    <!-- Location Notes -->
                    <div class="col-md-12 mb-3">
                        <label class="form-label" for="meter_location_notes">Meter Location Notes</label>
                        <input type="text" class="form-control" id="meter_location_notes" name="meter_location_notes" value="{{ old('meter_location_notes', $meter->meter_location_notes) }}">
                    </div>

                    <div class="col-md-12 mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ $meter->is_active ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">Active Meter</label>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <button type="submit" class="btn btn-primary"><i class="fa fa-save me-1"></i> Update Meter</button>
                    <a href="{{ route('electricity.meters.index') }}" class="btn btn-secondary ms-2">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    <script src="{{ asset('js/plugins/select2/js/select2.full.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            $('.select2').select2({
                width: '100%'
            });
        });
    </script>
@endsection
