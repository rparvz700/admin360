<!-- Assignment Modal -->
<div class="modal fade" id="assignmentModal" tabindex="-1" aria-labelledby="assignmentModalLabel" aria-hidden="true"
    data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="block block-rounded block-transparent mb-0">
                <div class="block-header bg-primary py-3">
                    <h3 class="block-title text-white fs-sm fw-bold">
                        <i class="fa fa-car-side me-1"></i> Assign Vehicle & Driver Dashboard
                    </h3>
                    <div class="block-options">
                        <button type="button" class="btn-block-option text-white" data-bs-dismiss="modal" aria-label="Close">
                            <i class="fa fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="block-content p-4">
                    <!-- Ticket Info Summary -->
                    <div class="alert alert-info border-start border-4 border-info mb-4" id="ticketInfoSummary">
                        <div class="row g-2 align-items-center">
                            <div class="col-sm-6 col-md-3">
                                <span class="fs-xs text-uppercase text-muted d-block">Ticket #</span>
                                <strong id="modalTicketNumber" class="fs-sm"></strong>
                            </div>
                            <div class="col-sm-6 col-md-3">
                                <span class="fs-xs text-uppercase text-muted d-block">Start Time</span>
                                <strong id="modalStartTime" class="fs-sm"></strong>
                            </div>
                            <div class="col-sm-6 col-md-3">
                                <span class="fs-xs text-uppercase text-muted d-block">End Time</span>
                                <strong id="modalEndTime" class="fs-sm"></strong>
                            </div>
                            <div class="col-sm-6 col-md-3">
                                <span class="fs-xs text-uppercase text-muted d-block">Passenger Count</span>
                                <strong id="modalPassengers" class="fs-sm"></strong>
                            </div>
                        </div>
                    </div>

                    <!-- Selection Summary -->
                    <div class="row mb-4" id="selectionSummary" style="display: none;">
                        <div class="col-md-12">
                            <div class="alert alert-success border-start border-4 border-success mb-0">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <span class="fs-xs text-uppercase fw-bold text-success d-block"><i class="fa fa-car me-1"></i> Selected Vehicle:</span>
                                        <div id="selectedVehicleInfo" class="mt-1 fs-sm"></div>
                                    </div>
                                    <div class="col-md-6">
                                        <span class="fs-xs text-uppercase fw-bold text-success d-block"><i class="fa fa-user-tie me-1"></i> Selected Driver:</span>
                                        <div id="selectedDriverInfo" class="mt-1 fs-sm"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabs for Vehicles and Drivers -->
                    <ul class="nav nav-tabs nav-tabs-alt mb-3" id="assignmentTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="vehicles-tab" data-bs-toggle="tab"
                                data-bs-target="#vehicles" type="button" role="tab">
                                <i class="fa fa-car me-1"></i> Vehicles <span class="badge bg-primary ms-1" id="vehicleCount">0</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="drivers-tab" data-bs-toggle="tab" data-bs-target="#drivers"
                                type="button" role="tab">
                                <i class="fa fa-user me-1"></i> Drivers <span class="badge bg-primary ms-1" id="driverCount">0</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="manual-tab" data-bs-toggle="tab" data-bs-target="#manual"
                                type="button" role="tab">
                                <i class="fa fa-keyboard me-1"></i> Manual / Ad-hoc Assignment
                            </button>
                        </li>
                    </ul>

                    <!-- Tab Content -->
                    <div class="tab-content" id="assignmentTabContent">
                        <!-- Vehicles Tab -->
                        <div class="tab-pane fade show active" id="vehicles" role="tabpanel">
                            <div class="row g-2 mb-3">
                                <div class="col-md-6">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text"><i class="fa fa-search"></i></span>
                                        <input type="text" class="form-control form-control-sm" id="vehicleSearch"
                                            placeholder="Search by registration, brand, model...">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <select class="form-select form-select-sm" id="vehicleStatusFilter">
                                        <option value="">All Statuses</option>
                                        <option value="available">Available</option>
                                        <option value="on_assignment">On Assignment</option>
                                        <option value="in_maintenance">In Maintenance</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <select class="form-select form-select-sm" id="vehicleOwnershipFilter">
                                        <option value="">All Ownerships</option>
                                        <option value="Owned">Owned</option>
                                        <option value="Rented">Rented</option>
                                    </select>
                                </div>
                            </div>

                            <div id="vehiclesGrid" class="row g-3" style="max-height: 480px; overflow-y: auto;">
                                <div class="col-12 text-center py-5">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Drivers Tab -->
                        <div class="tab-pane fade" id="drivers" role="tabpanel">
                            <div class="row g-2 mb-3">
                                <div class="col-md-6">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text"><i class="fa fa-search"></i></span>
                                        <input type="text" class="form-control form-control-sm" id="driverSearch"
                                            placeholder="Search by driver name, phone...">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <select class="form-select form-select-sm" id="driverStatusFilter">
                                        <option value="">All Statuses</option>
                                        <option value="available">Available</option>
                                        <option value="on_assignment">On Assignment</option>
                                        <option value="on_leave">On Leave</option>
                                        <option value="sick">Sick</option>
                                        <option value="unavailable">Unavailable</option>
                                    </select>
                                </div>
                            </div>

                            <div id="driversGrid" class="row g-3" style="max-height: 480px; overflow-y: auto;">
                                <div class="col-12 text-center py-5">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Manual Assignment Tab -->
                        <div class="tab-pane fade" id="manual" role="tabpanel">
                            <div class="alert alert-info py-2 fs-xs mb-3">
                                <i class="fa fa-info-circle me-1"></i> Use this tab to manually enter ad-hoc rented vehicle and driver details that are not in the database.
                            </div>

                            <div class="row g-3">
                                <!-- Manual Vehicle Entry -->
                                <div class="col-md-6">
                                    <div class="block block-rounded block-bordered h-100 mb-0">
                                        <div class="block-header bg-body-light py-2">
                                            <h4 class="block-title fs-xs fw-bold text-primary text-uppercase">
                                                <i class="fa fa-car me-1"></i> Ad-hoc Vehicle Details
                                            </h4>
                                        </div>
                                        <div class="block-content fs-sm pb-3">
                                            <div class="mb-2">
                                                <label class="form-label fs-xs fw-semibold">Vehicle Type <span class="text-danger">*</span></label>
                                                <select class="form-select form-select-sm" id="manual_vehicle_type"
                                                    name="manual_vehicle_type">
                                                    <option value="">Select Type</option>
                                                    @foreach ($vehicleTypes as $type)
                                                        <option value="{{ $type->id }}">{{ $type->type_name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label fs-xs fw-semibold">Registration Number <span class="text-danger">*</span></label>
                                                <input type="text" id="manual_registration" class="form-control form-control-sm"
                                                    placeholder="e.g., DHA-12345">
                                            </div>
                                            <div class="row g-2 mb-2">
                                                <div class="col-6">
                                                    <label class="form-label fs-xs fw-semibold">Brand <span class="text-danger">*</span></label>
                                                    <input type="text" id="manual_brand" class="form-control form-control-sm"
                                                        placeholder="e.g., Toyota">
                                                </div>
                                                <div class="col-6">
                                                    <label class="form-label fs-xs fw-semibold">Model <span class="text-danger">*</span></label>
                                                    <input type="text" id="manual_model" class="form-control form-control-sm"
                                                        placeholder="e.g., Hiace">
                                                </div>
                                            </div>
                                            <div class="row g-2 mb-2">
                                                <div class="col-6">
                                                    <label class="form-label fs-xs fw-semibold">Color</label>
                                                    <input type="text" id="manual_color" class="form-control form-control-sm"
                                                        placeholder="e.g., White">
                                                </div>
                                                <div class="col-6">
                                                    <label class="form-label fs-xs fw-semibold">Seating Capacity <span class="text-danger">*</span></label>
                                                    <input type="number" id="manual_seating" class="form-control form-control-sm"
                                                        min="1" value="4">
                                                </div>
                                            </div>
                                            <div class="row g-2 mb-2">
                                                <div class="col-6">
                                                    <label class="form-label fs-xs fw-semibold">Rental Type <span class="text-danger">*</span></label>
                                                    <select id="manual_rental_type" class="form-select form-select-sm">
                                                        <option value="daily">Daily Rental</option>
                                                        <option value="hourly">Hourly Rental</option>
                                                        <option value="trip">Per Trip</option>
                                                    </select>
                                                </div>
                                                <div class="col-6">
                                                    <label class="form-label fs-xs fw-semibold">Rental Cost (BDT)</label>
                                                    <input type="number" id="manual_rental_cost" class="form-control form-control-sm"
                                                        placeholder="Amount in BDT" step="0.01">
                                                </div>
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label fs-xs fw-semibold">Rental Vendor Company</label>
                                                <select class="form-select form-select-sm" id="manual_rental_company"
                                                    name="manual_rental_company">
                                                    <option value="">Select Rental Vendor</option>
                                                    @foreach ($vendors as $vendor)
                                                        <option value="{{ $vendor->id }}">
                                                            {{ $vendor->name }} ({{ $vendor->vendor_code }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Manual Driver Entry -->
                                <div class="col-md-6">
                                    <div class="block block-rounded block-bordered h-100 mb-0">
                                        <div class="block-header bg-body-light py-2">
                                            <h4 class="block-title fs-xs fw-bold text-success text-uppercase">
                                                <i class="fa fa-user-tie me-1"></i> Ad-hoc Driver Details
                                            </h4>
                                        </div>
                                        <div class="block-content fs-sm pb-3">
                                            <div class="mb-2">
                                                <label class="form-label fs-xs fw-semibold">Driver Name <span class="text-danger">*</span></label>
                                                <input type="text" id="manual_driver_name" class="form-control form-control-sm"
                                                    placeholder="Full Name">
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label fs-xs fw-semibold">Phone Number <span class="text-danger">*</span></label>
                                                <input type="text" id="manual_driver_phone" class="form-control form-control-sm"
                                                    placeholder="e.g., 01711-123456">
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label fs-xs fw-semibold">Email</label>
                                                <input type="email" id="manual_driver_email" class="form-control form-control-sm"
                                                    placeholder="driver@example.com">
                                            </div>
                                            <div class="row g-2 mb-2">
                                                <div class="col-6">
                                                    <label class="form-label fs-xs fw-semibold">NID Number</label>
                                                    <input type="text" id="manual_nid" class="form-control form-control-sm"
                                                        placeholder="National ID">
                                                </div>
                                                <div class="col-6">
                                                    <label class="form-label fs-xs fw-semibold">Blood Group</label>
                                                    <select id="manual_blood_group" class="form-select form-select-sm">
                                                        <option value="">Select</option>
                                                        <option value="A+">A+</option>
                                                        <option value="A-">A-</option>
                                                        <option value="B+">B+</option>
                                                        <option value="B-">B-</option>
                                                        <option value="O+">O+</option>
                                                        <option value="O-">O-</option>
                                                        <option value="AB+">AB+</option>
                                                        <option value="AB-">AB-</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label fs-xs fw-semibold">Emergency Contact</label>
                                                <input type="text" id="manual_emergency_contact" class="form-control form-control-sm"
                                                    placeholder="Emergency contact phone">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-3">
                                <label class="form-label fs-xs fw-semibold">Additional Assignment Notes</label>
                                <textarea id="manual_notes" class="form-control form-control-sm" rows="2"
                                    placeholder="Any additional information about this ad-hoc rental..."></textarea>
                            </div>

                            <div class="mt-3 d-flex gap-2">
                                <button type="button" class="btn btn-sm btn-primary" id="validateManualEntry">
                                    <i class="fa fa-check-circle me-1"></i> Validate & Preview
                                </button>
                                <button type="button" class="btn btn-sm btn-alt-secondary" id="clearManualForm">
                                    <i class="fa fa-eraser me-1"></i> Clear Form
                                </button>
                            </div>

                            <!-- Preview Section -->
                            <div id="manualPreviewSection" class="mt-3" style="display: none;">
                                <div class="alert alert-success py-2 fs-xs mb-2">
                                    <strong><i class="fa fa-check-circle me-1"></i> Ready to Assign:</strong> Please review the details below and click "Confirm Assignment" to proceed.
                                </div>
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <div class="p-3 bg-body-light rounded border border-primary fs-sm">
                                            <span class="fs-xs fw-bold text-primary text-uppercase d-block mb-1">Vehicle Preview</span>
                                            <div id="manualVehiclePreview"></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="p-3 bg-body-light rounded border border-success fs-sm">
                                            <span class="fs-xs fw-bold text-success text-uppercase d-block mb-1">Driver Preview</span>
                                            <div id="manualDriverPreview"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="block-content block-content-full block-content-sm text-end bg-body-light rounded-bottom">
                    <button type="button" class="btn btn-sm btn-alt-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-sm btn-primary" id="confirmAssignment" disabled>
                        <i class="fa fa-check me-1"></i> Confirm Assignment
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Vehicle Card Template -->
<template id="vehicleCardTemplate">
    <div class="col-md-6 col-lg-4 vehicle-card" data-vehicle-id="" data-status="" data-ownership="">
        <div class="block block-rounded block-bordered h-100 vehicle-item mb-0" style="cursor: pointer; transition: all 0.2s ease;">
            <div class="block-header bg-body-light py-2 d-flex justify-content-between align-items-center">
                <strong class="vehicle-registration fs-sm"></strong>
                <span class="badge vehicle-status-badge fs-xs"></span>
            </div>
            <div class="block-content p-3 fs-sm">
                <h6 class="vehicle-name fs-sm fw-bold mb-2 text-dark"></h6>
                <div class="vehicle-details">
                    <p class="mb-1 fs-xs text-muted">
                        <i class="fa fa-tag text-primary me-1"></i> <span class="vehicle-type text-dark"></span>
                    </p>
                    <p class="mb-1 fs-xs text-muted">
                        <i class="fa fa-users text-info me-1"></i> <span class="vehicle-capacity text-dark"></span> seats
                    </p>
                    <p class="mb-1 fs-xs text-muted">
                        <i class="fa fa-building text-warning me-1"></i> <span class="vehicle-ownership text-dark"></span>
                    </p>
                    <p class="mb-1 fs-xs text-muted">
                        <i class="fa fa-paint-brush text-secondary me-1"></i> <span class="vehicle-color text-dark"></span>
                    </p>
                </div>
                <div class="vehicle-assignment-info mt-2" style="display: none;">
                    <hr class="my-2">
                    <div class="alert alert-warning py-1 px-2 mb-0 fs-xs">
                        <div><strong>Current Assignment:</strong></div>
                        <div class="assignment-ticket"></div>
                        <div class="assignment-countdown"></div>
                    </div>
                </div>
                <div class="vehicle-match-indicator mt-2" style="display: none;">
                    <span class="badge bg-info fs-xs">
                        <i class="fa fa-check-circle me-1"></i> Matches Requirements
                    </span>
                </div>
            </div>
        </div>
    </div>
</template>

<!-- Driver Card Template -->
<template id="driverCardTemplate">
    <div class="col-md-6 col-lg-4 driver-card" data-driver-id="" data-status="">
        <div class="block block-rounded block-bordered h-100 driver-item mb-0" style="cursor: pointer; transition: all 0.2s ease;">
            <div class="block-header bg-body-light py-2 d-flex justify-content-between align-items-center">
                <strong class="driver-name fs-sm"></strong>
                <span class="badge driver-status-badge fs-xs"></span>
            </div>
            <div class="block-content p-3 fs-sm">
                <div class="d-flex align-items-start">
                    <div class="driver-avatar me-3">
                        <img class="driver-image rounded-circle" style="width: 48px; height: 48px; object-fit: cover;"
                            alt="Driver">
                    </div>
                    <div class="flex-grow-1">
                        <div class="driver-details">
                            <p class="mb-1 fs-xs text-muted">
                                <i class="fa fa-phone text-primary me-1"></i> <span class="driver-phone text-dark"></span>
                            </p>
                            <p class="mb-1 fs-xs text-muted">
                                <i class="fa fa-building text-info me-1"></i> <span class="driver-office-location text-dark"></span>
                            </p>
                            <p class="mb-1 fs-xs text-muted">
                                <i class="fa fa-id-badge text-secondary me-1"></i> <span class="driver-job-location text-dark"></span>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="driver-assignment-info mt-2" style="display: none;">
                    <hr class="my-2">
                    <div class="alert alert-warning py-1 px-2 mb-0 fs-xs">
                        <div><strong>Current Assignment:</strong></div>
                        <div class="assignment-ticket"></div>
                        <div class="assignment-countdown"></div>
                    </div>
                </div>
                <div class="driver-unavailable-info mt-2" style="display: none;">
                    <hr class="my-2">
                    <div class="alert alert-danger py-1 px-2 mb-0 fs-xs">
                        <div class="unavailable-message"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style>
    .vehicle-item.selected,
    .driver-item.selected {
        border: 2px solid #0d6efd !important;
        background-color: #f0f7ff !important;
        box-shadow: 0 4px 12px rgba(13, 110, 253, 0.15) !important;
    }

    .vehicle-item:hover,
    .driver-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
    }

    .vehicle-item.unavailable,
    .driver-item.unavailable {
        opacity: 0.55;
        cursor: not-allowed !important;
    }

    .countdown-timer {
        font-weight: bold;
        color: #dc3545;
    }
</style>

@section('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>

    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Assignment Modal JavaScript
        let selectedVehicle = null;
        let selectedDriver = null;
        let allVehicles = [];
        let allDrivers = [];
        let ticketData = null;
        let isManualAssignment = false;
        let manualVehicleData = null;
        let manualDriverData = null;
        let currentAssignedVehicleId = null; // NEW: Store currently assigned vehicle ID
        let currentAssignedDriverId = null; // NEW: Store currently assigned driver ID


        // Initialize modal - MODIFIED
        function initAssignmentModal(ticketId) {
            // Reset all state variables
            selectedVehicle = null;
            selectedDriver = null;
            isManualAssignment = false;
            manualVehicleData = null;
            manualDriverData = null;
            currentAssignedVehicleId = null; // Reset current assignment info
            currentAssignedDriverId = null; // Reset current assignment info

            // Reset UI elements
            $('#selectionSummary').hide();
            $('#confirmAssignment').prop('disabled', true).html(
                '<i class="fas fa-check"></i> Confirm Assignment'); // Ensure button is reset
            $('#manualPreviewSection').hide();

            // Clear previous selections in UI
            $('.vehicle-item').removeClass('selected');
            $('.driver-item').removeClass('selected');
            $('#selectedVehicleInfo').empty();
            $('#selectedDriverInfo').empty();

            // Clear manual form (if any values were left)
            clearManualForm();

            // Reset filters
            $('#vehicleSearch').val('');
            $('#vehicleStatusFilter').val('');
            $('#vehicleOwnershipFilter').val('');
            $('#driverSearch').val('');
            $('#driverStatusFilter').val('');

            // Load data
            loadAssignmentData(ticketId);
        }

        // Load vehicles and drivers data - MODIFIED
        function loadAssignmentData(ticketId) {
            $.ajax({
                url: '{{ route('admin.tickets.assignment.resources') }}',
                method: 'GET',
                data: {
                    ticket_id: ticketId
                },
                success: function(response) {
                    allVehicles = response.vehicles;
                    allDrivers = response.drivers;
                    ticketData = response.ticket;

                    // NEW: Store currently assigned IDs from ticket data for pre-selection
                    currentAssignedVehicleId = ticketData.assigned_vehicle_id;
                    currentAssignedDriverId = ticketData.assigned_driver_id;

                    // Update ticket info summary
                    updateTicketSummary(ticketData);

                    // Render grids
                    renderVehicles(allVehicles);
                    renderDrivers(allDrivers);

                    // Update counts
                    updateCounts();

                    // NEW: Pre-select if there's an existing assignment
                    if (currentAssignedVehicleId) {
                        const assignedVehicle = allVehicles.find(v => v.id === currentAssignedVehicleId);
                        if (assignedVehicle) {
                            selectVehicle(assignedVehicle);
                        }
                    }
                    if (currentAssignedDriverId) {
                        const assignedDriver = allDrivers.find(d => d.id === currentAssignedDriverId);
                        if (assignedDriver) {
                            selectDriver(assignedDriver);
                        }
                    }

                    // If both are pre-selected, enable confirm button (for reassigning existing)
                    if (currentAssignedVehicleId && currentAssignedDriverId) {
                        $('#confirmAssignment').prop('disabled', false);
                    }
                },
                error: function(xhr) {
                    console.error('Error loading data:', xhr);
                    alert('Failed to load vehicles and drivers. Please try again.');
                }
            });
        }

        // Update ticket info summary
        function updateTicketSummary(ticket) {
            $('#modalTicketNumber').text(ticket.ticket_number);
            $('#modalStartTime').text(moment(ticket.start_datetime).format('MMM D, Y HH:mm'));
            $('#modalEndTime').text(moment(ticket.end_datetime).format('MMM D, Y HH:mm'));
            $('#modalPassengers').text(ticket.passenger_count);
        }

        // Render vehicles
        function renderVehicles(vehicles) {
            const grid = $('#vehiclesGrid');
            grid.empty();

            if (vehicles.length === 0) {
                grid.html('<div class="col-12 text-center py-4"><p class="text-muted">No vehicles available</p></div>');
                return;
            }

            vehicles.forEach(vehicle => {
                const card = createVehicleCard(vehicle);
                grid.append(card);
            });
        }

        // Create vehicle card
        function createVehicleCard(vehicle) {
            const template = document.getElementById('vehicleCardTemplate');
            const clone = template.content.cloneNode(true);
            const card = $(clone);

            // Set data attributes
            card.find('.vehicle-card').attr({
                'data-vehicle-id': vehicle.id,
                'data-status': vehicle.status,
                'data-ownership': vehicle.ownership
            });

            // Set content
            card.find('.vehicle-registration').text(vehicle.registration_number);
            card.find('.vehicle-name').text(`${vehicle.brand} ${vehicle.model}`);
            card.find('.vehicle-type').text(vehicle.vehicle_type);
            card.find('.vehicle-capacity').text(vehicle.seating_capacity);
            card.find('.vehicle-ownership').text(vehicle.ownership);
            card.find('.vehicle-color').text(vehicle.color);

            // Set status badge
            const statusBadge = card.find('.vehicle-status-badge');
            statusBadge.text(vehicle.status_label)
                .removeClass()
                .addClass(`badge bg-${vehicle.status_color}`);

            // Show match indicator if matches requirements
            if (vehicle.matches_requirement) {
                card.find('.vehicle-match-indicator').show();
            }

            // Handle assignment info
            if (vehicle.current_assignment) {
                const assignmentInfo = card.find('.vehicle-assignment-info');
                assignmentInfo.show();
                assignmentInfo.find('.assignment-ticket').text(`Ticket: ${vehicle.current_assignment.ticket_number}`);

                const timeRemaining = vehicle.current_assignment.time_remaining;
                const countdownText = timeRemaining > 0 ?
                    `Available in: ${formatTimeRemaining(timeRemaining)}` :
                    'Should be available now';
                assignmentInfo.find('.assignment-countdown').html(`<span class="countdown-timer">${countdownText}</span>`);
            }

            // Make unavailable if not available
            if (!vehicle.is_available) {
                card.find('.vehicle-item').addClass('unavailable');
            }

            // If this is the currently assigned vehicle, pre-select it visually
            if (currentAssignedVehicleId === vehicle.id) {
                card.find('.vehicle-item').addClass('selected');
                // Ensure manual assignment flag is false if an existing vehicle is pre-selected
                isManualAssignment = false;
            }

            // Click handler
            card.find('.vehicle-item').on('click', function() {
                if (!vehicle.is_available) {
                    alert('This vehicle is not available for the selected time period.');
                    return;
                }
                selectVehicle(vehicle);
            });

            return card;
        }

        // Render drivers
        function renderDrivers(drivers) {
            const grid = $('#driversGrid');
            grid.empty();

            if (drivers.length === 0) {
                grid.html('<div class="col-12 text-center py-4"><p class="text-muted">No drivers available</p></div>');
                return;
            }

            drivers.forEach(driver => {
                const card = createDriverCard(driver);
                grid.append(card);
            });
        }

        // Create driver card
        function createDriverCard(driver) {
            const template = document.getElementById('driverCardTemplate');
            const clone = template.content.cloneNode(true);
            const card = $(clone);

            // Set data attributes
            card.find('.driver-card').attr({
                'data-driver-id': driver.id,
                'data-status': driver.status
            });

            // Set content
            card.find('.driver-name').text(driver.name);
            card.find('.driver-phone').text(driver.phone || 'N/A');
            card.find('.driver-office-location').text(driver.office_location || 'N/A');
            card.find('.driver-job-location').text(driver.job_location || 'N/A');

            // Set image
            const imagePath = driver.image_path ? `/storage/${driver.image_path}` :
                '{{ asset('/media/avatars/driver.png') }}';
            card.find('.driver-image').attr('src', imagePath);

            // Set status badge
            const statusBadge = card.find('.driver-status-badge');
            statusBadge.text(driver.status_label)
                .removeClass()
                .addClass(`badge bg-${driver.status_color}`);

            // Handle assignment info
            if (driver.current_assignment) {
                const assignmentInfo = card.find('.driver-assignment-info');
                assignmentInfo.show();
                assignmentInfo.find('.assignment-ticket').text(`Ticket: ${driver.current_assignment.ticket_number}`);

                const timeRemaining = driver.current_assignment.time_remaining;
                const countdownText = timeRemaining > 0 ?
                    `Available in: ${formatTimeRemaining(timeRemaining)}` :
                    'Should be available now';
                assignmentInfo.find('.assignment-countdown').html(`<span class="countdown-timer">${countdownText}</span>`);
            }

            // Handle unavailable info
            if (driver.unavailable_until && driver.status !== 'on_assignment') {
                const unavailableInfo = card.find('.driver-unavailable-info');
                unavailableInfo.show();
                const untilText = moment(driver.unavailable_until).format('MMM D, Y HH:mm');
                unavailableInfo.find('.unavailable-message').text(`Unavailable until: ${untilText}`);
            }

            // Make unavailable if not available
            if (!driver.is_available) {
                card.find('.driver-item').addClass('unavailable');
            }

            // If this is the currently assigned driver, pre-select it visually
            if (currentAssignedDriverId === driver.id) {
                card.find('.driver-item').addClass('selected');
                // Ensure manual assignment flag is false if an existing driver is pre-selected
                isManualAssignment = false;
            }

            // Click handler
            card.find('.driver-item').on('click', function() {
                if (!driver.is_available) {
                    alert('This driver is not available for the selected time period.');
                    return;
                }
                selectDriver(driver);
            });

            return card;
        }

        // Select vehicle - MODIFIED (to also set isManualAssignment to false if a system vehicle is selected)
        function selectVehicle(vehicle) {
            selectedVehicle = vehicle;
            isManualAssignment = false; // A system vehicle is selected, so it's not manual

            // Update UI
            $('.vehicle-item').removeClass('selected');
            $(`.vehicle-card[data-vehicle-id="${vehicle.id}"] .vehicle-item`).addClass('selected');

            // Update selection summary
            $('#selectedVehicleInfo').html(`
        <strong>${vehicle.brand} ${vehicle.model}</strong><br>
        ${vehicle.registration_number} | ${vehicle.vehicle_type} | ${vehicle.seating_capacity} seats
        ${vehicle.is_manual_entry ? '<br><span class="badge bg-warning">Ad-hoc Rental</span>' : ''}
    `);

            updateSelectionSummary();
        }

        // Select driver - MODIFIED (to also set isManualAssignment to false if a system driver is selected)
        function selectDriver(driver) {
            selectedDriver = driver;
            isManualAssignment = false; // A system driver is selected, so it's not manual

            // Update UI
            $('.driver-item').removeClass('selected');
            $(`.driver-card[data-driver-id="${driver.id}"] .driver-item`).addClass('selected');

            // Update selection summary
            $('#selectedDriverInfo').html(`
        <strong>${driver.name}</strong><br>
        ${driver.phone} | ${driver.employment_contract}
        ${driver.is_manual_entry ? '<br><span class="badge bg-warning">Ad-hoc Driver</span>' : ''}
    `);

            updateSelectionSummary();
        }

        // Update selection summary
        function updateSelectionSummary() {
            if (selectedVehicle && selectedDriver) {
                $('#selectionSummary').show();
                $('#confirmAssignment').prop('disabled', false);
            } else {
                // If one is selected but not the other, still show summary, but keep button disabled
                $('#selectionSummary').show();
                $('#confirmAssignment').prop('disabled', true);
            }
        }

        // Confirm assignment - MODIFIED
        function confirmAssignment() {
            // If manual tab is active but no data is validated, prevent confirmation
            if ($('#manual-tab').hasClass('active') && (!manualVehicleData || !manualDriverData)) {
                alert('Please validate manual entry details first.');
                return;
            }

            if (!selectedVehicle || !selectedDriver) {
                alert('Please select both a vehicle and a driver.');
                return;
            }

            // Show loading
            $('#confirmAssignment').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Assigning...');

            // Prepare data
            const requestData = {
                ticket_id: ticketData.id,
                is_manual: isManualAssignment
            };

            if (isManualAssignment) {
                // Manual assignment - send manual data placeholders
                requestData.vehicle_id = 'manual';
                requestData.driver_id = 'manual';
                requestData.manual_vehicle_data = {
                    vehicle_type: manualVehicleData.vehicle_type,
                    registration_number: manualVehicleData.registration_number,
                    brand: manualVehicleData.brand,
                    model: manualVehicleData.model,
                    color: manualVehicleData.color,
                    seating_capacity: manualVehicleData.seating_capacity,
                    rental_type: manualVehicleData.rental_type,
                    rental_cost: manualVehicleData.rental_cost,
                    rental_company: manualVehicleData.rental_company,
                    notes: $('#manual_notes').val().trim() || '' // Use live notes for final submission
                };
                requestData.manual_driver_data = {
                    name: manualDriverData.name,
                    phone: manualDriverData.phone,
                    email: manualDriverData.email,
                    license_number: manualDriverData.license_number,
                    license_expiry: manualDriverData.license_expiry,
                    nid: manualDriverData.nid,
                    blood_group: manualDriverData.blood_group,
                    emergency_contact: manualDriverData.emergency_contact,
                    notes: $('#manual_notes').val().trim() || '' // Use live notes for final submission
                };
            } else {
                // Regular assignment
                requestData.vehicle_id = selectedVehicle.id;
                requestData.driver_id = selectedDriver.id;
            }

            $.ajax({
                url: '{{ route('admin.tickets.assignment.assign') }}',
                method: 'POST', // Use POST, backend will handle update/create
                data: requestData,
                success: function(response) {
                    if (response.success) {
                        // NEW: Determine if it's a reassignment based on previous state
                        const action = (currentAssignedVehicleId || currentAssignedDriverId) ? 'reassigned' :
                            'assigned';
                        const message = isManualAssignment ?
                            `Ad-hoc rental vehicle and driver ${action} successfully!` :
                            `Vehicle and driver ${action} successfully!`;
                        alert(message);

                        $('#assignmentModal').modal('hide');
                        location.reload(); // Reload page to reflect changes
                    } else {
                        alert(response.message);
                        $('#confirmAssignment').prop('disabled', false).html(
                            '<i class="fas fa-check"></i> Confirm Assignment');
                    }
                },
                error: function(xhr) {
                    const message = xhr.responseJSON?.message ||
                        'Failed to assign vehicle and driver. Please try again.';
                    alert(message);
                    $('#confirmAssignment').prop('disabled', false).html(
                        '<i class="fas fa-check"></i> Confirm Assignment');
                }
            });
        }

        // Format time remaining
        function formatTimeRemaining(hours) {
            if (hours < 1) {
                return `${Math.round(hours * 60)} minutes`;
            } else if (hours < 24) {
                return `${Math.round(hours)} hours`;
            } else {
                return `${Math.round(hours / 24)} days`;
            }
        }

        // Update counts
        function updateCounts() {
            const availableVehicles = allVehicles.filter(v => v.is_available).length;
            const totalVehicles = allVehicles.length;
            $('#vehicleCount').text(`${availableVehicles}/${totalVehicles} Available`);

            const availableDrivers = allDrivers.filter(d => d.is_available).length;
            const totalDrivers = allDrivers.length;
            $('#driverCount').text(`${availableDrivers}/${totalDrivers} Available`);
        }

        // Manual Assignment Functions - MODIFIED
        function validateManualEntry() {
            // Validate vehicle fields
            const vehicleType = $('#manual_vehicle_type').val();
            const registration = $('#manual_registration').val().trim();
            const brand = $('#manual_brand').val().trim();
            const model = $('#manual_model').val().trim();
            const seating = $('#manual_seating').val();
            const rentalType = $('#manual_rental_type').val();

            // Validate driver fields
            const driverName = $('#manual_driver_name').val().trim();
            const driverPhone = $('#manual_driver_phone').val().trim();
            // const licenseNumber = $('#manual_license_number').val().trim();

            // Check required vehicle fields
            if (!vehicleType || !registration || !brand || !model || !seating || !rentalType) {
                alert('Please fill in all required vehicle fields (marked with *)');
                return;
            }

            // Check required driver fields
            if (!driverName || !driverPhone) {
                alert('Please fill in all required driver fields (marked with *)');
                return;
            }

            // Create manual vehicle data object
            manualVehicleData = {
                id: 'manual_vehicle_' + Date.now(), // Unique ID for selection purposes
                is_manual_entry: true, // Flag for manual entry
                vehicle_type: vehicleType,
                registration_number: registration,
                brand: brand,
                model: model,
                color: $('#manual_color').val().trim() || 'N/A',
                seating_capacity: seating,
                rental_type: rentalType,
                rental_cost: parseFloat($('#manual_rental_cost').val()) || 0, // Ensure it's a number
                rental_company: $('#manual_rental_company').val().trim() || 'N/A',
                ownership: 'Rented',
                is_available: true, // Manual entries are assumed available
                status: 'on_assignment', // They will be on assignment immediately
                status_label: 'On Assignment',
                status_color: 'info'
            };

            // Create manual driver data object
            manualDriverData = {
                id: 'manual_driver_' + Date.now(), // Unique ID for selection purposes
                is_manual_entry: true, // Flag for manual entry
                name: driverName,
                phone: driverPhone,
                email: $('#manual_driver_email').val().trim() || 'N/A',
                // license_number: licenseNumber,
                license_expiry: $('#manual_license_expiry').val() || 'N/A',
                nid: $('#manual_nid').val().trim() || 'N/A',
                blood_group: $('#manual_blood_group').val() || 'N/A',
                emergency_contact: $('#manual_emergency_contact').val().trim() || 'N/A',
                employment_contract: 'Ad-hoc',
                is_available: true, // Manual entries are assumed available
                status: 'on_assignment', // They will be on assignment immediately
                status_label: 'On Assignment',
                status_color: 'info'
            };

            // Add notes (shared for both manual vehicle and driver)
            const notes = $('#manual_notes').val().trim();
            if (notes) {
                manualVehicleData.notes = notes;
                manualDriverData.notes = notes;
            }

            // Set as selected
            selectedVehicle = manualVehicleData;
            selectedDriver = manualDriverData;
            isManualAssignment = true;

            // Show preview
            displayManualPreview();

            // Update selection summary
            $('#selectedVehicleInfo').html(`
        <strong>${manualVehicleData.brand} ${manualVehicleData.model}</strong><br>
        ${manualVehicleData.registration_number} | ${manualVehicleData.vehicle_type} | ${manualVehicleData.seating_capacity} seats<br>
        <span class="badge bg-warning">Ad-hoc Rental</span>
    `);

            $('#selectedDriverInfo').html(`
        <strong>${manualDriverData.name}</strong><br>
        ${manualDriverData.phone} | License: ${manualDriverData.license_number}<br>
        <span class="badge bg-warning">Ad-hoc Driver</span>
    `);

            updateSelectionSummary();
        }

        function displayManualPreview() {
            // Display vehicle preview
            $('#manualVehiclePreview').html(`
        <p><strong>Type:</strong> ${manualVehicleData.vehicle_type}</p>
        <p><strong>Registration:</strong> ${manualVehicleData.registration_number}</p>
        <p><strong>Brand & Model:</strong> ${manualVehicleData.brand} ${manualVehicleData.model}</p>
        <p><strong>Color:</strong> ${manualVehicleData.color}</p>
        <p><strong>Seating:</strong> ${manualVehicleData.seating_capacity} persons</p>
        <p><strong>Rental Type:</strong> ${manualVehicleData.rental_type}</p>
        ${manualVehicleData.rental_cost > 0 ? `<p><strong>Cost:</strong> BDT ${manualVehicleData.rental_cost}</p>` : ''}
        ${manualVehicleData.rental_company !== 'N/A' ? `<p><strong>Rental Company:</strong> ${manualVehicleData.rental_company}</p>` : ''}
    `);

            // Display driver preview
            $('#manualDriverPreview').html(`
        <p><strong>Name:</strong> ${manualDriverData.name}</p>
        <p><strong>Phone:</strong> ${manualDriverData.phone}</p>
        ${manualDriverData.email !== 'N/A' ? `<p><strong>Email:</strong> ${manualDriverData.email}</p>` : ''}
        <p><strong>License #:</strong> ${manualDriverData.license_number}</p>
        ${manualDriverData.license_expiry !== 'N/A' ? `<p><strong>License Expiry:</strong> ${manualDriverData.license_expiry}</p>` : ''}
        ${manualDriverData.nid !== 'N/A' ? `<p><strong>NID:</strong> ${manualDriverData.nid}</p>` : ''}
        ${manualDriverData.blood_group !== 'N/A' ? `<p><strong>Blood Group:</strong> ${manualDriverData.blood_group}</p>` : ''}
        ${manualDriverData.emergency_contact !== 'N/A' ? `<p><strong>Emergency:</strong> ${manualDriverData.emergency_contact}</p>` : ''}
    `);

            // Show preview section
            $('#manualPreviewSection').slideDown();
        }

        function clearManualForm() {
            // Clear all manual input fields
            $('#manual_vehicle_type').val('');
            $('#manual_registration').val('');
            $('#manual_brand').val('');
            $('#manual_model').val('');
            $('#manual_color').val('');
            $('#manual_seating').val('4');
            $('#manual_rental_type').val('daily');
            $('#manual_rental_cost').val('');
            $('#manual_rental_company').val('');

            $('#manual_driver_name').val('');
            $('#manual_driver_phone').val('');
            $('#manual_driver_email').val('');
            $('#manual_license_number').val('');
            $('#manual_license_expiry').val('');
            $('#manual_nid').val('');
            $('#manual_blood_group').val('');
            $('#manual_emergency_contact').val('');

            $('#manual_notes').val('');

            // Hide preview
            $('#manualPreviewSection').slideUp();

            // Reset manual data objects
            manualVehicleData = null;
            manualDriverData = null;

            // IMPORTANT: If we are clearing the manual form, and no other vehicle/driver is selected,
            // then the overall selection should be cleared.
            if (isManualAssignment) {
                selectedVehicle = null;
                selectedDriver = null;
                updateSelectionSummary(); // This will disable the confirm button
            }
        }

        // Filter vehicles
        function filterVehicles() {
            const searchTerm = $('#vehicleSearch').val().toLowerCase();
            const statusFilter = $('#vehicleStatusFilter').val();
            const ownershipFilter = $('#vehicleOwnershipFilter').val();

            const filtered = allVehicles.filter(vehicle => {
                const matchesSearch = !searchTerm ||
                    vehicle.registration_number.toLowerCase().includes(searchTerm) ||
                    vehicle.brand.toLowerCase().includes(searchTerm) ||
                    vehicle.model.toLowerCase().includes(searchTerm);

                const matchesStatus = !statusFilter || vehicle.status === statusFilter;
                const matchesOwnership = !ownershipFilter || vehicle.ownership === ownershipFilter;

                return matchesSearch && matchesStatus && matchesOwnership;
            });

            renderVehicles(filtered);
        }

        // Filter drivers
        function filterDrivers() {
            const searchTerm = $('#driverSearch').val().toLowerCase();
            const statusFilter = $('#driverStatusFilter').val();

            const filtered = allDrivers.filter(driver => {
                const matchesSearch = !searchTerm ||
                    driver.name.toLowerCase().includes(searchTerm) ||
                    (driver.phone && driver.phone.toLowerCase().includes(searchTerm));

                const matchesStatus = !statusFilter || driver.status === statusFilter;

                return matchesSearch && matchesStatus;
            });

            renderDrivers(filtered);
        }

        // Event listeners
        $(document).ready(function() {
            // Vehicle filters
            $('#vehicleSearch').on('input', filterVehicles);
            $('#vehicleStatusFilter').on('change', filterVehicles);
            $('#vehicleOwnershipFilter').on('change', filterVehicles);

            // Driver filters
            $('#driverSearch').on('input', filterDrivers);
            $('#driverStatusFilter').on('change', filterDrivers);

            // Confirm button
            $('#confirmAssignment').on('click', confirmAssignment);

            // Manual assignment validation
            $('#validateManualEntry').on('click', validateManualEntry);

            // Clear manual form
            $('#clearManualForm').on('click', clearManualForm);

            // Tab change handler - MODIFIED
            $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
                const target = $(e.target).attr('data-bs-target');
                if (target === '#manual') {
                    // Switched to manual tab
                    isManualAssignment = true;
                    // Clear selections from other tabs visually, and if no manual data is ready, clear logic too
                    $('.vehicle-item').removeClass('selected');
                    $('.driver-item').removeClass('selected');

                    // If we have manual data validated, use it as selected, otherwise clear selected for manual tab
                    if (manualVehicleData && manualDriverData) {
                        selectedVehicle = manualVehicleData;
                        selectedDriver = manualDriverData;
                    } else {
                        selectedVehicle = null;
                        selectedDriver = null;
                    }
                    updateSelectionSummary(); // Re-evaluate button state based on current state
                } else {
                    // Switched away from manual tab
                    // If we were on manual tab and hadn't validated, clear previous selections.
                    // But if there's an existing assignment, try to re-select it
                    if (isManualAssignment && (!manualVehicleData || !manualDriverData)) {
                        selectedVehicle = null;
                        selectedDriver = null;
                    }
                    // If we switch back to vehicles/drivers, ensure isManualAssignment is false
                    isManualAssignment = false;

                    // Re-apply existing selections if they exist, to ensure button state is correct
                    if (currentAssignedVehicleId) {
                        const assignedVehicle = allVehicles.find(v => v.id === currentAssignedVehicleId);
                        if (assignedVehicle) selectVehicle(assignedVehicle);
                    }
                    if (currentAssignedDriverId) {
                        const assignedDriver = allDrivers.find(d => d.id === currentAssignedDriverId);
                        if (assignedDriver) selectDriver(assignedDriver);
                    }
                    updateSelectionSummary(); // Re-evaluate button state
                }
            });

            // Reset when modal closes - MODIFIED
            $('#assignmentModal').on('hidden.bs.modal', function() {
                // Reset all state variables
                selectedVehicle = null;
                selectedDriver = null;
                isManualAssignment = false;
                manualVehicleData = null;
                manualDriverData = null;
                currentAssignedVehicleId = null;
                currentAssignedDriverId = null;

                // Reset UI elements
                $('#selectionSummary').hide();
                $('#manualPreviewSection').hide();
                $('#confirmAssignment').prop('disabled', true).html(
                    '<i class="fas fa-check"></i> Confirm Assignment');

                // Clear forms and selections
                clearManualForm();
                $('.vehicle-item').removeClass('selected');
                $('.driver-item').removeClass('selected');
                $('#selectedVehicleInfo').empty();
                $('#selectedDriverInfo').empty();

                // Reset filters
                $('#vehicleSearch').val('');
                $('#vehicleStatusFilter').val('');
                $('#vehicleOwnershipFilter').val('');
                $('#driverSearch').val('');
                $('#driverStatusFilter').val('');

                // Ensure active tab is vehicles on next open
                $('#assignmentTabs a[href="#vehicles"]').tab('show');
            });
        });
    </script>
@endsection
