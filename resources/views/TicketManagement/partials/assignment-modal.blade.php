<!-- Assignment Modal -->
<div class="modal fade" id="assignmentModal" tabindex="-1" aria-labelledby="assignmentModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="assignmentModalLabel">
                    <i class="fas fa-car"></i> Assign Vehicle & Driver
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Ticket Info Summary -->
                <div class="alert alert-info mb-4" id="ticketInfoSummary">
                    <div class="row">
                        <div class="col-md-3">
                            <strong>Ticket:</strong> <span id="modalTicketNumber"></span>
                        </div>
                        <div class="col-md-3">
                            <strong>Start:</strong> <span id="modalStartTime"></span>
                        </div>
                        <div class="col-md-3">
                            <strong>End:</strong> <span id="modalEndTime"></span>
                        </div>
                        <div class="col-md-3">
                            <strong>Passengers:</strong> <span id="modalPassengers"></span>
                        </div>
                    </div>
                </div>

                <!-- Selection Summary -->
                <div class="row mb-4" id="selectionSummary" style="display: none;">
                    <div class="col-md-12">
                        <div class="alert alert-success">
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>Selected Vehicle:</strong>
                                    <div id="selectedVehicleInfo" class="mt-2"></div>
                                </div>
                                <div class="col-md-6">
                                    <strong>Selected Driver:</strong>
                                    <div id="selectedDriverInfo" class="mt-2"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabs for Vehicles and Drivers -->
                <ul class="nav nav-tabs mb-3" id="assignmentTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="vehicles-tab" data-bs-toggle="tab" data-bs-target="#vehicles" type="button" role="tab">
                            <i class="fas fa-car"></i> Vehicles <span class="badge bg-primary ms-2" id="vehicleCount">0</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="drivers-tab" data-bs-toggle="tab" data-bs-target="#drivers" type="button" role="tab">
                            <i class="fas fa-user"></i> Drivers <span class="badge bg-primary ms-2" id="driverCount">0</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="manual-tab" data-bs-toggle="tab" data-bs-target="#manual" type="button" role="tab">
                            <i class="fas fa-keyboard"></i> Manual Assignment
                        </button>
                    </li>
                </ul>

                <!-- Tab Content -->
                <div class="tab-content" id="assignmentTabContent">
                    <!-- Vehicles Tab -->
                    <div class="tab-pane fade show active" id="vehicles" role="tabpanel">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <input type="text" class="form-control" id="vehicleSearch" placeholder="Search vehicles...">
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" id="vehicleStatusFilter">
                                    <option value="">All Status</option>
                                    <option value="available">Available</option>
                                    <option value="on_assignment">On Assignment</option>
                                    <option value="in_maintenance">In Maintenance</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" id="vehicleOwnershipFilter">
                                    <option value="">All Types</option>
                                    <option value="Owned">Owned</option>
                                    <option value="Rented">Rented</option>
                                </select>
                            </div>
                        </div>

                        <div id="vehiclesGrid" class="row g-3" style="max-height: 500px; overflow-y: auto;">
                            <!-- Vehicle cards will be loaded here -->
                            <div class="col-12 text-center py-5">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Drivers Tab -->
                    <div class="tab-pane fade" id="drivers" role="tabpanel">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <input type="text" class="form-control" id="driverSearch" placeholder="Search drivers...">
                            </div>
                            <div class="col-md-6">
                                <select class="form-select" id="driverStatusFilter">
                                    <option value="">All Status</option>
                                    <option value="available">Available</option>
                                    <option value="on_assignment">On Assignment</option>
                                    <option value="on_leave">On Leave</option>
                                    <option value="sick">Sick</option>
                                    <option value="unavailable">Unavailable</option>
                                </select>
                            </div>
                        </div>

                        <div id="driversGrid" class="row g-3" style="max-height: 500px; overflow-y: auto;">
                            <!-- Driver cards will be loaded here -->
                            <div class="col-12 text-center py-5">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Manual Assignment Tab -->
                    <div class="tab-pane fade" id="manual" role="tabpanel">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Use this tab to manually enter ad-hoc rented vehicle and driver details that are not in the system.
                        </div>

                        <div class="row">
                            <!-- Manual Vehicle Entry -->
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header bg-primary text-white">
                                        <h6 class="mb-0"><i class="fas fa-car"></i> Ad-hoc Vehicle Details</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label class="form-label required">Vehicle Type</label>
                                            <select id="manual_vehicle_type" class="form-select">
                                                <option value="">Select Type</option>
                                                <option value="Sedan">Sedan</option>
                                                <option value="SUV">SUV</option>
                                                <option value="Van">Van</option>
                                                <option value="Bus">Bus</option>
                                                <option value="Microbus">Microbus</option>
                                                <option value="Pickup">Pickup</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label required">Registration Number</label>
                                            <input type="text" id="manual_registration" class="form-control" placeholder="e.g., DHA-12345">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label required">Brand</label>
                                            <input type="text" id="manual_brand" class="form-control" placeholder="e.g., Toyota">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label required">Model</label>
                                            <input type="text" id="manual_model" class="form-control" placeholder="e.g., Corolla">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Color</label>
                                            <input type="text" id="manual_color" class="form-control" placeholder="e.g., White">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label required">Seating Capacity</label>
                                            <input type="number" id="manual_seating" class="form-control" min="1" value="4">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label required">Rental Type</label>
                                            <select id="manual_rental_type" class="form-select">
                                                <option value="daily">Daily Rental</option>
                                                <option value="hourly">Hourly Rental</option>
                                                <option value="trip">Per Trip</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Rental Cost</label>
                                            <input type="number" id="manual_rental_cost" class="form-control" placeholder="Amount in BDT" step="0.01">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Rental Company</label>
                                            <input type="text" id="manual_rental_company" class="form-control" placeholder="e.g., ABC Car Rental">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Manual Driver Entry -->
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header bg-success text-white">
                                        <h6 class="mb-0"><i class="fas fa-user"></i> Ad-hoc Driver Details</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label class="form-label required">Driver Name</label>
                                            <input type="text" id="manual_driver_name" class="form-control" placeholder="Full Name">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label required">Phone Number</label>
                                            <input type="text" id="manual_driver_phone" class="form-control" placeholder="e.g., 01711-123456">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Email</label>
                                            <input type="email" id="manual_driver_email" class="form-control" placeholder="driver@example.com">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label required">License Number</label>
                                            <input type="text" id="manual_license_number" class="form-control" placeholder="Driving License #">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">License Expiry Date</label>
                                            <input type="date" id="manual_license_expiry" class="form-control">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">NID Number</label>
                                            <input type="text" id="manual_nid" class="form-control" placeholder="National ID">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Blood Group</label>
                                            <select id="manual_blood_group" class="form-select">
                                                <option value="">Select Blood Group</option>
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
                                        <div class="mb-3">
                                            <label class="form-label">Emergency Contact</label>
                                            <input type="text" id="manual_emergency_contact" class="form-control" placeholder="Emergency phone">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-body">
                                        <h6>Additional Notes</h6>
                                        <textarea id="manual_notes" class="form-control" rows="3" placeholder="Any additional information about this rental..."></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-12">
                                <button type="button" class="btn btn-primary" id="validateManualEntry">
                                    <i class="fas fa-check-circle"></i> Validate & Preview
                                </button>
                                <button type="button" class="btn btn-secondary" id="clearManualForm">
                                    <i class="fas fa-eraser"></i> Clear Form
                                </button>
                            </div>
                        </div>

                        <!-- Preview Section -->
                        <div id="manualPreviewSection" class="mt-4" style="display: none;">
                            <div class="alert alert-success">
                                <h6><i class="fas fa-check-circle"></i> Ready to Assign</h6>
                                <p class="mb-0">Please review the details below and click "Confirm Assignment" to proceed.</p>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card border-primary">
                                        <div class="card-header bg-primary text-white">
                                            <strong>Vehicle Preview</strong>
                                        </div>
                                        <div class="card-body" id="manualVehiclePreview">
                                            <!-- Vehicle preview will be inserted here -->
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card border-success">
                                        <div class="card-header bg-success text-white">
                                            <strong>Driver Preview</strong>
                                        </div>
                                        <div class="card-body" id="manualDriverPreview">
                                            <!-- Driver preview will be inserted here -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmAssignment" disabled>
                    <i class="fas fa-check"></i> Confirm Assignment
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Vehicle Card Template -->
<template id="vehicleCardTemplate">
    <div class="col-md-6 col-lg-4 vehicle-card" data-vehicle-id="" data-status="" data-ownership="">
        <div class="card h-100 vehicle-item" style="cursor: pointer; transition: all 0.3s;">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong class="vehicle-registration"></strong>
                <span class="badge vehicle-status-badge"></span>
            </div>
            <div class="card-body">
                <h6 class="vehicle-name mb-2"></h6>
                <div class="vehicle-details">
                    <p class="mb-1 small">
                        <i class="fas fa-tag"></i> <span class="vehicle-type"></span>
                    </p>
                    <p class="mb-1 small">
                        <i class="fas fa-users"></i> <span class="vehicle-capacity"></span> seats
                    </p>
                    <p class="mb-1 small">
                        <i class="fas fa-building"></i> <span class="vehicle-ownership"></span>
                    </p>
                    <p class="mb-1 small">
                        <i class="fas fa-paint-brush"></i> <span class="vehicle-color"></span>
                    </p>
                </div>
                <div class="vehicle-assignment-info mt-2" style="display: none;">
                    <hr class="my-2">
                    <div class="alert alert-warning py-2 px-2 mb-0 small">
                        <div><strong>Current Assignment:</strong></div>
                        <div class="assignment-ticket"></div>
                        <div class="assignment-countdown"></div>
                    </div>
                </div>
                <div class="vehicle-match-indicator mt-2" style="display: none;">
                    <span class="badge bg-info">
                        <i class="fas fa-check-circle"></i> Matches Requirements
                    </span>
                </div>
            </div>
        </div>
    </div>
