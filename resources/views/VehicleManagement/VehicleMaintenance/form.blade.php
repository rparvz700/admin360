<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label" for="vehicle_id">Vehicle <span class="text-danger">*</span></label>
        <select class="form-select" id="vehicle_id" name="vehicle_id" required>
            <option value="">Select Vehicle</option>
            @foreach($vehicles as $vehicle)
                <option value="{{ $vehicle->id }}" {{ old('vehicle_id', $maintenance->vehicle_id ?? '') == $vehicle->id ? 'selected' : '' }}>
                    {{ $vehicle->registration_number }} - {{ $vehicle->brand }} {{ $vehicle->model }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label" for="maintenance_type">Maintenance Type <span class="text-danger">*</span></label>
        <select class="form-select" id="maintenance_type" name="maintenance_type" required>
            <option value="">Select Type</option>
            <option value="routine" {{ old('maintenance_type', $maintenance->maintenance_type ?? '') == 'routine' ? 'selected' : '' }}>Routine</option>
            <option value="breakdown" {{ old('maintenance_type', $maintenance->maintenance_type ?? '') == 'breakdown' ? 'selected' : '' }}>Breakdown</option>
            <option value="accident" {{ old('maintenance_type', $maintenance->maintenance_type ?? '') == 'accident' ? 'selected' : '' }}>Accident</option>
            <option value="inspection" {{ old('maintenance_type', $maintenance->maintenance_type ?? '') == 'inspection' ? 'selected' : '' }}>Inspection</option>
        </select>
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label" for="vendor_id">Vendor <span class="text-danger">*</span></label>
        <select class="form-select" id="vendor_id" name="vendor_id" required>
            <option value="">Select Vendor</option>
            @foreach($vendors as $vendor)
                <option value="{{ $vendor->id }}" {{ old('vendor_id', $maintenance->vendor_id ?? '') == $vendor->id ? 'selected' : '' }}>
                    {{ $vendor->name }} ({{ $vendor->vendor_code }})
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label" for="status">Status <span class="text-danger">*</span></label>
        <select class="form-select" id="status" name="status" required>
            <option value="scheduled" {{ old('status', $maintenance->status ?? '') == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
            <option value="in_progress" {{ old('status', $maintenance->status ?? 'in_progress') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
            <option value="completed" {{ old('status', $maintenance->status ?? '') == 'completed' ? 'selected' : '' }}>Completed</option>
            <option value="cancelled" {{ old('status', $maintenance->status ?? '') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
        </select>
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-label" for="start_datetime">Start Date & Time <span class="text-danger">*</span></label>
        <input type="datetime-local" class="form-control" id="start_datetime" name="start_datetime" 
               value="{{ old('start_datetime', $maintenance && $maintenance->start_datetime ? $maintenance->start_datetime->format('Y-m-d\TH:i') : '') }}" required>
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-label" for="estimated_end_datetime">Estimated End Date & Time</label>
        <input type="datetime-local" class="form-control" id="estimated_end_datetime" name="estimated_end_datetime" 
               value="{{ old('estimated_end_datetime', $maintenance && $maintenance->estimated_end_datetime ? $maintenance->estimated_end_datetime->format('Y-m-d\TH:i') : '') }}">
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-label" for="actual_end_datetime">Actual End Date & Time</label>
        <input type="datetime-local" class="form-control" id="actual_end_datetime" name="actual_end_datetime" 
               value="{{ old('actual_end_datetime', $maintenance && $maintenance->actual_end_datetime ? $maintenance->actual_end_datetime->format('Y-m-d\TH:i') : '') }}">
    </div>

    <div class="col-md-12 mb-3">
        <label class="form-label" for="service_description">Service Description <span class="text-danger">*</span></label>
        <textarea class="form-control" id="service_description" name="service_description" rows="3" required>{{ old('service_description', $maintenance->service_description ?? '') }}</textarea>
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-label" for="meter_reading_at_service">Meter Reading (KM)</label>
        <input type="number" class="form-control" id="meter_reading_at_service" name="meter_reading_at_service" 
               value="{{ old('meter_reading_at_service', $maintenance->meter_reading_at_service ?? '') }}" min="0">
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-label" for="labor_cost">Labor Cost (৳)</label>
        <input type="number" step="0.01" class="form-control" id="labor_cost" name="labor_cost" 
               value="{{ old('labor_cost', $maintenance->labor_cost ?? 0) }}" min="0">
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-label" for="performed_by">Performed By</label>
        <input type="text" class="form-control" id="performed_by" name="performed_by" 
               value="{{ old('performed_by', $maintenance->performed_by ?? '') }}">
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label" for="next_service_due_date">Next Service Due Date</label>
        <input type="date" class="form-control" id="next_service_due_date" name="next_service_due_date" 
               value="{{ old('next_service_due_date', $maintenance && $maintenance->next_service_due_date ? $maintenance->next_service_due_date->format('Y-m-d') : '') }}">
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label" for="next_service_due_km">Next Service Due KM</label>
        <input type="number" class="form-control" id="next_service_due_km" name="next_service_due_km" 
               value="{{ old('next_service_due_km', $maintenance->next_service_due_km ?? '') }}" min="0">
    </div>

    <div class="col-md-12 mb-3">
        <label class="form-label" for="remarks">Remarks</label>
        <textarea class="form-control" id="remarks" name="remarks" rows="2">{{ old('remarks', $maintenance->remarks ?? '') }}</textarea>
    </div>
</div>

<!-- Parts Section -->
<div class="mb-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Parts Used/Replaced</h5>
        <button type="button" class="btn btn-sm btn-success" id="addPartBtn">
            <i class="fa fa-plus"></i> Add Part
        </button>
    </div>
    <div id="partsContainer">
        <!-- Existing parts for edit mode -->
        @if(isset($maintenance) && $maintenance->maintenanceParts->count() > 0)
            @foreach($maintenance->maintenanceParts as $index => $existingPart)
                <div class="part-row border rounded p-3 mb-3">
                    <div class="row">
                        <div class="col-md-4 mb-2">
                            <label class="form-label">Part <span class="text-danger">*</span></label>
                            <select class="form-select" name="parts[{{ $index }}][vehicle_part_id]" required>
                                <option value="">Select Part</option>
                                @foreach($parts as $part)
                                    <option value="{{ $part->id }}" {{ $existingPart->vehicle_part_id == $part->id ? 'selected' : '' }}>
                                        {{ $part->part_name }} ({{ $part->part_code }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 mb-2">
                            <label class="form-label">Action</label>
                            <select class="form-select" name="parts[{{ $index }}][action_type]">
                                <option value="replace" {{ $existingPart->action_type == 'replace' ? 'selected' : '' }}>Replace</option>
                                <option value="repair" {{ $existingPart->action_type == 'repair' ? 'selected' : '' }}>Repair</option>
                                <option value="service" {{ $existingPart->action_type == 'service' ? 'selected' : '' }}>Service</option>
                            </select>
                        </div>
                        <div class="col-md-2 mb-2">
                            <label class="form-label">Quantity</label>
                            <input type="number" class="form-control" name="parts[{{ $index }}][quantity]" value="{{ $existingPart->quantity }}" min="1">
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="form-label">Cost (৳)</label>
                            <input type="number" step="0.01" class="form-control part-cost" name="parts[{{ $index }}][part_cost]" value="{{ $existingPart->part_cost }}">
                        </div>
                        <div class="col-md-1 mb-2 d-flex align-items-end">
                            <button type="button" class="btn btn-sm btn-danger remove-part w-100">Remove</button>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label">Warranty (Months)</label>
                            <input type="number" class="form-control" name="parts[{{ $index }}][warranty_period_months]" value="{{ $existingPart->warranty_period_months }}">
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label">Next Replacement KM</label>
                            <input type="number" class="form-control" name="parts[{{ $index }}][next_replacement_due_km]" value="{{ $existingPart->next_replacement_due_km }}">
                        </div>
                    </div>
                </div>
            @endforeach
        @endif
    </div>
</div>

<!-- Part Row Template -->
<template id="partRowTemplate">
    <div class="part-row border rounded p-3 mb-3">
        <div class="row">
            <div class="col-md-4 mb-2">
                <label class="form-label">Part <span class="text-danger">*</span></label>
                <select class="form-select" name="parts[INDEX][vehicle_part_id]" required>
                    <option value="">Select Part</option>
                    @foreach($parts as $part)
                        <option value="{{ $part->id }}">{{ $part->part_name }} ({{ $part->part_code }})</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 mb-2">
                <label class="form-label">Action</label>
                <select class="form-select" name="parts[INDEX][action_type]">
                    <option value="replace">Replace</option>
                    <option value="repair">Repair</option>
                    <option value="service">Service</option>
                </select>
            </div>
            <div class="col-md-2 mb-2">
                <label class="form-label">Quantity</label>
                <input type="number" class="form-control" name="parts[INDEX][quantity]" value="1" min="1">
            </div>
            <div class="col-md-3 mb-2">
                <label class="form-label">Cost (৳)</label>
                <input type="number" step="0.01" class="form-control part-cost" name="parts[INDEX][part_cost]" value="0">
            </div>
            <div class="col-md-1 mb-2 d-flex align-items-end">
                <button type="button" class="btn btn-sm btn-danger remove-part w-100">Remove</button>
            </div>
            <div class="col-md-4 mb-2">
                <label class="form-label">Warranty (Months)</label>
                <input type="number" class="form-control" name="parts[INDEX][warranty_period_months]" value="0">
            </div>
            <div class="col-md-4 mb-2">
                <label class="form-label">Tyre Position</label>
                <input type="text" class="form-control" name="parts[INDEX][tyre_position]" placeholder="e.g., Front Left">
            </div>
            <div class="col-md-4 mb-2">
                <label class="form-label">Next Replacement KM</label>
                <input type="number" class="form-control" name="parts[INDEX][next_replacement_due_km]">
            </div>
        </div>
    </div>
</template>

@section('scripts')
<script src="{{ asset('js/lib/jquery.min.js') }}"></script>
<script>
$(document).ready(function() {
    let partIndex = {{ isset($maintenance) && $maintenance->maintenanceParts->count() > 0 ? $maintenance->maintenanceParts->count() : 0 }};

    // Add part row
    $('#addPartBtn').click(function() {
        let template = $('#partRowTemplate').html();
        template = template.replace(/INDEX/g, partIndex);
        $('#partsContainer').append(template);
        partIndex++;
    });

    // Remove part row
    $(document).on('click', '.remove-part', function() {
        $(this).closest('.part-row').remove();
    });
});
</script>
@endsection