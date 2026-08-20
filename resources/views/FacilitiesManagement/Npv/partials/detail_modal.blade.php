<!-- NPV Agreement Detail Breakdown Modal -->
<div class="modal fade" id="npvDetailModal" tabindex="-1" aria-labelledby="npvDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-body-light border-bottom py-3">
                <div>
                    <h5 class="modal-title font-semibold mb-0" id="npvDetailModalLabel" style="font-size: 1.05rem;">
                        <i class="fa fa-calculator text-primary me-2"></i>
                        NPV Cashflow Breakdown: <span id="modalAgrRef" class="text-primary font-mono fw-bold">--</span>
                    </h5>
                    <div class="fs-xs text-muted mt-1" id="modalAgrSub">
                        Vendor: <span id="modalVendorName" class="fw-semibold">--</span> &bull; Site: <span id="modalSiteName" class="fw-semibold">--</span>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body p-4">
                <!-- Loading State Spinner -->
                <div id="modalLoadingSpinner" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading calculation detail...</span>
                    </div>
                    <div class="mt-2 text-muted fs-sm">Computing detailed monthly cash flows...</div>
                </div>

                <!-- Error Alert -->
                <div id="modalErrorAlert" class="alert alert-danger d-none" role="alert">
                    <i class="fa fa-exclamation-circle me-1"></i> <span id="modalErrorMessage">Unable to load details.</span>
                </div>

                <!-- Modal Content Container -->
                <div id="modalMainContent" class="d-none">
                    <!-- Executive Summary Strip (Single Bar Layout matching Valuation Report) -->
                    <div class="npv-summary-strip mb-3">
                        <div class="row g-0">
                            <div class="col-6 col-lg-3 summary-metric-item hero-metric ps-3 ps-md-4 py-2"
                                title="Total Present Value (NPV): Sum of discounted monthly net cash outflows">
                                <div class="metric-label" title="Total Present Value (NPV)"><i
                                        class="fa fa-coins me-1 text-primary"></i> Total Present Value (NPV)</div>
                                <div class="metric-value text-npv-primary" id="modalTotalNPV">৳ 0.00</div>
                                <div class="metric-subtext">Discounted Net Outflows</div>
                            </div>
                            <div class="col-6 col-lg-3 summary-metric-item ps-3 ps-md-4 py-2"
                                title="Total Nominal Outflow: Undiscounted total cash outflow over lease term">
                                <div class="metric-label" title="Total Nominal Outflow"><i
                                        class="fa fa-money-bill-wave me-1 text-warning"></i> Total Nominal Outflow</div>
                                <div class="metric-value text-npv-warning" id="modalTotalOutflow">৳ 0.00</div>
                                <div class="metric-subtext">Undiscounted Cash Total</div>
                            </div>
                            <div class="col-6 col-lg-3 summary-metric-item ps-3 ps-md-4 py-2"
                                title="Lease Horizon & Discount Rate">
                                <div class="metric-label" title="Lease Horizon & Rate"><i
                                        class="fa fa-calendar-alt me-1 text-info"></i> Lease Horizon & Rate</div>
                                <div class="metric-value text-npv-info"><span id="modalTotalMonths">0</span> <span class="fs-xs font-normal">mos</span></div>
                                <div class="metric-subtext" id="modalMonthlyRate">Monthly Rate: 0.00%</div>
                            </div>
                            <div class="col-6 col-lg-3 summary-metric-item ps-3 ps-md-4 py-2"
                                title="Lease Date Range">
                                <div class="metric-label" title="Lease Date Range"><i
                                        class="fa fa-clock me-1 text-success"></i> Lease Date Range</div>
                                <div class="metric-value text-npv-success fs-sm" id="modalDateRange" style="line-height: 1.6;">-- to --</div>
                                <div class="metric-subtext">Base Date & Expiry Horizon</div>
                            </div>
                        </div>
                    </div>

                    <!-- Contract & Rent Source Reference Audit (Matching Single Agreement Workbench) -->
                    <div class="npv-source-audit mb-3">
                        <div class="audit-header d-flex justify-content-between align-items-center">
                            <h3 class="audit-title" style="font-size: 0.85rem;">
                                <i class="fa fa-database me-1 text-muted"></i> Rent Base & Contract Source Audit
                            </h3>
                            <button type="button" class="btn btn-sm btn-alt-secondary py-0 px-2" data-bs-toggle="collapse"
                                data-bs-target="#modalAuditContent" aria-expanded="false">
                                <i class="fa fa-chevron-down fs-xs"></i>
                            </button>
                        </div>
                        <div class="collapse show" id="modalAuditContent">
                            <div class="p-3 fs-xs">
                                <div class="row g-3">
                                    <!-- Row 1 Left: Rent Base Parameters -->
                                    <div class="col-md-6">
                                        <div class="audit-data-box h-100">
                                            <div class="text-muted font-semibold mb-1">Rent Base Parameters</div>
                                            <div>Base Rent: <strong>৳ <span id="modalAuditBaseRent">0.00</span></strong></div>
                                            <div>At Source Tax: <strong><span id="modalAuditTaxSource">NO (0)</span></strong></div>
                                            <div>VAT: ৳ <span id="modalAuditVat">0.00</span> | Tax: ৳ <span id="modalAuditTax">0.00</span></div>
                                            <div class="mt-1 fs-2xs text-muted">
                                                Agreement Term: <strong><span id="modalAuditStartDate">N/A</span></strong> to <strong><span id="modalAuditEndDate">N/A</span></strong>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Row 1 Right: Security Deposit & Advance Structure -->
                                    <div class="col-md-6">
                                        <div class="audit-data-box h-100">
                                            <div class="text-muted font-semibold mb-1 d-flex justify-content-between align-items-center">
                                                <span>Security Deposit & Advance Structure</span>
                                                <span class="badge bg-primary-light text-primary fs-3xs d-none" id="modalAuditSdBadge">0 Clauses</span>
                                            </div>
                                            <div>Total Deposit: <strong>৳ <span id="modalAuditSdTotal">0.00</span></strong></div>
                                            <div>Adjustable Advance: <strong>৳ <span id="modalAuditSdAbsorbable">0.00</span></strong></div>
                                            <div>Non-Adjustable Deposit: <strong>৳ <span id="modalAuditSdNonAbsorbable">0.00</span></strong></div>
                                            <div id="modalAuditSdClausesBox">
                                                <div class="mt-1 fs-2xs text-muted">
                                                    Interval: <span id="modalAuditSdFrequency">N/A</span> months | Start: <span id="modalAuditSdStartDate">N/A</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Row 2 Left: Rent Components Breakdown -->
                                    <div class="col-md-6">
                                        <div class="audit-data-box h-100">
                                            <div class="text-muted font-semibold mb-1">Rent Components Breakdown</div>
                                            <div id="modalAuditComponentsBox">
                                                <!-- Populated by JS -->
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Row 2 Right: Rent Escalation Cycles -->
                                    <div class="col-md-6">
                                        <div class="audit-data-box h-100">
                                            <div class="text-muted font-semibold mb-1 d-flex justify-content-between align-items-center">
                                                <span>Rent Escalation Cycles</span>
                                                <span class="badge bg-primary-light text-primary fs-3xs" id="modalAuditIncBadge">0 Defined</span>
                                            </div>
                                            <div id="modalAuditIncrementsBox">
                                                <!-- Populated by JS -->
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Rent Increment Legend (If applicable) -->
                    <div id="modalIncLegend" class="npv-inc-legend d-none">
                        <span class="npv-inc-legend-title">Increment cycle:</span>
                        <span class="badge npv-inc-badge npv-inc-badge-new"><i class="fa fa-arrow-up"></i> 2nd</span>
                        <span>takes effect this month</span>
                        <span class="npv-inc-legend-sep">|</span>
                        <span class="badge npv-inc-badge npv-inc-badge-carry"><i class="fa fa-arrow-up"></i> 2nd</span>
                        <span>carried forward</span>
                        <span class="npv-inc-legend-sep">|</span>
                        <span>no badge = base rent</span>
                    </div>

                    <!-- Cashflow Schedule Table Container matching Workbench -->
                    <div class="npv-table-wrapper" style="max-height: 440px; overflow: auto;">
                        <table class="table table-sm table-bordered table-striped table-hover table-vcenter table-npv mb-0" id="modalCashflowTable">
                            <thead class="sticky-top bg-light" style="z-index: 10;">
                                <tr>
                                    <th class="text-center npv-col-left-1" style="width: 40px;">#</th>
                                    <th class="text-center npv-col-left-2" title="Billing Month & Increment Cycle">Billing Month</th>
                                    <th class="text-end npv-col-breakdown" title="Office space gross rent">Office Gross (৳)</th>
                                    <th class="text-end npv-col-breakdown" title="DG space gross rent">DG Gross (৳)</th>
                                    <th class="text-end npv-col-breakdown" title="Parking space gross rent">Parking Gross (৳)</th>
                                    <th class="text-end npv-col-breakdown" title="Store space gross rent">Store Gross (৳)</th>
                                    <th class="text-end npv-total-col" title="Sum of space-wise gross rents">Total Gross (৳)</th>
                                    <th class="text-end text-danger" title="Advance deduction">Advance Adj. (-৳)</th>
                                    <th class="text-end text-success" title="Security deposit refund">SD Refund (-৳)</th>
                                    <th class="text-end fw-bold" title="Net cash outflow">Net Outflow (৳)</th>
                                    <th class="text-center" title="Discount factor">Discount Factor</th>
                                    <th class="text-end text-primary fw-bold npv-col-right-2" title="Net Outflow x Discount Factor">Present Value (NPV) (৳)</th>
                                    <th class="text-end text-info npv-col-right-1" title="Cumulative PV">Cumulative PV (৳)</th>
                                </tr>
                            </thead>
                            <tbody id="modalCashflowRows">
                                <!-- Populated dynamically via JS -->
                            </tbody>
                            <tfoot class="npv-tfoot-total sticky-bottom" style="z-index: 10;">
                                <tr>
                                    <td class="text-center npv-col-left-1"></td>
                                    <td class="text-end fw-bold npv-col-left-2">TOTALS:</td>
                                    <td class="text-end npv-col-breakdown">-</td>
                                    <td class="text-end npv-col-breakdown">-</td>
                                    <td class="text-end npv-col-breakdown">-</td>
                                    <td class="text-end npv-col-breakdown">-</td>
                                    <td class="text-end num-cell fw-bold npv-total-col" id="modalFootTotalGross">৳ 0.00</td>
                                    <td class="text-end num-cell text-warning fw-bold" id="modalFootAdvanceDeduction">-৳ 0.00</td>
                                    <td class="text-end num-cell text-success fw-bold" id="modalFootDepositRefund">-৳ 0.00</td>
                                    <td class="text-end num-cell fw-bold" id="modalFootNetOutflow">৳ 0.00</td>
                                    <td class="text-center">-</td>
                                    <td class="text-end num-cell text-primary fw-bold npv-col-right-2" id="modalFootTotalNPV">৳ 0.00</td>
                                    <td class="text-end npv-col-right-1">-</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <div class="modal-footer bg-body-light border-top d-flex justify-content-between py-2">
                <a id="modalFullWorkbenchBtn" href="#" class="btn btn-sm btn-alt-primary" target="_blank">
                    <i class="fa fa-external-link-alt me-1"></i> Open Full Workbench Page
                </a>
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
