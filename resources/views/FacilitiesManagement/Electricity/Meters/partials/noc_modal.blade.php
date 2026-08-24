<!-- 6-Month NOC Management Modal -->
<div class="modal fade" id="nocManagementModal" tabindex="-1" aria-labelledby="nocManagementModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-body-light">
                <h5 class="modal-title" id="nocManagementModalLabel">
                    <i class="fa fa-shield-alt text-primary me-2"></i> 6-Month NOC Management: <span id="noc_meter_number_title" class="fw-bold"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Active NOC Summary Banner -->
                <div id="noc_active_banner" class="alert alert-info d-flex align-items-center mb-3" style="display: none;">
                    <i class="fa fa-info-circle fa-2x me-3"></i>
                    <div>
                        <div class="fw-bold" id="noc_active_text">Active 6-Month NOC Found</div>
                        <div class="fs-sm" id="noc_active_period"></div>
                    </div>
                </div>

                <!-- Tabs: List vs Upload -->
                <ul class="nav nav-tabs nav-tabs-alt mb-3" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="noc-list-tab" data-bs-toggle="tab" data-bs-target="#noc-list-pane" type="button" role="tab"><i class="fa fa-list me-1"></i> NOC History</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="noc-upload-tab" data-bs-toggle="tab" data-bs-target="#noc-upload-pane" type="button" role="tab"><i class="fa fa-plus-circle me-1"></i> Upload New 6-Month NOC</button>
                    </li>
                </ul>

                <div class="tab-content">
                    <!-- Pane 1: NOC History -->
                    <div class="tab-pane fade show active" id="noc-list-pane" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-vcenter fs-sm" id="noc-history-table">
                                <thead class="table-light">
                                    <tr>
                                        <th>Ref / NOC No</th>
                                        <th>6-Month Period</th>
                                        <th>Issuing Authority</th>
                                        <th>Status</th>
                                        <th class="text-center">Document</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="noc-history-tbody">
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted"><i class="fa fa-spinner fa-spin me-2"></i> Loading NOC documents...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Pane 2: Upload New NOC -->
                    <div class="tab-pane fade" id="noc-upload-pane" role="tabpanel">
                        <form id="nocUploadForm" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" id="noc_meter_id" name="meter_id">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label" for="noc_number">NOC / Certificate No <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="noc_number" name="noc_number" required placeholder="e.g. NOC-2026-H1-082">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label" for="issuing_authority">Issuing Authority</label>
                                    <input type="text" class="form-control" id="issuing_authority" name="issuing_authority" placeholder="e.g. DESCO, Subcenter, Landlord">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label" for="period_start_date">Period Start Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="period_start_date" name="period_start_date" required value="{{ date('Y-01-01') }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label" for="period_end_date">Period End Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="period_end_date" name="period_end_date" required value="{{ date('Y-06-30') }}">
                                    <small class="form-text text-muted">Standard 6-month cycle (e.g. Jan 1 - Jun 30 or Jul 1 - Dec 31)</small>
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label class="form-label" for="noc_file">NOC Document Attachment (PDF / Image) <span class="text-danger">*</span></label>
                                    <input type="file" class="form-control" id="noc_file" name="noc_file" accept=".pdf,.jpg,.jpeg,.png" required>
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label class="form-label" for="noc_remarks">Remarks / Description</label>
                                    <textarea class="form-control" id="noc_remarks" name="remarks" rows="2" placeholder="Optional notes"></textarea>
                                </div>
                            </div>
                            <div class="text-end">
                                <button type="submit" class="btn btn-primary" id="saveNocBtn"><i class="fa fa-upload me-1"></i> Upload & Save NOC</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
