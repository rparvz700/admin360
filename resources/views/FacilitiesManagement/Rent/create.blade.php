@extends('Partials.app', ['activeMenu' => 'rent'])

@section('title')
    {{ config('app.name') }}
@endsection

@section('page_title')
    Add Rent
@endsection

@section('styles')
    <link rel="stylesheet" href="{{ asset('js/plugins/select2/css/select2.min.css') }}">
@endsection

@section('content')
    <div class="content">
        <div class="rent-page-header">
            <div>
                <div class="rent-eyebrow">Facilities Management</div>
                <h2>Create Rent</h2>
                <p>Set base rent terms, increments, and security deposit structure.</p>
            </div>
            <a href="{{ route('rent.index') }}" class="btn btn-alt-secondary">
                <i class="fa fa-arrow-left me-1"></i> Back
            </a>
        </div>

        <div class="block block-rounded rent-shell">
            <div class="block-header block-header-default rent-block-header">
                <div>
                    <h3 class="block-title">Rent Profile</h3>
                    <div class="text-muted fs-sm">Configure base rent and periodic adjustments.</div>
                </div>
            </div>
            <div class="block-content fs-sm data-content">
                <form action="{{ route('rent.store') }}" method="POST" autocomplete="off">
                    @csrf
                    <!-- Add Rent Section -->
                    <section class="mb-4 p-3 border rounded rent-panel">
                        <h5 class="mb-3">Base Rent</h5>
                        <div class="row">
                            <div class="col-md-6 col-sm-12 mb-4">
                                <label class="form-label" for="agreement_id">
                                    Agreement

                                    <a id="viewAgreementBtn" href="javascript:void(0);" class="text-muted"
                                        style="pointer-events: none;" title="View Selected Agreement" target="_blank">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                </label>
                                {{-- <input type="text" class="form-control" id="agreement_id" name="agreement_id"
                                    value="{{ old('agreement_id') }}" required> --}}

                                <select id="agreement_id" name="agreement_id" class="form-select js-select2"
                                    data-placeholder="Select agreement" required>
                                    <option value=""></option>
                                    @foreach ($agreements as $agreement)
                                        <option value="{{ $agreement->id }}">{{ $agreement->agreement_ref_no }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 col-sm-12 mb-4">
                                <label class="form-label" for="base_rent">Base Rent</label>
                                <input type="number" step="0.01" class="form-control" id="base_rent" name="base_rent"
                                    value="{{ old('base_rent') }}" required>
                            </div>
                            {{-- <div class="col-md-6 col-sm-12 mb-4">
                                <label class="form-label" for="vat">VAT</label>
                                <input type="number" step="0.01" class="form-control" id="vat" name="vat"
                                    value="{{ old('vat') }}">
                            </div>
                            <div class="col-md-6 col-sm-12 mb-4">
                                <label class="form-label" for="tax">Tax</label>
                                <input type="number" step="0.01" class="form-control" id="tax" name="tax"
                                    value="{{ old('tax') }}">
                            </div> --}}
                            <div class="col-md-6 col-sm-12 mb-4">
                                <label class="form-label" for="is_at_source">Is At Source</label>
                                <select class="form-select" id="is_at_source" name="is_at_source">
                                    <option value="">Select</option>
                                    <option value="1" {{ old('is_at_source') == '1' ? 'selected' : '' }}>Yes</option>
                                    <option value="0" {{ old('is_at_source') == '0' ? 'selected' : '' }}>No</option>
                                </select>
                            </div>
                            <div class="col-md-6 col-sm-12 mb-4">
                                <label class="form-label" for="rent_type">Rent Type</label>
                                <select class="form-select" id="rent_type" name="rent_type">
                                    <option value="">Select</option>
                                    <option value="Monthly">Monthly</option>
                                    <option value="Quarterly">Quarterly</option>
                                    <option value="Half Yearly">Half Yearly</option>
                                    <option value="Yearly">Yearly</option>
                                </select>
                                {{-- <input type="text" class="form-control" id="rent_type" name="rent_type"
                                    value="{{ old('rent_type') }}"> --}}
                            </div>
                            {{-- <div class="col-md-6 col-sm-12 mb-4">
                                <label class="form-label" for="start_date">Start Date</label>
                                <input type="date" class="form-control" id="start_date" name="start_date"
                                    value="{{ old('start_date') }}" required>
                            </div>
                            <div class="col-md-6 col-sm-12 mb-4">
                                <label class="form-label" for="end_date">End Date</label>
                                <input type="date" class="form-control" id="end_date" name="end_date"
                                    value="{{ old('end_date') }}">
                            </div> --}}
                            <div class="col-md-12 mb-4">
                                <label class="form-label" for="remarks">Remarks</label>
                                <textarea class="form-control" id="remarks" name="remarks">{{ old('remarks') }}</textarea>
                            </div>
                        </div>
                    </section>

                    <!-- Rent Increments Section -->
                    <section class="mb-4 p-3 border rounded rent-panel">
                        <h5 class="mb-3">Rent Increments</h5>
                        <table class="table table-bordered" id="incrementsTable">
                            <thead>
                                <tr>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Amount</th>
                                    <th>Percentage</th>
                                    <th>Method Description</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                        <button type="button" class="btn btn-alt-success" id="addIncrement">Add Increment</button>
                    </section>

                    <!-- Security Deposits Section -->
                    <section class="mb-4 p-3 border rounded rent-panel">
                        <h5 class="mb-3">Security Deposits</h5>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label>Total</label>
                                <input type="number" step="0.01" name="security_deposit_total" class="form-control"
                                    value="{{ old('security_deposit_total') }}">
                            </div>
                            <div class="col-md-4">
                                <label>Absorbable</label>
                                <input type="number" step="0.01" name="security_deposit_absorbable" class="form-control"
                                    value="{{ old('security_deposit_absorbable') }}">
                            </div>
                            <div class="col-md-4">
                                <label>Non-Absorbable</label>
                                <input type="number" step="0.01" name="security_deposit_non_absorbable"
                                    class="form-control" value="{{ old('security_deposit_non_absorbable') }}">
                            </div>
                        </div>
                        <table class="table table-bordered" id="depositsTable">
                            <thead>
                                <tr>
                                    <th>Absorb Amount</th>
                                    <th>Absorb %</th>
                                    <th>Absorb Start</th>
                                    <th>Absorb End</th>
                                    {{-- <th>Absorb Freq</th> --}}
                                    <th>Method Desc</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                        <button type="button" class="btn btn-alt-success" id="addDeposit">Add Deposit</button>
                    </section>
                    <div class="rent-action-bar">
                        <a href="{{ route('rent.index') }}" class="btn btn-alt-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-check me-1"></i> Save Rent
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('js/plugins/select2/js/select2.full.js') }}"></script>
    <script>
        One.helpersOnLoad(['jq-select2']);
        document.addEventListener('DOMContentLoaded', function() {
            if (window.jQuery) {
                let incrementIndex = 0;

                $('#addIncrement').click(function() {

                    $('#incrementsTable tbody').append(`
                        <tr>
                            <td><input type="date" name="increments[${incrementIndex}][increment_start_date]" class="form-control" required></td>
                            <td><input type="date" name="increments[${incrementIndex}][increment_end_date]" class="form-control"></td>
                            <td><input type="number" step="0.01" name="increments[${incrementIndex}][increment_amount]" class="form-control inc-amount" required></td>
                            <td><input type="number" step="0.01" name="increments[${incrementIndex}][increment_percentage]" class="form-control inc-percent"></td>
                            <td><input type="text" name="increments[${incrementIndex}][method_description]" class="form-control"></td>
                            <td><button type="button" class="btn btn-alt-danger btn-sm remove-increment">Remove</button></td>
                        </tr>
                    `);

                    incrementIndex++;
                });
                $(document).on('click', '.remove-increment', function() {
                    $(this).closest('tr').remove();
                });

                let depositIndex = 0;

                $('#addDeposit').click(function() {

                    $('#depositsTable tbody').append(`
                        <tr>
                            <td><input type="number" step="0.01" name="deposits[${depositIndex}][absorb_amount]" class="form-control abs-amount"></td>
                            <td><input type="number" step="0.01" name="deposits[${depositIndex}][absorb_amount_percentage]" class="form-control abs-percent"></td>
                            <td><input type="date" name="deposits[${depositIndex}][absorb_start_date]" class="form-control"></td>
                            <td><input type="date" name="deposits[${depositIndex}][absorb_end_date]" class="form-control"></td>
                            <td><input type="text" name="deposits[${depositIndex}][method_description]" class="form-control"></td>
                            <td><button type="button" class="btn btn-alt-danger btn-sm remove-deposit">Remove</button></td>
                        </tr>
                    `);

                    depositIndex++;
                });
                $(document).on('click', '.remove-deposit', function() {
                    $(this).closest('tr').remove();
                });
            }
        });

        $('#agreement_id').on('change', function() {

            let agreementId = $(this).val();
            let viewBtn = $('#viewAgreementBtn');

            if (agreementId) {

                let url = "{{ route('agreements.show', ':id') }}";
                url = url.replace(':id', agreementId);

                viewBtn
                    .attr('href', url)
                    .css('pointer-events', 'auto')
                    .removeClass('text-muted')
                    .addClass('text-primary');

            } else {

                viewBtn
                    .attr('href', 'javascript:void(0);')
                    .css('pointer-events', 'none')
                    .removeClass('text-primary')
                    .addClass('text-muted');
            }
        });

        function getBaseRent() {
            return parseFloat($('#base_rent').val()) || 0;
        }

        // If Amount is typed, calculate Percentage
        $(document).on('input', '.inc-amount', function() {
            let baseRent = getBaseRent();
            let amount = parseFloat($(this).val()) || 0;
            let row = $(this).closest('tr');

            if (baseRent > 0) {
                let percentage = (amount / baseRent) * 100;
                row.find('.inc-percent').val(percentage.toFixed(2));
            }
        });

        // If Percentage is typed, calculate Amount
        $(document).on('input', '.inc-percent', function() {
            let baseRent = getBaseRent();
            let percentage = parseFloat($(this).val()) || 0;
            let row = $(this).closest('tr');

            if (baseRent > 0) {
                let amount = (percentage / 100) * baseRent;
                row.find('.inc-amount').val(amount.toFixed(2));
            }
        });

        $(document).on('input', '.abs-amount', function() {
            let baseRent = getBaseRent();
            let amount = parseFloat($(this).val()) || 0;
            let row = $(this).closest('tr');

            if (baseRent > 0) {
                let percentage = (amount / baseRent) * 100;
                row.find('.abs-percent').val(percentage.toFixed(2));
            }
        });

        $(document).on('input', '.abs-percent', function() {
            let baseRent = getBaseRent();
            let percentage = parseFloat($(this).val()) || 0;
            let row = $(this).closest('tr');

            if (baseRent > 0) {
                let amount = (percentage / 100) * baseRent;
                row.find('.abs-amount').val(amount.toFixed(2));
            }
        });

        // Optional: Re-calculate all rows if Base Rent changes
        $('#base_rent').on('input', function() {
            let baseRent = parseFloat($(this).val()) || 0;
            if (baseRent > 0) {
                $('.inc-percent').each(function() {
                    let row = $(this).closest('tr');
                    let percentage = parseFloat($(this).val()) || 0;
                    if (percentage > 0) {
                        let amount = (percentage / 100) * baseRent;
                        row.find('.inc-amount').val(amount.toFixed(2));
                    }
                });

                $('.abs-percent').each(function() {
                    let row = $(this).closest('tr');
                    let percentage = parseFloat($(this).val()) || 0;
                    if (percentage > 0) {
                        let amount = (percentage / 100) * baseRent;
                        row.find('.abs-amount').val(amount.toFixed(2));
                    }
                });
            }
        });

        // --- End Calculation Logic ---
    </script>
@endsection
