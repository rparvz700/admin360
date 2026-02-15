<div class="mb-3">
    <label class="form-label" for="vehicle_id">Vehicle <span class="text-danger">*</span></label>
    <select class="form-select" id="vehicle_id" name="vehicle_id" required>
        <option value="">Select Vehicle</option>
        @foreach($vehicles as $vehicle)
            <option value="{{ $vehicle->id }}" {{ old('vehicle_id') == $vehicle->id ? 'selected' : '' }}>
                {{ $vehicle->registration_number }} - {{ $vehicle->brand }} {{ $vehicle->model }}
            </option>
        @endforeach
    </select>
</div>

<div class="mb-3">
    <label class="form-label" for="log_type">Log Type <span class="text-danger">*</span></label>
    <select class="form-select" id="log_type" name="log_type" required>
        <option value="">Select Type</option>
        <option value="meter_reading" {{ old('log_type') == 'meter_reading' ? 'selected' : '' }}>Meter Reading</option>
        <option value="assignment" {{ old('log_type') == 'assignment' ? 'selected' : '' }}>Assignment</option>
        <option value="status_change" {{ old('log_type') == 'status_change' ? 'selected' : '' }}>Status Change</option>
    </select>
    <small class="form-text text-muted">Select the type of operational log</small>
</div>

<!-- Meter Reading Fields (shown when log_type = meter_reading) -->
<div id="meter_reading_fields" style="display: none;">
    <div class="mb-3">
        <label class="form-label" for="meter_reading">Meter Reading (KM) <span class="text-danger">*</span></label>
        <input type="number" class="form-control" id="meter_reading" name="meter_reading" 
               value="{{ old('meter_reading') }}" min="0">
        <small class="form-text text-muted">Current odometer reading</small>
    </div>
</div>

<!-- Assignment Fields (shown when log_type = assignment) -->
<div id="assignment_fields" style="display: none;">
    <div class="mb-3">
        <label class="form-label" for="assigned_department">Assigned Department <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="assigned_department" name="assigned_department" 
               value="{{ old('assigned_department') }}">
    </div>

    <div class="mb-3">
        <label class="form-label" for="assigned_user_id">Assigned User <span class="text-danger">*</span></label>
        <select class="form-select" id="assigned_user_id" name="assigned_user_id">
            <option value="">Select User</option>
            @foreach($users as $user)
                <option value="{{ $user->id }}" {{ old('assigned_user_id') == $user->id ? 'selected' : '' }}>
                    {{ $user->name }}
                </option>
            @endforeach
        </select>
    </div>
</div>

<!-- Status Change Fields (shown when log_type = status_change) -->
<div id="status_change_fields" style="display: none;">
    <div class="mb-3">
        <label class="form-label" for="vehicle_status">Vehicle Status <span class="text-danger">*</span></label>
        <select class="form-select" id="vehicle_status" name="vehicle_status">
            <option value="">Select Status</option>
            <option value="active" {{ old('vehicle_status') == 'active' ? 'selected' : '' }}>Active</option>
            <option value="inactive" {{ old('vehicle_status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
            <option value="under_maintenance" {{ old('vehicle_status') == 'under_maintenance' ? 'selected' : '' }}>Under Maintenance</option>
            <option value="sold" {{ old('vehicle_status') == 'sold' ? 'selected' : '' }}>Sold</option>
            <option value="scrapped" {{ old('vehicle_status') == 'scrapped' ? 'selected' : '' }}>Scrapped</option>
        </select>
    </div>
</div>

<div class="mb-3">
    <label class="form-label" for="remarks">Remarks</label>
    <textarea class="form-control" id="remarks" name="remarks" rows="3">{{ old('remarks') }}</textarea>
    <small class="form-text text-muted">Additional notes or comments</small>
</div>

@section('scripts')
<script src="{{ asset('js/lib/jquery.min.js') }}"></script>
<script>
$(document).ready(function() {
    // Show/hide fields based on log type
    function toggleFields() {
        var logType = $('#log_type').val();
        
        // Hide all conditional fields
        $('#meter_reading_fields').hide();
        $('#assignment_fields').hide();
        $('#status_change_fields').hide();
        
        // Remove required attribute from all conditional fields
        $('#meter_reading').removeAttr('required');
        $('#assigned_department').removeAttr('required');
        $('#assigned_user_id').removeAttr('required');
        $('#vehicle_status').removeAttr('required');
        
        // Show relevant fields and add required attribute
        if (logType === 'meter_reading') {
            $('#meter_reading_fields').show();
            $('#meter_reading').attr('required', 'required');
        } else if (logType === 'assignment') {
            $('#assignment_fields').show();
            $('#assigned_department').attr('required', 'required');
            $('#assigned_user_id').attr('required', 'required');
        } else if (logType === 'status_change') {
            $('#status_change_fields').show();
            $('#vehicle_status').attr('required', 'required');
        }
    }
    
    // Trigger on page load
    toggleFields();
    
    // Trigger on log type change
    $('#log_type').change(function() {
        toggleFields();
    });
});
</script>
@endsection