</template>

<!-- Driver Card Template -->
<template id="driverCardTemplate">
    <div class="col-md-6 col-lg-4 driver-card" data-driver-id="" data-status="">
        <div class="card h-100 driver-item" style="cursor: pointer; transition: all 0.3s;">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong class="driver-name"></strong>
                <span class="badge driver-status-badge"></span>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-start">
                    <div class="driver-avatar me-3">
                        <img class="driver-image rounded-circle" style="width: 50px; height: 50px; object-fit: cover;" alt="Driver">
                    </div>
                    <div class="flex-grow-1">
                        <div class="driver-details">
                            <p class="mb-1 small">
                                <i class="fas fa-phone"></i> <span class="driver-phone"></span>
                            </p>
                            <p class="mb-1 small">
                                <i class="fas fa-envelope"></i> <span class="driver-office-location"></span>
                            </p>
                            <p class="mb-1 small">
                                <i class="fas fa-id-badge"></i> <span class="driver-job-location"></span>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="driver-assignment-info mt-2" style="display: none;">
                    <hr class="my-2">
                    <div class="alert alert-warning py-2 px-2 mb-0 small">
                        <div><strong>Current Assignment:</strong></div>
                        <div class="assignment-ticket"></div>
                        <div class="assignment-countdown"></div>
                    </div>
                </div>
                <div class="driver-unavailable-info mt-2" style="display: none;">
                    <hr class="my-2">
                    <div class="alert alert-danger py-2 px-2 mb-0 small">
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
    border: 2px solid #0d6efd;
    background-color: #e7f1ff;
}

