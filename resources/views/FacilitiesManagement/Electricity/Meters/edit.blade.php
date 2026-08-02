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

                    <!-- Monthly Due Date (Day of Month) -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label" for="due_date_day">Monthly Due Date / Day</label>
                        <input type="number" min="1" max="31" class="form-control" id="due_date_day" name="due_date_day" value="{{ old('due_date_day', $meter->due_date_day) }}" placeholder="e.g. 15 (15th of every month)">
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

                    <!-- Meter Owner -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label" for="meter_owner">Meter Owner</label>
                        <select class="form-select select2" id="meter_owner" name="meter_owner" style="width: 100%;">
                            <option value="">Select Meter Owner</option>
                            <option value="Bangladesh Railway" {{ old('meter_owner', $meter->meter_owner) == 'Bangladesh Railway' ? 'selected' : '' }}>Bangladesh Railway</option>
                            <option value="House Owner"        {{ old('meter_owner', $meter->meter_owner) == 'House Owner' ? 'selected' : '' }}>House Owner</option>
                            <option value="SComm"              {{ old('meter_owner', $meter->meter_owner) == 'SComm' ? 'selected' : '' }}>SComm</option>
                        </select>
                    </div>

                    <!-- Authority Name -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label" for="authority_name">Authority Name</label>
                        <input type="text" class="form-control" id="authority_name" name="authority_name" value="{{ old('authority_name', $meter->authority_name ?? $meter->provider_name) }}" placeholder="e.g. DESCO, DPDC, NESCO, BREB">
                    </div>

                    <!-- Payment Process -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label" for="payment_process">Payment Process</label>
                        <select class="form-select select2" id="payment_process" name="payment_process" style="width: 100%;">
                            <option value="">Select Payment Process</option>
                            <option value="bKash"        {{ old('payment_process', $meter->payment_process) == 'bKash' ? 'selected' : '' }}>bKash</option>
                            <option value="Bank Challan" {{ old('payment_process', $meter->payment_process) == 'Bank Challan' ? 'selected' : '' }}>Bank Challan</option>
                            <option value="BEFTN"        {{ old('payment_process', $meter->payment_process) == 'BEFTN' ? 'selected' : '' }}>BEFTN</option>
                        </select>
                    </div>

                    <!-- Site / Building -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label" for="building_id">Site / Building <span class="text-danger">*</span></label>
                        <select class="form-select select2" id="building_id" name="building_id" required style="width: 100%;">
                            <option value="">Select Site / Building</option>
                            @foreach($buildings as $building)
                                <option value="{{ $building->id }}" {{ old('building_id', $meter->building_id) == $building->id ? 'selected' : '' }}>
                                    {{ $building->site_name }} {{ ($building->code ?? $building->site_code) ? "(" . ($building->code ?? $building->site_code) . ")" : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Floor (optional) -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label" for="floor_ids">Floor (Optional)</label>
                        <select class="form-select select2" id="floor_ids" name="floor_ids[]" multiple style="width: 100%;" data-placeholder="Select Floors (Optional)">
                        </select>
                    </div>

                    <!-- Vendor / House Owner -->
                    <div class="col-md-4 mb-3">
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
                    <div class="col-md-4 mb-3">
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
            var allFloors = @json($floors->map(function($f) {
                return [
                    'id' => $f->id,
                    'building_id' => $f->building_id,
                    'floor_label' => $f->floor_label
                ];
            }));

            $('.select2').select2({
                width: '100%'
            });

            function updateFloorOptions(selectedBuildingId, selectedFloorIds) {
                var $floorSelect = $('#floor_ids');
                $floorSelect.empty();

                if (selectedBuildingId) {
                    var filtered = allFloors.filter(function(f) {
                        return String(f.building_id) === String(selectedBuildingId);
                    });

                    if (filtered.length > 0) {
                        filtered.forEach(function(f) {
                            var isSelected = selectedFloorIds && selectedFloorIds.map(String).indexOf(String(f.id)) !== -1;
                            $floorSelect.append(new Option(f.floor_label, f.id, isSelected, isSelected));
                        });
                    }
                }

                $floorSelect.trigger('change.select2');
            }

            $('#building_id').on('change', function() {
                updateFloorOptions($(this).val(), $('#floor_ids').val());
            });

            @php
                $initialFloorIds = (array) old('floor_ids', $meter->floors->pluck('id')->toArray());
                if (empty($initialFloorIds) && $meter->floor_id) {
                    $initialFloorIds = [$meter->floor_id];
                }
            @endphp

            var initialBuilding = $('#building_id').val();
            var initialFloors = @json($initialFloorIds);
            updateFloorOptions(initialBuilding, initialFloors);
        });
    </script>
@endsection
