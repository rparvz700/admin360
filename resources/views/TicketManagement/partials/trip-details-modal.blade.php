<!-- Trip Details Modal -->
<div class="modal fade" id="tripDetailsModal" tabindex="-1" aria-labelledby="tripDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="tripDetailsModalLabel">
                    <i class="fas fa-route"></i> Trip & Assignment Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <!-- Left Column: Details -->
                    <div class="col-lg-6">
                        <!-- Ticket Section -->
                        <div class="card mb-3 shadow-sm border-0 bg-light">
                            <div class="card-body">
                                <h6 class="card-title text-primary mb-3">
                                    <i class="fas fa-ticket-alt me-1"></i> Ticket Information
                                </h6>
                                <div class="row g-2">
                                    <div class="col-sm-6">
                                        <span class="text-muted d-block small">Ticket Number</span>
                                        <strong id="dt-ticket-no">N/A</strong>
                                    </div>
                                    <div class="col-sm-6">
                                        <span class="text-muted d-block small">Trip Purpose</span>
                                        <strong id="dt-purpose">N/A</strong>
                                    </div>
                                    <div class="col-sm-6">
                                        <span class="text-muted d-block small">Passenger Count</span>
                                        <strong id="dt-passengers">N/A</strong>
                                    </div>
                                    <div class="col-sm-6">
                                        <span class="text-muted d-block small">Priority</span>
                                        <span id="dt-priority" class="badge">N/A</span>
                                    </div>
                                    <div class="col-12 mt-2">
                                        <span class="text-muted d-block small mb-1">Planned Route Details</span>
                                        <div id="dt-planned-route" class="p-2 bg-white rounded border small text-dark"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Vehicle & Driver Section -->
                        <div class="card mb-3 shadow-sm border-0 bg-light">
                            <div class="card-body">
                                <h6 class="card-title text-success mb-3">
                                    <i class="fas fa-user-friends me-1"></i> Assigned Resources
                                </h6>
                                <div class="row g-2">
                                    <div class="col-sm-6">
                                        <span class="text-muted d-block small">Vehicle</span>
                                        <strong id="dt-vehicle">N/A</strong>
                                    </div>
                                    <div class="col-sm-6">
                                        <span class="text-muted d-block small">Driver</span>
                                        <strong id="dt-driver">N/A</strong>
                                    </div>
                                    <div class="col-sm-6">
                                        <span class="text-muted d-block small">Seating Capacity</span>
                                        <strong id="dt-seating">N/A</strong>
                                    </div>
                                    <div class="col-sm-6">
                                        <span class="text-muted d-block small">Driver Phone</span>
                                        <strong id="dt-driver-phone">N/A</strong>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Trip Readings & Remarks Section -->
                        <div class="card mb-0 shadow-sm border-0 bg-light">
                            <div class="card-body">
                                <h6 class="card-title text-warning mb-3">
                                    <i class="fas fa-clipboard-check me-1"></i> Readings & Remarks
                                </h6>
                                <div class="row g-2">
                                    <div class="col-sm-4">
                                        <span class="text-muted d-block small">Start Odo Meter</span>
                                        <strong id="dt-start-odo">N/A</strong>
                                    </div>
                                    <div class="col-sm-4">
                                        <span class="text-muted d-block small">End Odo Meter</span>
                                        <strong id="dt-end-odo">N/A</strong>
                                    </div>
                                    <div class="col-sm-4">
                                        <span class="text-muted d-block small">Distance Travelled</span>
                                        <span id="dt-distance" class="badge bg-secondary">N/A</span>
                                    </div>
                                    <div class="col-12 mt-2">
                                        <span class="text-muted d-block small mb-1">Remarks</span>
                                        <div id="dt-remarks" class="p-2 border bg-white rounded small text-dark" style="min-height: 60px; white-space: pre-wrap;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Map -->
                    <div class="col-lg-6 mt-3 mt-lg-0">
                        <div class="card h-100 shadow-sm border-0">
                            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center py-2">
                                <h6 class="mb-0 text-white"><i class="fas fa-map-marked-alt me-1"></i> Route & Live Tracking</h6>
                                <span class="badge bg-success" id="dt-tracking-status">N/A</span>
                            </div>
                            <div class="card-body p-0" style="position: relative; min-height: 400px; height: 100%;">
                                <div id="trip-map" style="position: absolute; top: 0; bottom: 0; left: 0; right: 0; min-height: 400px; border-radius: 0 0 .25rem .25rem;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
