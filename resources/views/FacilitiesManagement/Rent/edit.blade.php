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
                <h2>Edit Rent</h2>
                <p>Update rent terms, increments, and security deposit schedule.</p>
            </div>
            <div class="rent-header-actions">
                <a href="{{ route('rent.show', $base) }}" class="btn btn-alt-secondary">
                    <i class="fa fa-eye me-1"></i> View
                </a>
                <a href="{{ route('rent.index') }}" class="btn btn-alt-secondary">
                    <i class="fa fa-arrow-left me-1"></i> Back
                </a>
            </div>
        </div>

        <div class="block block-rounded rent-shell">
            <div class="block-header block-header-default rent-block-header">
                <div>
                    <h3 class="block-title">{{ $base->agreement->agreement_ref_no ?? 'Rent Profile' }}</h3>
                    <div class="text-muted fs-sm">Last updated {{ optional($base->updated_at)->format('Y-m-d H:i') }}</div>
                </div>
            </div>
            <div class="block-content fs-sm data-content">
                <form action="{{ route('rent.update', $base->id) }}" method="POST" autocomplete="off">
                    @csrf
                    @method('PUT')
                    <!-- Add Rent Section -->
                    <section class="mb-4 p-3 border rounded rent-panel">
                        <h5 class="mb-3">Base Rent</h5>
                        <div class="row">
                            <div class="col-md-6 col-sm-12 mb-4">
                                <label class="form-label" for="agreement_id">
                                    Agreement

                                    <a id="viewAgreementBtn" href="javascript:void(0);" class="text-muted"
                                        style="pointer-events: none;" target="_blank" title="View Selected Agreement">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                </label>
                                <select id="agreement_id" name="agreement_id" class="form-select js-select2"
                                    data-placeholder="Select agreement" required>
                                    <option value=""></option>
                                    @foreach ($agreements as $agreement)
                                        <option value="{{ $agreement->id }}"
                                            {{ $agreement->id == $base->agreement_id ? 'selected' : '' }}>
                                            {{ $agreement->agreement_ref_no }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 col-sm-12 mb-4">
                                <label class="form-label" for="base_rent">Base Rent</label>
                                <input type="number" step="0.01" class="form-control" id="base_rent" name="base_rent"
                                    value="{{ old('base_rent', $base->base_rent) }}" required>
                            </div>
                            {{-- <div class="col-md-6 col-sm-12 mb-4">
                                <label class="form-label" for="vat">VAT</label>
                                <input type="number" step="0.01" class="form-control" id="vat" name="vat"
                                    value="{{ old('vat', $base->vat) }}">
                            </div>
                            <div class="col-md-6 col-sm-12 mb-4">
                                <label class="form-label" for="tax">Tax</label>
                                <input type="number" step="0.01" class="form-control" id="tax" name="tax"
                                    value="{{ old('tax', $base->tax) }}">
                            </div> --}}
                            <div class="col-md-6 col-sm-12 mb-4">
                                <label class="form-label" for="is_at_source">Is At Source</label>
                                <select class="form-select" id="is_at_source" name="is_at_source">
                                    <option value="">Select</option>
                                    <option value="1"
                                        {{ old('is_at_source', $base->is_at_source) == '1' ? 'selected' : '' }}>
                                        Yes
                                    </option>
                                    <option value="0"
                                        {{ old('is_at_source', $base->is_at_source) == '0' ? 'selected' : '' }}>
                                        No
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-6 col-sm-12 mb-4">
                                <label class="form-label" for="rent_type">Rent Type</label>
                                {{-- <input type="text" class="form-control" id="rent_type" name="rent_type"
                                    value="{{ old('rent_type', $base->rent_type) }}"> --}}
                                <select class="form-select" id="rent_type" name="rent_type">
                                    <option value="">Select</option>
                                    <option value="Monthly" {{ $base->rent_type == 'Monthly' ? 'selected' : '' }}>Monthly
                                    </option>
                                    <option value="Quarterly" {{ $base->rent_type == 'Quarterly' ? 'selected' : '' }}>
                                        Quarterly</option>
                                    <option value="Half Yearly" {{ $base->rent_type == 'Half Yearly' ? 'selected' : '' }}>
                                        Half Yearly</option>
                                    <option value="Yearly" {{ $base->rent_type == 'Yearly' ? 'selected' : '' }}>Yearly
                                    </option>
                                </select>
                            </div>
                            {{-- <div class="col-md-6 col-sm-12 mb-4">
                            <label class="form-label" for="start_date">Start Date</label>
                            <input type="date" class="form-control" id="start_date" name="start_date"
                                value="{{ old('start_date', $base->start_date) }}" required>
                        </div>
                        <div class="col-md-6 col-sm-12 mb-4">
                            <label class="form-label" for="end_date">End Date</label>
                            <input type="date" class="form-control" id="end_date" name="end_date"
                                value="{{ old('end_date', $base->end_date) }}">
                        </div> --}}
                            <div class="col-md-12 mb-4">
                                <label class="form-label" for="remarks">Remarks</label>
                                <textarea class="form-control" id="remarks" name="remarks">{{ old('remarks', $base->remarks) }}</textarea>
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
                            <tbody>
                                @foreach ($base->increments as $ikey => $inc)
                                    <tr>
                                        <td><input type="date"
                                                name="increments[{{ $ikey }}][increment_start_date]"
                                                class="form-control" value="{{ $inc->increment_start_date }}" required>
                                        </td>
                                        <td><input type="date"
                                                name="increments[{{ $ikey }}][increment_end_date]"
                                                class="form-control" value="{{ $inc->increment_end_date }}"></td>
                                        <td><input type="number" step="0.01"
                                                name="increments[{{ $ikey }}][increment_amount]"
                                                class="form-control inc-amount" value="{{ $inc->increment_amount }}"
                                                required></td>
                                        <td><input type="number" step="0.01"
                                                name="increments[{{ $ikey }}][increment_percentage]"
                                                class="form-control inc-percent" value="{{ $inc->increment_percentage }}">
                                        </td>
                                        <td><input type="text"
                                                name="increments[{{ $ikey }}][method_description]"
                                                class="form-control" value="{{ $inc->method_description }}"></td>
                                        <td><button type="button"
                                                class="btn btn-alt-danger btn-sm remove-increment">Remove</button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
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
                                    value="{{ isset($base->agreement->securityDeposits[0]) ? $base->agreement->securityDeposits[0]->security_deposit_total : '' }}">
                            </div>
                            <div class="col-md-4">
                                <label>Absorbable</label>
                                <input type="number" step="0.01" name="security_deposit_absorbable"
                                    class="form-control"
                                    value="{{ isset($base->agreement->securityDeposits[0]) ? $base->agreement->securityDeposits[0]->security_deposit_absorbable : '' }}">
                            </div>
                            <div class="col-md-4">
                                <label>Non-Absorbable</label>
                                <input type="number" step="0.01" name="security_deposit_non_absorbable"
                                    class="form-control"
                                    value="{{ isset($base->agreement->securityDeposits[0]) ? $base->agreement->securityDeposits[0]->security_deposit_non_absorbable : '' }}">
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
                            <tbody>
                                @if (isset($base->agreement) && $base->agreement->securityDeposits)
                                    @foreach ($base->agreement->securityDeposits as $dkey => $deposit)
                                        <tr>
                                            <td><input type="number" step="0.01"
                                                    name="deposits[{{ $dkey }}][absorb_amount]"
                                                    class="form-control abs-amount"
                                                    value="{{ $deposit->absorb_amount }}"></td>
                                            <td><input type="number" step="0.01"
                                                    name="deposits[{{ $dkey }}][absorb_amount_percentage]"
                                                    class="form-control abs-percent"
                                                    value="{{ $deposit->absorb_amount_percentage }}"></td>
                                            <td><input type="date"
                                                    name="deposits[{{ $dkey }}][absorb_start_date]"
                                                    class="form-control" value="{{ $deposit->absorb_start_date }}"></td>
                                            <td><input type="date"
                                                    name="deposits[{{ $dkey }}][absorb_end_date]"
                                                    class="form-control" value="{{ $deposit->absorb_end_date }}"></td>
                                            {{-- <td><input type="text"
                                                    name="deposits[{{ $dkey }}][absorb_frequency]"
                                                    class="form-control" value="{{ $deposit->absorb_frequency }}"></td> --}}
                                            <td><input type="text"
                                                    name="deposits[{{ $dkey }}][method_description]"
                                                    class="form-control" value="{{ $deposit->method_description }}"></td>
                                            <td><button type="button"
                                                    class="btn btn-alt-danger btn-sm remove-deposit">Remove</button>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                        <button type="button" class="btn btn-alt-success" id="addDeposit">Add Deposit</button>
                    </section>
                    <div class="rent-action-bar">
                        <a href="{{ route('rent.index') }}" class="btn btn-alt-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-check me-1"></i> Update Rent
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endsection

    @section('scripts')
        <script src="{{ asset('js/plugins/select2/js/select2.full.js') }}"></script>
        <script>
            One.helpersOnLoad(['jq-select2']);

            // document.addEventListener('DOMContentLoaded', function() {
            //     if (window.jQuery) {
            //         $('#addIncrement').click(function() {
            //             $('#incrementsTable tbody').append(`
    //         <tr>
    //             <td><input type="date" name="increments[][increment_date]" class="form-control" required></td>
    //             <td><input type="number" step="0.01" name="increments[][increment_amount]" class="form-control" required></td>
    //             <td><input type="number" step="0.01" name="increments[][increment_percentage]" class="form-control"></td>
    //             <td><input type="text" name="increments[][remarks]" class="form-control"></td>
    //             <td><button type="button" class="btn btn-danger btn-sm remove-increment">Remove</button></td>
    //         </tr>
    //     `);
            //         });
            //         $(document).on('click', '.remove-increment', function() {
            //             $(this).closest('tr').remove();
            //         });
            //     }
            // });


            $(function() {
                let incrementIndex = {{ $base->increments->count() }};

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

                let depositIndex =
                    {{ isset($base->agreement->securityDeposits) ? $base->agreement->securityDeposits->count() : 0 }};
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
            });


            $(document).ready(function() {

                function updateAgreementLink() {

                    let agreementId = $('#agreement_id').val();
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
                }

                // Run on dropdown change
                $('#agreement_id').on('change', function() {
                    updateAgreementLink();
                });

                // IMPORTANT → Run on page load (Edit page needs this)
                updateAgreementLink();
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