.vehicle-item:hover,
.driver-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.vehicle-item.unavailable,
.driver-item.unavailable {
    opacity: 0.6;
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

// Initialize modal
function initAssignmentModal(ticketId) {
    selectedVehicle = null;
    selectedDriver = null;
    isManualAssignment = false;
    manualVehicleData = null;
    manualDriverData = null;    
    // Reset UI
    $('#selectionSummary').hide();
    $('#confirmAssignment').prop('disabled', true);
    $('#manualPreviewSection').hide();
    
    // Load data
    loadAssignmentData(ticketId);
}

// Load vehicles and drivers data
function loadAssignmentData(ticketId) {
    $.ajax({
        url: '{{ route("admin.tickets.assignment.resources") }}',
        method: 'GET',
        data: { ticket_id: ticketId },
        success: function(response) {
            allVehicles = response.vehicles;
            allDrivers = response.drivers;
            ticketData = response.ticket;
            
            // Update ticket info summary
            updateTicketSummary(ticketData);
            
            // Render grids
            renderVehicles(allVehicles);
            renderDrivers(allDrivers);
            
            // Update counts
            updateCounts();
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
        const countdownText = timeRemaining > 0 
            ? `Available in: ${formatTimeRemaining(timeRemaining)}`
            : 'Should be available now';
        assignmentInfo.find('.assignment-countdown').html(`<span class="countdown-timer">${countdownText}</span>`);
    }
    
    // Make unavailable if not available
    console.log(vehicle);
    if (!vehicle.is_available) {
        card.find('.vehicle-item').addClass('unavailable');
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
    const imagePath = driver.image_path ? `/storage/${driver.image_path}` : '{{ asset('/media/avatars/driver.png') }}';
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
        const countdownText = timeRemaining > 0 
            ? `Available in: ${formatTimeRemaining(timeRemaining)}`
            : 'Should be available now';
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

// Select vehicle
function selectVehicle(vehicle) {
    selectedVehicle = vehicle;
    
    // Update UI
    $('.vehicle-item').removeClass('selected');
    $(`.vehicle-card[data-vehicle-id="${vehicle.id}"] .vehicle-item`).addClass('selected');
    
    // Update selection summary
    $('#selectedVehicleInfo').html(`
        <strong>${vehicle.brand} ${vehicle.model}</strong><br>
        ${vehicle.registration_number} | ${vehicle.vehicle_type} | ${vehicle.seating_capacity} seats
    `);
    
    updateSelectionSummary();
}

// Select driver
function selectDriver(driver) {
    selectedDriver = driver;
    
    // Update UI
    $('.driver-item').removeClass('selected');
    $(`.driver-card[data-driver-id="${driver.id}"] .driver-item`).addClass('selected');
    
    // Update selection summary
    $('#selectedDriverInfo').html(`
        <strong>${driver.name}</strong><br>
        ${driver.phone} | ${driver.employment_contract}
    `);
    
    updateSelectionSummary();
}

// Update selection summary
function updateSelectionSummary() {
    if (selectedVehicle && selectedDriver) {
        $('#selectionSummary').show();
        $('#confirmAssignment').prop('disabled', false);
    } else {
        $('#selectionSummary').show();
        $('#confirmAssignment').prop('disabled', true);
    }
}

// Confirm assignment
function confirmAssignment() {
    if (!selectedVehicle || !selectedDriver) {
        alert('Please select both a vehicle and a driver.');
        return;
    }
    
    // Show loading
    $('#confirmAssignment').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Assigning...');
    // Prepare data
    const requestData = {
            ticket_id: ticketData.id,
            vehicle_id: selectedVehicle.id,
            driver_id: selectedDriver.id,
            is_manual: isManualAssignment
    };
    if (isManualAssignment) {
        // Manual assignment - send manual data
        requestData.vehicle_id = 'manual';
        requestData.driver_id = 'manual';
        requestData.manual_vehicle_data = {
            registration_number: manualVehicleData.registration_number,
            brand: manualVehicleData.brand,
            model: manualVehicleData.model,
            color: manualVehicleData.color,
            seating_capacity: manualVehicleData.seating_capacity,
            rental_type: manualVehicleData.rental_type,
            rental_cost: manualVehicleData.rental_cost,
            rental_company: manualVehicleData.rental_company,
            notes: manualVehicleData.notes || ''
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
            notes: manualDriverData.notes || ''
        };
    } else {
        // Regular assignment
        requestData.vehicle_id = selectedVehicle.id;
        requestData.driver_id = selectedDriver.id;
    };

    $.ajax({
        url: '{{ route("admin.tickets.assignment.assign") }}',
        method: 'POST',
        data: requestData,
        success: function(response) {
            if (response.success) {
                // Show success message
                const message = isManualAssignment 
                    ? 'Ad-hoc rental vehicle and driver assigned successfully!' 
                    : response.message;
                alert(message);
                
                // Close modal
                $('#assignmentModal').modal('hide');
                
                // Reload page to show updated assignment
                location.reload();
            } else {
                alert(response.message);
                $('#confirmAssignment').prop('disabled', false).html('<i class="fas fa-check"></i> Confirm Assignment');
            }
        },
        error: function(xhr) {
            const message = xhr.responseJSON?.message || 'Failed to assign vehicle and driver. Please try again.';
            alert(message);
            $('#confirmAssignment').prop('disabled', false).html('<i class="fas fa-check"></i> Confirm Assignment');
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
    const availableDrivers = allDrivers.filter(d => d.is_available).length;
    
    $('#vehicleCount').text(`${availableVehicles}/${allVehicles.length} Available`);
    $('#driverCount').text(`${availableDrivers}/${allDrivers.length} Available`);
}

// Manual Assignment Functions
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
    const licenseNumber = $('#manual_license_number').val().trim();
    
    // Check required vehicle fields
    if (!vehicleType || !registration || !brand || !model || !seating || !rentalType) {
        alert('Please fill in all required vehicle fields (marked with *)');
        return;
    }
    
    // Check required driver fields
    if (!driverName || !driverPhone || !licenseNumber) {
        alert('Please fill in all required driver fields (marked with *)');
        return;
    }
    
    // Create manual vehicle data object
    manualVehicleData = {
        id: 'manual_' + Date.now(),
        is_manual: true,
        vehicle_type: vehicleType,
        registration_number: registration,
        brand: brand,
        model: model,
        color: $('#manual_color').val().trim() || 'N/A',
        seating_capacity: seating,
        rental_type: rentalType,
        rental_cost: $('#manual_rental_cost').val() || 0,
        rental_company: $('#manual_rental_company').val().trim() || 'N/A',
        ownership: 'Rented',
        is_available: true,
        status: 'available',
        status_label: 'Available',
        status_color: 'success'
    };
    
    // Create manual driver data object
    manualDriverData = {
        id: 'manual_' + Date.now(),
        is_manual: true,
        name: driverName,
        phone: driverPhone,
        email: $('#manual_driver_email').val().trim() || 'N/A',
        license_number: licenseNumber,
        license_expiry: $('#manual_license_expiry').val() || 'N/A',
        nid: $('#manual_nid').val().trim() || 'N/A',
        blood_group: $('#manual_blood_group').val() || 'N/A',
        emergency_contact: $('#manual_emergency_contact').val().trim() || 'N/A',
        employment_contract: 'Ad-hoc',
        is_available: true,
        status: 'available',
        status_label: 'Available',
        status_color: 'success'
    };
    
    // Add notes
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
    
    // Reset selection
    if (isManualAssignment) {
        selectedVehicle = null;
        selectedDriver = null;
        manualVehicleData = null;
        manualDriverData = null;
        updateSelectionSummary();
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
    
    // Tab change handler
    $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
        const target = $(e.target).attr('data-bs-target');
        if (target === '#manual') {
            // Switched to manual tab
            isManualAssignment = true;
        } else {
            // Switched away from manual tab
            if (isManualAssignment && !manualVehicleData) {
                selectedVehicle = null;
                selectedDriver = null;
                updateSelectionSummary();
            }
        }
    });
    
    // Reset when modal closes
    $('#assignmentModal').on('hidden.bs.modal', function() {
        selectedVehicle = null;
        selectedDriver = null;
        isManualAssignment = false;
        manualVehicleData = null;
        manualDriverData = null;
        $('#selectionSummary').hide();
        $('#manualPreviewSection').hide();
        $('#confirmAssignment').prop('disabled', true).html('<i class="fas fa-check"></i> Confirm Assignment');
    });
});
</script>
@endsection