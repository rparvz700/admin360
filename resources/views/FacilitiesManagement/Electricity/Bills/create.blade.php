@extends('Partials.app', ['activeMenu' => 'electricity-bills'])

@section('title') Generate Electricity Requisition @endsection

@section('styles')
    <link rel="stylesheet" href="{{ asset('js/plugins/select2/css/select2.min.css') }}">
@endsection

@section('content')
<div class="content">
    <div class="block block-rounded">
        <div class="block-header block-header-default">
            <h3 class="block-title"><i class="fa fa-file-invoice-dollar text-primary me-2"></i> New Electricity Requisition</h3>
            <a href="{{ route('electricity.bills.index') }}" class="btn btn-secondary btn-sm float-end">Back to List</a>
        </div>
        <div class="block-content">
            <form action="{{ route('electricity.bills.store') }}" method="POST" enctype="multipart/form-data" autocomplete="off">
                @csrf

                <!-- Section 1: Meter & Site Context -->
                <h4 class="fw-light mb-3">1. Select Meter & Site</h4>
                <div class="row mb-4">
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="meter_id">Select Electricity Meter <span class="text-danger">*</span></label>
                        <select class="form-select select2" id="meter_id" name="meter_id" required style="width: 100%;">
                            <option value="">-- Choose Meter --</option>
                            @foreach($meters as $meter)
                                <option value="{{ $meter->id }}" data-type="{{ $meter->meter_type }}" data-provider="{{ $meter->provider_name }}">
                                    {{ $meter->meter_number }} ({{ $meter->meter_type_label }}) — Site: {{ $meter->building->site_name ?? 'N/A' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label" for="bill_type">Bill Type <span class="text-danger">*</span></label>
                        <select class="form-select bg-light" id="bill_type" name="bill_type" readonly required>
                            <option value="postpaid">Postpaid</option>
                            <option value="prepaid">Prepaid</option>
                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label" for="project_name">Project Name <span class="text-danger">*</span></label>
                        <select class="form-select select2" id="project_name" name="project_name" required style="width: 100%;">
                            <option value="">-- Select Project --</option>
                            @foreach($projects as $project)
                                <option value="{{ $project->name }}" {{ old('project_name', 'BR Project') == $project->name ? 'selected' : '' }}>
                                    {{ $project->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Section 2: Meter Reading & Calculation (Postpaid) -->
                <div id="postpaid-section" class="border rounded p-3 mb-4 bg-body-extra-light">
                    <h4 class="fw-light mb-3 text-primary"><i class="fa fa-tachometer-alt me-1"></i> Postpaid Meter Reading & Calculation</h4>
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label" for="previous_reading">Previous Reading (kWh)</label>
                            <input type="number" step="0.01" class="form-control bg-light calc-input" id="previous_reading" name="previous_reading" value="0.00" readonly>
                            <small class="form-text text-muted">Auto-fetched from previous bill</small>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label" for="current_reading">Current Reading (kWh) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" class="form-control calc-input" id="current_reading" name="current_reading" value="0.00">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label" for="units_consumed">Units Consumed (kWh)</label>
                            <input type="number" step="0.01" class="form-control bg-light fw-bold text-info" id="units_consumed" name="units_consumed" readonly value="0.00">
                            <small class="form-text text-muted">Current - Previous Reading</small>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label" for="rate_per_unit">Rate per kWh (৳)</label>
                            <input type="number" step="0.01" class="form-control calc-input" id="rate_per_unit" name="rate_per_unit" value="0.00" placeholder="Optional unit rate">
                        </div>
                    </div>
                </div>

                <!-- Section 3: Financials & Payment Details -->
                <h4 class="fw-light mb-3">2. Requisition & Payment Details</h4>
                <div class="row mb-4">
                    <div class="col-md-3 mb-3">
                        <label class="form-label" for="billing_month">Bill Month <span class="text-danger">*</span></label>
                        <input type="month" class="form-control" id="billing_month" name="billing_month" value="{{ old('billing_month', date('Y-m')) }}" required>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label" for="net_amount">Base Bill Amount (৳) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" class="form-control calc-input" id="net_amount" name="net_amount" value="0.00" required>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label" for="vat_amount">VAT / Late Fee / Surcharge (৳)</label>
                        <input type="number" step="0.01" class="form-control calc-input" id="vat_amount" name="vat_amount" value="0.00">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label" for="total_amount">Total Bill Amount (৳) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" class="form-control bg-light fw-bold text-primary" id="total_amount" name="total_amount" value="0.00" readonly required>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label" for="payment_mode">Payment Mode <span class="text-danger">*</span></label>
                        <select class="form-select select2" id="payment_mode" name="payment_mode" required style="width: 100%;">
                            <option value="BEFTN">BEFTN</option>
                            <option value="Cheque">Cheque</option>
                            <option value="bKash">bKash / Mobile Banking</option>
                            <option value="Cash">Cash</option>
                        </select>
                    </div>

                    <div class="col-md-5 mb-3">
                        <label class="form-label" for="cheque_name">Cheque / Favour Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="cheque_name" name="cheque_name" value="{{ old('cheque_name', 'Govt. Revenue Collection') }}" required>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label" for="payment_account_details">Bank / bKash Account Details</label>
                        <input type="text" class="form-control" id="payment_account_details" name="payment_account_details" placeholder="e.g. A/C No: 102938471, Bank: Sonali Bank">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label" for="received_subcenter_date">Received from Sub-Centre Date</label>
                        <input type="date" class="form-control" id="received_subcenter_date" name="received_subcenter_date" value="{{ date('Y-m-d') }}">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label" for="last_payment_date">Last Payment / Due Date</label>
                        <input type="date" class="form-control" id="last_payment_date" name="last_payment_date">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label" for="bill_file">Bill Attachment / Voucher (PDF / Image)</label>
                        <input type="file" class="form-control" id="bill_file" name="bill_file" accept=".pdf,.jpg,.jpeg,.png">
                    </div>

                    <div class="col-md-12 mb-3">
                        <label class="form-label" for="remarks">Remarks / Description</label>
                        <textarea class="form-control" id="remarks" name="remarks" rows="2" placeholder="Enter optional notes"></textarea>
                    </div>
                </div>

                <div class="mb-4">
                    <button type="submit" class="btn btn-primary"><i class="fa fa-save me-1"></i> Generate Requisition Sheet</button>
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

    // When meter selected, fetch previous reading & set bill_type
    $('#meter_id').on('change select2:select', function() {
        var meterId = $(this).val();
        if (!meterId) return;

        var selectedOption = $(this).find('option:selected');
        var type = selectedOption.data('type');
        
        if (type === 'prepaid') {
            $('#bill_type').val('prepaid');
            $('#postpaid-section').slideUp();
            $('#previous_reading').val('0.00');
            $('#current_reading').val('0.00');
            $('#units_consumed').val('0.00');
            calculatePostpaid();
        } else {
            $('#bill_type').val('postpaid');
            $('#postpaid-section').slideDown();

            // AJAX fetch previous reading
            $.ajax({
                url: "{{ url('facilities-management/electricity/previous-reading') }}/" + meterId,
                method: 'GET',
                success: function(res) {
                    $('#previous_reading').val(parseFloat(res.previous_reading || 0).toFixed(2));
                    calculatePostpaid();
                }
            });
        }
    });

    // Real-time postpaid & total calculation
    function calculatePostpaid() {
        var billType = $('#bill_type').val();
        if (billType === 'postpaid') {
            var prev = parseFloat($('#previous_reading').val()) || 0;
            var curr = parseFloat($('#current_reading').val()) || 0;
            var rate = parseFloat($('#rate_per_unit').val()) || 0;
            var units = Math.max(0, curr - prev);
            
            $('#units_consumed').val(units.toFixed(2));
            
            if (rate > 0 && units > 0) {
                var calculatedNet = units * rate;
                $('#net_amount').val(calculatedNet.toFixed(2));
            }
        }

        var net = parseFloat($('#net_amount').val()) || 0;
        var vat = parseFloat($('#vat_amount').val()) || 0;
        var total = net + vat;
        $('#total_amount').val(total.toFixed(2));
    }

    $('.calc-input, #current_reading, #rate_per_unit, #net_amount, #vat_amount').on('input change keyup', function() {
        calculatePostpaid();
    });
});
</script>
@endsection
