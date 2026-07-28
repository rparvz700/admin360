<div class="row">
    <!-- Maintenance context card (only when creating from maintenance) -->
    @if(isset($maintenance) && $maintenance)
    <div class="col-md-12 mb-4">
        <div class="alert alert-secondary mb-0">
            <div class="row">
                <div class="col-md-3">
                    <small class="text-muted d-block">Vehicle</small>
                    <strong>{{ $maintenance->vehicle->registration_number ?? 'N/A' }}</strong>
                    — {{ $maintenance->vehicle->brand ?? '' }} {{ $maintenance->vehicle->model ?? '' }}
                </div>
                <div class="col-md-2">
                    <small class="text-muted d-block">Type</small>
                    <span class="badge bg-{{ $maintenance->getMaintenanceTypeBadge() }}">
                        {{ $maintenance->getMaintenanceTypeLabel() }}
                    </span>
                </div>
                <div class="col-md-2">
                    <small class="text-muted d-block">Service Date</small>
                    <strong>{{ $maintenance->start_datetime->format('d M Y') }}</strong>
                </div>
                <div class="col-md-2">
                    <small class="text-muted d-block">Labor Cost</small>
                    <strong>৳ {{ number_format($maintenance->labor_cost, 2) }}</strong>
                </div>
                <div class="col-md-2">
                    <small class="text-muted d-block">Parts Cost</small>
                    <strong>৳ {{ number_format($maintenance->parts_cost, 2) }}</strong>
                </div>
                <div class="col-md-1">
                    <small class="text-muted d-block">Total</small>
                    <strong class="text-primary">৳ {{ number_format($maintenance->total_service_cost, 2) }}</strong>
                </div>
            </div>
        </div>
    </div>
    @elseif(isset($rent) && $rent)
    <!-- Rent context card (only when creating from rent) -->
    <div class="col-md-12 mb-4">
        <div class="alert alert-secondary mb-0">
            <div class="row">
                <div class="col-md-3">
                    <small class="text-muted d-block">Agreement Ref No.</small>
                    <strong>{{ $rent->agreement->agreement_ref_no ?? 'N/A' }}</strong>
                </div>
                <div class="col-md-2">
                    <small class="text-muted d-block">Vendor</small>
                    <strong>{{ $rent->agreement->vendor->name ?? 'N/A' }}</strong>
                </div>
                <div class="col-md-2">
                    <small class="text-muted d-block">Base Rent</small>
                    <strong>৳ {{ number_format($rent->base_rent, 2) }}</strong>
                </div>
                <div class="col-md-2">
                    <small class="text-muted d-block">VAT / Tax</small>
                    <strong>৳ {{ number_format($rent->vat + $rent->tax, 2) }}</strong>
                </div>
                <div class="col-md-3">
                    <small class="text-muted d-block">Total Rent Amount (Base + VAT + Tax)</small>
                    <strong class="text-primary">৳ {{ number_format($rent->base_rent + $rent->vat + $rent->tax, 2) }}</strong>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Invoice Number (auto-generated, read-only) -->
    <div class="col-md-4 mb-3">
        <label class="form-label" for="invoice_number">Invoice Number</label>
        <input type="text" class="form-control bg-light" id="invoice_number" name="invoice_number"
               value="{{ old('invoice_number', $invoice->invoice_number ?? $nextInvoiceNumber) }}" readonly>
        <small class="form-text text-muted">Auto-generated</small>
    </div>

    <!-- Vendor -->
    <div class="col-md-4 mb-3">
        <label class="form-label" for="vendor_id">Vendor <span class="text-danger">*</span></label>
        <select class="form-select" id="vendor_id" name="vendor_id" required
            {{ (isset($maintenance) && $maintenance) || (isset($rent) && $rent) ? 'readonly' : '' }}>
            <option value="">Select Vendor</option>
            @foreach($vendors as $vendor)
                <option value="{{ $vendor->id }}"
                    {{ old('vendor_id',
                        $invoice->vendor_id
                        ?? ($maintenance->vendor_id ?? ($rent->agreement->vendor_id ?? '')))
                        == $vendor->id ? 'selected' : '' }}>
                    {{ $vendor->name }} ({{ $vendor->vendor_code }})
                </option>
            @endforeach
        </select>
        @if(isset($maintenance) && $maintenance)
            <small class="form-text text-muted">Pre-filled from maintenance vendor</small>
        @elseif(isset($rent) && $rent)
            <small class="form-text text-muted">Pre-filled from rent agreement vendor</small>
        @endif
    </div>

    <!-- Invoice Date -->
    <div class="col-md-4 mb-3">
        <label class="form-label" for="invoice_date">Invoice Date <span class="text-danger">*</span></label>
        <input type="date" class="form-control" id="invoice_date" name="invoice_date"
               value="{{ old('invoice_date', isset($invoice) && $invoice->invoice_date ? $invoice->invoice_date->format('Y-m-d') : date('Y-m-d')) }}"
               required>
    </div>

    <!-- Due Date -->
    <div class="col-md-4 mb-3">
        <label class="form-label" for="due_date">Due Date</label>
        <input type="date" class="form-control" id="due_date" name="due_date"
               value="{{ old('due_date', isset($invoice) && $invoice->due_date ? $invoice->due_date->format('Y-m-d') : '') }}">
    </div>

    <!-- Subtotal -->
    <div class="col-md-4 mb-3">
        <label class="form-label" for="subtotal">Subtotal (৳) <span class="text-danger">*</span></label>
        <input type="number" step="0.01" class="form-control calc-input" id="subtotal" name="subtotal"
               value="{{ old('subtotal', $invoice->subtotal ?? ($maintenance->total_service_cost ?? ($rent ? ($rent->base_rent + $rent->vat + $rent->tax) : 0))) }}"
               min="0" required>
        @if(isset($maintenance) && $maintenance)
            <small class="form-text text-muted">Pre-filled from maintenance total cost</small>
        @elseif(isset($rent) && $rent)
            <small class="form-text text-muted">Pre-filled from rent total amount (Base + VAT + Tax)</small>
        @endif
    </div>

    <!-- Tax Amount -->
    <div class="col-md-4 mb-3">
        <label class="form-label" for="tax_amount">Tax Amount (৳)</label>
        <input type="number" step="0.01" class="form-control calc-input" id="tax_amount" name="tax_amount"
               value="{{ old('tax_amount', $invoice->tax_amount ?? 0) }}" min="0">
    </div>

    <!-- Discount Amount -->
    <div class="col-md-4 mb-3">
        <label class="form-label" for="discount_amount">Discount Amount (৳)</label>
        <input type="number" step="0.01" class="form-control calc-input" id="discount_amount" name="discount_amount"
               value="{{ old('discount_amount', $invoice->discount_amount ?? 0) }}" min="0">
    </div>

    <!-- Total Amount (auto-calculated) -->
    <div class="col-md-4 mb-3">
        <label class="form-label" for="total_amount">Total Amount (৳)</label>
        <input type="number" step="0.01" class="form-control bg-light fw-bold" id="total_amount" name="total_amount"
               value="{{ old('total_amount', $invoice->total_amount ?? 0) }}" readonly>
        <small class="form-text text-muted">Subtotal + Tax - Discount</small>
    </div>

    <!-- Payment Status -->
    <div class="col-md-4 mb-3">
        <label class="form-label" for="payment_status">Payment Status <span class="text-danger">*</span></label>
        <select class="form-select" id="payment_status" name="payment_status" required>
            <option value="pending"  {{ old('payment_status', $invoice->payment_status ?? 'pending') == 'pending'  ? 'selected' : '' }}>Pending</option>
            <option value="partial"  {{ old('payment_status', $invoice->payment_status ?? '') == 'partial'  ? 'selected' : '' }}>Partial</option>
            <option value="paid"     {{ old('payment_status', $invoice->payment_status ?? '') == 'paid'     ? 'selected' : '' }}>Paid</option>
            <option value="overdue"  {{ old('payment_status', $invoice->payment_status ?? '') == 'overdue'  ? 'selected' : '' }}>Overdue</option>
        </select>
    </div>

    <!-- Paid Amount (conditional) -->
    <div class="col-md-4 mb-3" id="paid_amount_section">
        <label class="form-label" for="paid_amount">Paid Amount (৳)</label>
        <input type="number" step="0.01" class="form-control" id="paid_amount" name="paid_amount"
               value="{{ old('paid_amount', $invoice->paid_amount ?? 0) }}" min="0">
    </div>

    <!-- Payment Date (conditional) -->
    <div class="col-md-4 mb-3" id="payment_date_section">
        <label class="form-label" for="payment_date">Payment Date</label>
        <input type="date" class="form-control" id="payment_date" name="payment_date"
               value="{{ old('payment_date', isset($invoice) && $invoice->payment_date ? $invoice->payment_date->format('Y-m-d') : '') }}">
    </div>

    <!-- Payment Method (conditional) -->
    <div class="col-md-4 mb-3" id="payment_method_section">
        <label class="form-label" for="payment_method">Payment Method</label>
        <select class="form-select" id="payment_method" name="payment_method">
            <option value="">Select Method</option>
            <option value="cash"          {{ old('payment_method', $invoice->payment_method ?? '') == 'cash'          ? 'selected' : '' }}>Cash</option>
            <option value="bank_transfer" {{ old('payment_method', $invoice->payment_method ?? '') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
            <option value="check"         {{ old('payment_method', $invoice->payment_method ?? '') == 'check'         ? 'selected' : '' }}>Check</option>
            <option value="card"          {{ old('payment_method', $invoice->payment_method ?? '') == 'card'          ? 'selected' : '' }}>Card</option>
        </select>
    </div>

    <!-- Invoice File Upload -->
    <div class="col-md-6 mb-3">
        <label class="form-label" for="invoice_file">Invoice File</label>
        <input type="file" class="form-control" id="invoice_file" name="invoice_file" accept=".pdf,.jpg,.jpeg,.png">
        <small class="form-text text-muted">Accepted: PDF, JPG, PNG (max 5MB)</small>
        @if(isset($invoice) && $invoice->invoice_file_path)
            <div class="mt-1">
                <small class="text-success">
                    <i class="fa fa-file me-1"></i>
                    Current file:
                    <a href="{{ asset('storage/' . $invoice->invoice_file_path) }}" target="_blank">
                        View Uploaded File
                    </a>
                </small>
            </div>
        @endif
    </div>

    <!-- Remarks -->
    <div class="col-md-12 mb-3">
        <label class="form-label" for="remarks">Remarks</label>
        <textarea class="form-control" id="remarks" name="remarks" rows="3">{{ old('remarks', $invoice->remarks ?? '') }}</textarea>
    </div>
</div>

@section('scripts')
<script src="{{ asset('js/lib/jquery.min.js') }}"></script>
<script>
$(document).ready(function() {

    // Auto-calculate total amount
    function calculateTotal() {
        var subtotal = parseFloat($('#subtotal').val()) || 0;
        var tax      = parseFloat($('#tax_amount').val()) || 0;
        var discount = parseFloat($('#discount_amount').val()) || 0;
        var total    = subtotal + tax - discount;
        $('#total_amount').val(total.toFixed(2));
    }

    // Trigger on input change
    $('.calc-input').on('input', function() {
        calculateTotal();
    });

    // Run on page load to set initial value
    calculateTotal();

    // Show/hide payment fields based on status
    function togglePaymentFields() {
        var status = $('#payment_status').val();
        if (status === 'partial' || status === 'paid') {
            $('#paid_amount_section').show();
            $('#payment_date_section').show();
            $('#payment_method_section').show();
        } else {
            $('#paid_amount_section').hide();
            $('#payment_date_section').hide();
            $('#payment_method_section').hide();
        }
    }

    // Trigger on page load
    togglePaymentFields();

    // Trigger on status change
    $('#payment_status').change(function() {
        togglePaymentFields();
    });
});
</script>
@endsection