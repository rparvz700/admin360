@extends('Partials.app', ['activeMenu' => 'electricity-bills'])

@section('title')
    Create Electricity Bill
@endsection

@section('styles')
    <link rel="stylesheet" href="{{ asset('js/plugins/select2/css/select2.min.css') }}">
@endsection

@section('content')
    <div class="content">
        <div class="block block-rounded">
            <div class="block-header block-header-default">
                <h3 class="block-title"><i class="fa fa-file-invoice-dollar text-primary me-2"></i> New Electricity Bill</h3>
                <a href="{{ route('electricity.bills.index') }}" class="btn btn-secondary btn-sm float-end">Back to List</a>
            </div>
            <div class="block-content">
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <form action="{{ route('electricity.bills.store') }}" method="POST" enctype="multipart/form-data"
                    autocomplete="off">
                    @csrf

                    <!-- Section 1: Meter & Site Context -->
                    <h4 class="fw-light mb-3">1. Select Meter & Site</h4>
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="meter_id">Select Electricity Meter <span
                                    class="text-danger">*</span></label>
                            <select class="form-select select2" id="meter_id" name="meter_id" required
                                style="width: 100%;">
                                <option value="">-- Choose Meter --</option>
                                @foreach ($meters as $meter)
                                    <option value="{{ $meter->id }}" data-type="{{ $meter->meter_type }}"
                                        data-provider="{{ $meter->provider_name }}"
                                        data-consumer="{{ $meter->consumer_no }}">
                                        {{ $meter->meter_number }} ({{ $meter->meter_type_label }}) — Site:
                                        {{ $meter->building->site_name ?? 'N/A' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label" for="bill_type">Bill Type <span class="text-danger">*</span></label>
                            <select class="form-select bg-light" id="bill_type"
                                style="pointer-events: none; background-color: #e9ecef;" tabindex="-1" disabled>
                                <option value="postpaid">Postpaid</option>
                                <option value="prepaid">Prepaid</option>
                            </select>
                            <input type="hidden" name="bill_type" id="bill_type_hidden"
                                value="{{ old('bill_type', 'postpaid') }}">
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label" for="project_name">Project Name <span
                                    class="text-danger">*</span></label>
                            <select class="form-select select2" id="project_name" required style="width: 100%;">
                                <option value="">-- Select Project --</option>
                                @foreach ($projects as $project)
                                    <option value="{{ $project->name }}"
                                        {{ old('project_name') == $project->name ? 'selected' : '' }}>
                                        {{ $project->name }}
                                    </option>
                                @endforeach
                            </select>
                            <input type="hidden" name="project_name" id="project_name_hidden"
                                value="{{ old('project_name') }}">
                        </div>
                    </div>

                    <!-- Section 2: Meter Reading & Calculation (Postpaid) -->
                    <div id="postpaid-section" class="border rounded p-3 mb-4 bg-body-extra-light">
                        <h4 class="fw-light mb-3 text-primary"><i class="fa fa-tachometer-alt me-1"></i> Postpaid Meter Reading</h4>

                        <!-- NOC Verification Alert Banner Container (Empty by default) -->
                        <div id="noc-banner-container"></div>

                        <!-- Off-Peak (Flat) - Required -->
                        <h5 class="fw-normal text-muted mb-2"><i class="fa fa-moon me-1"></i> Off-Peak / Flat - <span
                                class="text-danger">Required</span></h5>
                        <div class="row mb-3">
                            <div class="col-md-3 mb-3">
                                <label class="form-label" for="previous_reading">Previous Off-Peak Reading (kWh)</label>
                                <input type="number" step="0.01" class="form-control bg-light calc-input"
                                    id="previous_reading" name="previous_reading" value="0.00" readonly>
                                <small class="form-text text-muted">Auto-fetched from previous bill</small>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label" for="current_reading">Current Off-Peak Reading (kWh) <span
                                        class="text-danger" id="current_reading_req_mark">*</span></label>
                                <input type="number" step="0.01" class="form-control calc-input" id="current_reading"
                                    name="current_reading" value="0.00">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label" for="units_consumed">Off-Peak Units Consumed (kWh)</label>
                                <input type="number" step="0.01" class="form-control bg-light fw-bold text-info"
                                    id="units_consumed" name="units_consumed" readonly value="0.00">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label" for="rate_per_unit">Off-Peak Unit Charge (৳)</label>
                                <input type="number" step="0.01" class="form-control bg-light" id="rate_per_unit"
                                    name="rate_per_unit" value="0.00" readonly>
                                <small class="form-text text-muted">Auto-fetched from meter</small>
                            </div>
                        </div>

                        <hr class="my-3">

                        <!-- Peak - Optional -->
                        <h5 class="fw-normal text-muted mb-2"><i class="fa fa-sun me-1"></i> Peak - <span
                                class="text-secondary">Optional</span></h5>
                        <div class="row mb-3">
                            <div class="col-md-3 mb-3">
                                <label class="form-label" for="previous_peak_reading">Previous Peak Reading (kWh)</label>
                                <input type="number" step="0.01" class="form-control bg-light calc-input"
                                    id="previous_peak_reading" name="previous_peak_reading" value="0.00" readonly>
                                <small class="form-text text-muted">Auto-fetched from previous bill</small>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label" for="current_peak_reading">Current Peak Reading (kWh)</label>
                                <input type="number" step="0.01" class="form-control calc-input"
                                    id="current_peak_reading" name="current_peak_reading" value="0.00">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label" for="units_peak_consumed">Peak Units Consumed (kWh)</label>
                                <input type="number" step="0.01" class="form-control bg-light fw-bold text-info"
                                    id="units_peak_consumed" name="units_peak_consumed" readonly value="0.00">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label" for="rate_peak_per_unit">Peak Unit Charge (৳) <span class="text-muted">(Optional)</span></label>
                                <input type="number" step="0.01" class="form-control bg-light" id="rate_peak_per_unit"
                                    name="rate_peak_per_unit" value="0.00" readonly>
                                <small class="form-text text-muted">Auto-fetched from meter</small>
                            </div>
                        </div>
                    </div>

                    <!-- Section 2.1: Prepaid Meter Recharge & Calculation -->
                    <div id="prepaid-section" class="border rounded p-3 mb-4 bg-body-extra-light" style="display: none;">
                        <h4 class="fw-light mb-3 text-warning"><i class="fa fa-battery-half me-1"></i> Prepaid Meter
                            Recharge & Consumption</h4>
                        <div class="row">
                            <!-- Readonly Info from Last Recharge -->
                            <div class="col-md-4 mb-3">
                                <label class="form-label" for="last_recharge_amount">Last Recharge Amount (৳)</label>
                                <input type="number" step="0.01" class="form-control bg-light"
                                    id="last_recharge_amount" name="last_recharge_amount" readonly value="0.00">
                                <small class="form-text text-muted">Auto-fetched from previous recharge</small>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label" for="last_recharge_date">Last Recharge Date</label>
                                <input type="date" class="form-control bg-light" id="last_recharge_date"
                                    name="last_recharge_date" readonly>
                                <small class="form-text text-muted">Auto-fetched from previous recharge</small>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label" for="balance_after_last_recharge">Balance After Last Recharge (৳) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" class="form-control calc-prepaid"
                                    id="balance_after_last_recharge" name="balance_after_last_recharge" placeholder="Enter balance">
                                <small class="form-text text-muted">Enter balance after previous recharge</small>
                            </div>

                            <!-- Inputs for Current Recharge -->
                            <div class="col-md-4 mb-3">
                                <label class="form-label" for="recharge_date">Recharge Date <span
                                        class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="recharge_date" name="recharge_date"
                                    value="{{ date('Y-m-d') }}">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label" for="last_balance">Last Balance (৳) <span
                                        class="text-danger">*</span></label>
                                <input type="number" step="0.01" class="form-control calc-prepaid" id="last_balance"
                                    name="last_balance" value="0.00">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label" for="recharge_amount">Recharge Amount (৳) <span
                                        class="text-danger">*</span></label>
                                <input type="number" step="0.01" class="form-control calc-prepaid"
                                    id="recharge_amount" name="recharge_amount" value="0.00">
                            </div>

                            <!-- Per Day Consumption -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label d-flex justify-content-between align-items-center"
                                    for="per_day_consumption">
                                    <span>Per Day Consumption (৳)</span>
                                    <span class="form-check form-switch fs-xs mb-0">
                                        <input class="form-check-input" type="checkbox" id="toggle_edit_consumption">
                                        <label class="form-check-label fw-normal" for="toggle_edit_consumption">Enable
                                            Manual Edit</label>
                                    </span>
                                </label>
                                <div class="input-group">
                                    <input type="number" step="0.01" class="form-control bg-light fw-bold text-dark"
                                        id="per_day_consumption" name="per_day_consumption" readonly value="0.00">
                                    <span class="input-group-text">/ day</span>
                                </div>
                                <input type="hidden" name="is_consumption_edited" id="is_consumption_edited"
                                    value="0">
                                <small class="form-text text-muted" id="consumption_help_text">Calculated: (Balance After
                                    Last Recharge - Last Balance) / Days</small>
                            </div>

                            <!-- Dedicated fields for manual override proof -->
                            <div class="col-md-12 mb-3" id="consumption-override-proof" style="display: none;">
                                <div class="p-3 border border-warning rounded bg-warning-light">
                                    <h5 class="fw-normal text-warning mb-3"><i
                                            class="fa fa-exclamation-triangle me-1"></i> Manual Override Proof Required
                                    </h5>
                                    <div class="row">
                                        <div class="col-md-6 mb-2">
                                            <label class="form-label" for="consumption_edit_remarks">Consumption Override
                                                Remarks <span class="text-danger">*</span></label>
                                            <textarea class="form-control" id="consumption_edit_remarks" name="consumption_edit_remarks" rows="2"
                                                placeholder="Provide reason for manual edit (e.g. meter replacement or correction)"></textarea>
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <label class="form-label" for="consumption_edit_attachment">Override Proof
                                                Attachment (PDF / Image / Email) <span class="text-danger">*</span></label>
                                            <input type="file" class="form-control" id="consumption_edit_attachment"
                                                name="consumption_edit_attachment"
                                                accept=".pdf,.jpg,.jpeg,.png,.eml,.msg">
                                            <small class="form-text text-muted">Upload invoice, photos, or email file
                                                (.eml, .msg) as proof of correction</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section 3: Financials & Payment Details -->
                    <h4 class="fw-light mb-3">2. Billing & Payment Details</h4>
                    <div class="row mb-4">
                        <div class="col-md-4 mb-3">
                            <label class="form-label" for="billing_month">Bill Month <span
                                    class="text-danger">*</span></label>
                            <input type="month" class="form-control" id="billing_month" name="billing_month"
                                value="{{ old('billing_month', date('Y-m')) }}" required>
                        </div>

                        <div class="col-md-4 mb-3 postpaid-only-field">
                            <label class="form-label" for="net_amount">Base Bill Amount (৳) <span
                                    class="text-danger">*</span></label>
                            <input type="number" step="0.01" class="form-control calc-input" id="net_amount"
                                name="net_amount" value="0.00" required>
                        </div>

                        <div class="col-md-4 mb-3 postpaid-only-field">
                            <label class="form-label" for="vat_amount">VAT Amount (৳)</label>
                            <input type="number" step="0.01" class="form-control calc-input" id="vat_amount"
                                name="vat_amount" value="0.00">
                        </div>

                        <div class="col-md-4 mb-3 postpaid-only-field">
                            <label class="form-label" for="late_fee">Late Fee (৳)</label>
                            <input type="number" step="0.01" class="form-control calc-input" id="late_fee"
                                name="late_fee" value="0.00">
                        </div>

                        <div class="col-md-4 mb-3 postpaid-only-field">
                            <label class="form-label" for="meter_charge">Meter Charge (৳)</label>
                            <input type="number" step="0.01" class="form-control calc-input" id="meter_charge"
                                name="meter_charge" value="0.00">
                        </div>

                        <div class="col-md-4 mb-3 postpaid-only-field">
                            <label class="form-label" for="others_amount">Others Amount (৳)</label>
                            <input type="number" step="0.01" class="form-control calc-input" id="others_amount"
                                name="others_amount" value="0.00">
                        </div>

                        <div class="col-md-4 mb-3 postpaid-only-field">
                            <label class="form-label" for="total_amount">Total Bill Amount (৳) <span
                                    class="text-danger">*</span></label>
                            <input type="number" step="0.01" class="form-control bg-light fw-bold text-primary"
                                id="total_amount" name="total_amount" value="0.00" readonly required>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label" for="payment_mode">Payment Mode <span
                                    class="text-danger">*</span></label>
                            <select class="form-select select2" id="payment_mode" name="payment_mode" required
                                style="width: 100%;">
                                <option value="BEFTN">BEFTN</option>
                                <option value="Cheque">Cheque</option>
                                <option value="bKash">bKash / Mobile Banking</option>
                                <option value="Cash">Cash</option>
                            </select>
                        </div>

                        <div class="col-md-8 mb-3">
                            <label class="form-label" for="payment_account_details">Bank / bKash / Consumer Account
                                Details</label>
                            <input type="text" class="form-control" id="payment_account_details"
                                name="payment_account_details" placeholder="e.g. A/C No: 102938471, Bank: Sonali Bank">
                        </div>

                        <div class="col-md-4 mb-3 postpaid-only-field">
                            <label class="form-label" for="received_subcenter_date">Received from Sub-Centre Date</label>
                            <input type="date" class="form-control" id="received_subcenter_date"
                                name="received_subcenter_date" value="{{ date('Y-m-d') }}">
                        </div>

                        <div class="col-md-4 mb-3 postpaid-only-field">
                            <label class="form-label" for="last_payment_date">Last Payment / Due Date</label>
                            <input type="date" class="form-control" id="last_payment_date" name="last_payment_date">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label" for="bill_file">Bill Attachment / Voucher (PDF / Image)</label>
                            <input type="file" class="form-control" id="bill_file" name="bill_file"
                                accept=".pdf,.jpg,.jpeg,.png">
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label" for="remarks">Remarks / Description</label>
                            <textarea class="form-control" id="remarks" name="remarks" rows="2" placeholder="Enter optional notes"></textarea>
                        </div>
                    </div>

                    <div class="mb-4">
                        <button type="submit" class="btn btn-primary"><i class="fa fa-save me-1"></i> Save Electricity
                            Bill</button>
                        <a href="{{ route('electricity.bills.index') }}" class="btn btn-secondary ms-2">Cancel</a>
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

            // When meter selected, fetch previous reading, prepaid details & set bill_type
            $('#meter_id').on('change select2:select', function() {
                var meterId = $(this).val();
                $('#noc-banner-container').empty();

                if (!meterId) {
                    $('#project_name').val('').trigger('change');
                    $('#project_name_hidden').val('');
                    $('#project_name').prop('disabled', false);
                    return;
                }

                var selectedOption = $(this).find('option:selected');
                var type = selectedOption.data('type') || '';
                var derivedType = (type === 'prepaid') ? 'prepaid' : 'postpaid';

                $('#bill_type').val(derivedType);
                $('#bill_type_hidden').val(derivedType);

                if (derivedType === 'prepaid') {
                    $('#postpaid-section').slideUp();
                    $('#prepaid-section').slideDown();

                    // Hide postpaid-only fields and make them not required
                    $('.postpaid-only-field').slideUp();
                    $('#net_amount, #total_amount').prop('required', false);
                    $('#billing_month').prop('required', true);

                    $('#previous_reading').val('0.00');
                    $('#current_reading').val('0.00');
                    $('#units_consumed').val('0.00');
                    $('#rate_per_unit').val('0.00');

                    $('#previous_peak_reading').val('0.00');
                    $('#current_peak_reading').val('0.00');
                    $('#units_peak_consumed').val('0.00');

                    $('#late_fee').val('0.00');
                    $('#meter_charge').val('0.00');
                    $('#others_amount').val('0.00');

                    // AJAX fetch prepaid info
                    $.ajax({
                        url: "{{ url('facilities-management/electricity/previous-reading') }}/" +
                            meterId,
                        method: 'GET',
                        success: function(res) {
                            if (res.last_prepaid_bill) {
                                $('#last_recharge_amount').val(parseFloat(res.last_prepaid_bill
                                    .recharge_amount || 0).toFixed(2));
                                $('#last_recharge_date').val(res.last_prepaid_bill
                                    .recharge_date || '');
                                $('#balance_after_last_recharge').val('');
                            } else {
                                $('#last_recharge_amount').val('0.00');
                                $('#last_recharge_date').val('');
                                $('#balance_after_last_recharge').val('');
                            }

                            if (res.meter && res.meter.consumer_no) {
                                $('#payment_account_details').val('Consumer No.: ' + res.meter
                                    .consumer_no);
                            } else {
                                $('#payment_account_details').val('');
                            }

                            // Sync and lock project name
                            if (res.project_name) {
                                $('#project_name').val(res.project_name).trigger('change');
                                $('#project_name_hidden').val(res.project_name);
                                $('#project_name').prop('disabled', true);
                            } else {
                                $('#project_name').val('').trigger('change');
                                $('#project_name_hidden').val('');
                                $('#project_name').prop('disabled', false);
                            }

                            calculatePrepaidConsumption();
                        }
                    });
                } else {
                    $('#prepaid-section').slideUp();
                    $('#postpaid-section').slideDown();

                    // Show postpaid-only fields and make them required
                    $('.postpaid-only-field').slideDown();
                    $('#billing_month, #net_amount, #total_amount').prop('required', true);

                    // Make Off-Peak required, Peak optional
                    $('#current_reading, #rate_per_unit').prop('required', true);
                    $('#current_peak_reading').prop('required', false);

                    // AJAX fetch previous readings & meter settings
                    $.ajax({
                        url: "{{ url('facilities-management/electricity/previous-reading') }}/" +
                            meterId,
                        method: 'GET',
                        success: function(res) {
                            $('#previous_reading').val(parseFloat(res.previous_reading || 0)
                                .toFixed(2));
                            $('#previous_peak_reading').val(parseFloat(res
                                .previous_peak_reading || 0).toFixed(2));

                            // Auto-fill unit charges from meter
                            if (res.meter) {
                                $('#rate_per_unit').val(parseFloat(res.meter.unit_charge_offpeak || 0).toFixed(2));
                                $('#rate_peak_per_unit').val(parseFloat(res.meter.unit_charge_peak || 0).toFixed(2));
                            } else {
                                $('#rate_per_unit').val('0.00');
                                $('#rate_peak_per_unit').val('0.00');
                            }

                            // Rule for bKash Postpaid: Only Base Bill Amount is required
                            if (res.meter && res.meter.payment_process === 'bKash') {
                                $('#current_reading').prop('required', false);
                                $('#current_reading_req_mark').hide();
                                $('#payment_mode').val('bKash').trigger('change.select2');
                            } else {
                                $('#current_reading').prop('required', true);
                                $('#current_reading_req_mark').show();
                            }

                            // Active 6-Month NOC Verification Banner
                            if (res.active_noc) {
                                var nocHtml = '<div class="alert alert-success d-flex align-items-center mb-3">' +
                                    '<i class="fa fa-shield-alt fa-2x me-3"></i>' +
                                    '<div>' +
                                    '<div class="fw-bold">Active 6-Month NOC Verified (' + escapeHtml(res.active_noc.noc_number) + ')</div>' +
                                    '<div class="fs-sm">Validity Window: ' + escapeHtml(res.active_noc.period_formatted) + ' | Authority: ' + escapeHtml(res.active_noc.issuing_authority) + '</div>' +
                                    '</div>' +
                                    '</div>';
                                $('#noc-banner-container').html(nocHtml);
                            } else {
                                var nocHtml = '<div class="alert alert-warning d-flex align-items-center mb-3">' +
                                    '<i class="fa fa-exclamation-circle fa-2x me-3"></i>' +
                                    '<div>' +
                                    '<div class="fw-bold">Notice: No Active 6-Month NOC Document</div>' +
                                    '<div class="fs-sm">No active NOC found covering the current period. You can upload 6-month NOC documents from Meters Master.</div>' +
                                    '</div>' +
                                    '</div>';
                                $('#noc-banner-container').html(nocHtml);
                            }

                            if (res.meter && res.meter.consumer_no) {
                                $('#payment_account_details').val('Consumer No.: ' + res.meter
                                    .consumer_no);
                            } else {
                                $('#payment_account_details').val('');
                            }

                            // Sync and lock project name
                            if (res.project_name) {
                                $('#project_name').val(res.project_name).trigger('change');
                                $('#project_name_hidden').val(res.project_name);
                                $('#project_name').prop('disabled', true);
                            } else {
                                $('#project_name').val('').trigger('change');
                                $('#project_name_hidden').val('');
                                $('#project_name').prop('disabled', false);
                            }

                            calculateCalculations('previous_reading');
                        }
                    });
                }
            });

            // Real-time calculation logic for postpaid
            function calculateCalculations(sourceField) {
                var billType = $('#bill_type_hidden').val();

                if (billType === 'postpaid') {
                    // Off-Peak calculations
                    var prevOffpeak = parseFloat($('#previous_reading').val()) || 0;
                    var currOffpeak = parseFloat($('#current_reading').val()) || 0;
                    var unitsOffpeak = Math.max(0, currOffpeak - prevOffpeak);
                    $('#units_consumed').val(unitsOffpeak.toFixed(2));

                    // Peak calculations
                    var prevPeak = parseFloat($('#previous_peak_reading').val()) || 0;
                    var currPeak = parseFloat($('#current_peak_reading').val()) || 0;
                    var unitsPeak = Math.max(0, currPeak - prevPeak);
                    $('#units_peak_consumed').val(unitsPeak.toFixed(2));

                    // Auto-calculate suggested base bill amount if readings entered
                    var rateOffpeak = parseFloat($('#rate_per_unit').val()) || 0;
                    var ratePeak = parseFloat($('#rate_peak_per_unit').val()) || 0;
                    var calcNet = (unitsOffpeak * rateOffpeak) + (unitsPeak * ratePeak);

                    if ((sourceField === 'current_reading' || sourceField === 'current_peak_reading' || sourceField === 'previous_reading') && calcNet > 0) {
                        $('#net_amount').val(calcNet.toFixed(2));
                    }
                }

                var finalNet = parseFloat($('#net_amount').val()) || 0;
                var finalVat = parseFloat($('#vat_amount').val()) || 0;
                var finalLateFee = parseFloat($('#late_fee').val()) || 0;
                var finalMeterCharge = parseFloat($('#meter_charge').val()) || 0;
                var finalOthers = parseFloat($('#others_amount').val()) || 0;

                var total = finalNet + finalVat + finalLateFee + finalMeterCharge + finalOthers;
                $('#total_amount').val(total.toFixed(2));
            }

            // Real-time calculation logic for prepaid
            function calculatePrepaidConsumption() {
                var balanceAfterLast = parseFloat($('#balance_after_last_recharge').val()) || 0;
                var lastBalance = parseFloat($('#last_balance').val()) || 0;
                var lastDateVal = $('#last_recharge_date').val();
                var currDateVal = $('#recharge_date').val();

                // Get recharge amount
                var rechargeAmt = parseFloat($('#recharge_amount').val()) || 0;

                // Sync net amount and total amount behind the scenes for prepaid
                $('#net_amount').val(rechargeAmt.toFixed(2));
                $('#total_amount').val(rechargeAmt.toFixed(2));

                // If manual edit is active, do not overwrite per-day consumption
                if ($('#is_consumption_edited').val() === '1') {
                    return;
                }

                if (!lastDateVal || !currDateVal) {
                    $('#per_day_consumption').val('0.00');
                    return;
                }

                var lastDate = new Date(lastDateVal);
                var currDate = new Date(currDateVal);

                lastDate.setHours(0, 0, 0, 0);
                currDate.setHours(0, 0, 0, 0);

                var diffTime = currDate - lastDate;
                var diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

                if (diffDays > 0) {
                    var consumed = balanceAfterLast - lastBalance;
                    var perDay = Math.max(0, consumed) / diffDays;
                    $('#per_day_consumption').val(perDay.toFixed(2));
                    $('#consumption_help_text').html(
                        'Calculated: (৳' + balanceAfterLast.toFixed(2) + ' - ৳' + lastBalance.toFixed(2) +
                        ') / ' + diffDays + ' days = ৳' + perDay.toFixed(2) + '/day'
                    ).removeClass('text-danger text-warning').addClass('text-muted');
                } else {
                    $('#per_day_consumption').val('0.00');
                    $('#consumption_help_text').html(
                        'Cannot calculate consumption for 0 or negative days. (Recharge Date must be after Last Recharge Date)'
                    ).removeClass('text-muted text-danger').addClass('text-warning');
                }
            }

            // Input handlers
            $('#current_reading').on('input change keyup', function() {
                calculateCalculations('current_reading');
            });

            $('#current_peak_reading').on('input change keyup', function() {
                calculateCalculations('current_peak_reading');
            });

            $('#net_amount').on('input change keyup', function() {
                calculateCalculations('net_amount');
            });

            $('#vat_amount').on('input change keyup', function() {
                calculateCalculations('vat_amount');
            });

            $('#late_fee').on('input change keyup', function() {
                calculateCalculations('late_fee');
            });

            $('#meter_charge').on('input change keyup', function() {
                calculateCalculations('meter_charge');
            });

            $('#others_amount').on('input change keyup', function() {
                calculateCalculations('others_amount');
            });

            // Prepaid handlers
            $('.calc-prepaid').on('input change keyup', function() {
                calculatePrepaidConsumption();
            });

            $('#balance_after_last_recharge').on('input change keyup', function() {
                calculatePrepaidConsumption();
            });

            $('#recharge_date').on('change', function() {
                calculatePrepaidConsumption();
            });

            // Toggle manual edit switch
            $('#toggle_edit_consumption').on('change', function() {
                var checked = $(this).is(':checked');
                if (checked) {
                    $('#per_day_consumption').prop('readonly', false).removeClass('bg-light').addClass(
                        'border-warning');
                    $('#is_consumption_edited').val('1');
                    $('#consumption_help_text').html(
                            'Manual edit active. Override Remarks or Proof Attachment is required.')
                        .removeClass('text-muted text-warning').addClass('text-danger fw-semibold');
                    $('#consumption-override-proof').slideDown();
                } else {
                    $('#per_day_consumption').prop('readonly', true).addClass('bg-light').removeClass(
                        'border-warning');
                    $('#is_consumption_edited').val('0');
                    $('#consumption-override-proof').slideUp();
                    $('#consumption_edit_remarks').val('');
                    $('#consumption_edit_attachment').val('');
                    calculatePrepaidConsumption();
                }
            });

            // Form Submit validation for manual edit
            $('form').on('submit', function(e) {
                var billType = $('#bill_type_hidden').val();
                if (billType === 'prepaid') {
                    var isEdited = $('#is_consumption_edited').val() === '1';
                    if (isEdited) {
                        var remarks = $('#consumption_edit_remarks').val().trim();
                        var file = $('#consumption_edit_attachment').val();
                        if (!remarks && !file) {
                            e.preventDefault();
                            alert(
                                'If per day consumption is manually edited, you must provide dedicated override remarks or upload an override proof attachment.');
                            $('#consumption_edit_remarks').addClass('is-invalid').focus();
                            $('#consumption_edit_attachment').addClass('is-invalid');
                            return false;
                        }
                    }
                }
            });

            $('#remarks, #bill_file, #consumption_edit_remarks, #consumption_edit_attachment').on('input change',
                function() {
                    $(this).removeClass('is-invalid');
                });

            function escapeHtml(text) {
                return $('<div>').text(text || '').html();
            }

            // Trigger meter selection handling on page load if meter is pre-selected
            if ($('#meter_id').val()) {
                $('#meter_id').trigger('change');
            }
        });
    </script>
@endsection
