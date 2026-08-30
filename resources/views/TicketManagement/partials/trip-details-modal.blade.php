<!-- Trip Details Modal -->
<div class="modal fade" id="tripDetailsModal" tabindex="-1" aria-labelledby="tripDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="block block-rounded block-transparent mb-0">
                <div class="block-header bg-primary py-3">
                    <h3 class="block-title text-white fs-sm fw-bold">
                        <i class="fa fa-route me-1"></i> Trip & Resource Tracking Details
                    </h3>
                    <div class="block-options">
                        <button type="button" class="btn-block-option text-white" data-bs-dismiss="modal" aria-label="Close">
                            <i class="fa fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="block-content p-4">
                    <div class="row g-3">
                        <!-- Left Column: Details -->
                        <div class="col-lg-6">
                            <!-- Ticket Section -->
                            <div class="block block-rounded block-bordered mb-3">
                                <div class="block-header bg-body-light py-2">
                                    <h4 class="block-title fs-xs fw-bold text-primary text-uppercase">
                                        <i class="fa fa-ticket me-1"></i> Ticket Information
                                    </h4>
                                </div>
                                <div class="block-content fs-sm pb-3">
                                    <div class="row g-2">
                                        <div class="col-sm-6">
                                            <span class="text-muted d-block fs-xs text-uppercase">Ticket Number</span>
                                            <strong id="dt-ticket-no" class="text-dark">N/A</strong>
                                        </div>
                                        <div class="col-sm-6">
                                            <span class="text-muted d-block fs-xs text-uppercase">Priority</span>
                                            <span id="dt-priority" class="badge">N/A</span>
                                        </div>
                                        <div class="col-sm-6">
                                            <span class="text-muted d-block fs-xs text-uppercase">Passenger Count</span>
                                            <strong id="dt-passengers">N/A</strong>
                                        </div>
                                        <div class="col-sm-6">
                                            <span class="text-muted d-block fs-xs text-uppercase">Trip Purpose</span>
                                            <span id="dt-purpose" class="text-dark">N/A</span>
                                        </div>
                                        <div class="col-12 mt-2">
                                            <span class="text-muted d-block fs-xs text-uppercase mb-1">Planned Route Legs</span>
                                            <div id="dt-planned-route" class="p-2 bg-body-light rounded border fs-xs text-dark"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Vehicle & Driver Section -->
                            <div class="block block-rounded block-bordered mb-3">
                                <div class="block-header bg-body-light py-2">
                                    <h4 class="block-title fs-xs fw-bold text-success text-uppercase">
                                        <i class="fa fa-user-friends me-1"></i> Assigned Resources
                                    </h4>
                                </div>
                                <div class="block-content fs-sm pb-3">
                                    <div class="row g-2">
                                        <div class="col-sm-6">
                                            <span class="text-muted d-block fs-xs text-uppercase">Vehicle</span>
                                            <strong id="dt-vehicle" class="text-dark">N/A</strong>
                                        </div>
                                        <div class="col-sm-6">
                                            <span class="text-muted d-block fs-xs text-uppercase">Driver</span>
                                            <strong id="dt-driver" class="text-dark">N/A</strong>
                                        </div>
                                        <div class="col-sm-6">
                                            <span class="text-muted d-block fs-xs text-uppercase">Seating Capacity</span>
                                            <strong id="dt-seating">N/A</strong>
                                        </div>
                                        <div class="col-sm-6">
                                            <span class="text-muted d-block fs-xs text-uppercase">Driver Phone</span>
                                            <strong id="dt-driver-phone">N/A</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Trip Readings & Remarks Section -->
                            <div class="block block-rounded block-bordered mb-0">
                                <div class="block-header bg-body-light py-2">
                                    <h4 class="block-title fs-xs fw-bold text-warning text-uppercase">
                                        <i class="fa fa-gauge-high me-1"></i> Odometer Readings & Photos
                                    </h4>
                                </div>
                                <div class="block-content fs-sm pb-3">
                                    <div class="row g-2">
                                        <div class="col-sm-4 text-center text-sm-start">
                                            <span class="text-muted d-block fs-xs text-uppercase">Start Odo</span>
                                            <strong id="dt-start-odo" class="fs-6 text-dark d-block">N/A</strong>
                                            <div id="dt-start-odo-img-container" class="mt-1" style="display: none;">
                                                <img id="dt-start-odo-img" src="" alt="Start Odo Photo" class="img-thumbnail" style="max-height: 70px; cursor: pointer;" onclick="previewImage(this.src, 'Start Odometer Photo')">
                                            </div>
                                        </div>
                                        <div class="col-sm-4 text-center text-sm-start">
                                            <span class="text-muted d-block fs-xs text-uppercase">End Odo</span>
                                            <strong id="dt-end-odo" class="fs-6 text-dark d-block">N/A</strong>
                                            <div id="dt-end-odo-img-container" class="mt-1" style="display: none;">
                                                <img id="dt-end-odo-img" src="" alt="End Odo Photo" class="img-thumbnail" style="max-height: 70px; cursor: pointer;" onclick="previewImage(this.src, 'End Odometer Photo')">
                                            </div>
                                        </div>
                                        <div class="col-sm-4 text-center text-sm-start">
                                            <span class="text-muted d-block fs-xs text-uppercase">Travelled Distance</span>
                                            <span id="dt-distance" class="badge bg-secondary fs-xs mt-1">N/A</span>
                                        </div>
                                        <div class="col-12 mt-2">
                                            <span class="text-muted d-block fs-xs text-uppercase mb-1">Remarks</span>
                                            <div id="dt-remarks" class="p-2 border bg-body-light rounded fs-xs text-dark" style="min-height: 48px; white-space: pre-wrap;"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column: Map -->
                        <div class="col-lg-6">
                            <div class="block block-rounded block-bordered h-100 d-flex flex-column mb-0">
                                <div class="block-header bg-dark py-2">
                                    <h4 class="block-title fs-xs fw-bold text-white text-uppercase">
                                        <i class="fa fa-map-marked-alt me-1"></i> Route & Live GPS Trail
                                    </h4>
                                    <div class="block-options">
                                        <span class="badge bg-success fs-xs" id="dt-tracking-status">N/A</span>
                                    </div>
                                </div>
                                <div class="block-content p-0 flex-grow-1" style="position: relative; min-height: 420px;">
                                    <div id="trip-map" style="position: absolute; top: 0; bottom: 0; left: 0; right: 0; border-radius: 0 0 8px 8px;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="block-content block-content-full block-content-sm text-end bg-body-light rounded-bottom">
                    <button type="button" class="btn btn-sm btn-alt-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>
