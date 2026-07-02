--- START OF FILE Paste May 11, 2026 - 3:33PM ---

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
                                            {{ $agreement->id == old('agreement_id', $base->agreement_id) ? 'selected' : '' }}>
                                            {{ $agreement->agreement_ref_no }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 col-sm-12 mb-4">
                                <label class="form-label" for="base_rent">Base Rent</label>
                                <input type="number" step="0.01" class="form-control" id="base_rent" name="base_rent"
                                    value="{{ old('base_rent', $base->base_rent) }}" required>
                            </div>
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
                                <select class="form-select" id="rent_type" name="rent_type">
                                    <option value="">Select</option>
                                    <option value="Monthly"
                                        {{ old('rent_type', $base->rent_type) == 'Monthly' ? 'selected' : '' }}>Monthly
                                    </option>
                                    <option value="Quarterly"
                                        {{ old('rent_type', $base->rent_type) == 'Quarterly' ? 'selected' : '' }}>
                                        Quarterly</option>
                                    <option value="Half Yearly"
                                        {{ old('rent_type', $base->rent_type) == 'Half Yearly' ? 'selected' : '' }}>
                                        Half Yearly</option>
                                    <option value="Yearly"
                                        {{ old('rent_type', $base->rent_type) == 'Yearly' ? 'selected' : '' }}>Yearly
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-12 mb-4">
                                <label class="form-label" for="remarks">Remarks</label>
                                <textarea class="form-control" id="remarks" name="remarks">{{ old('remarks', $base->remarks) }}</textarea>
                            </div>
                        </div>
                    </section>

                    <!-- Utilities & Service Charges Section -->
                    <section class="mb-4 p-3 border rounded rent-panel">
                        <h5 class="mb-3">Utilities & Service Charges</h5>
                        <div class="table-responsive mb-3">
                            <table class="table table-bordered table-sm wizard-table" id="utilitiesTable">
                                <thead>
                                    <tr>
                                        <th>Utility Type</th>
                                        <th>Monthly Amount</th>
                                        <th>Disburse with Rent</th>
                                        <th style="width: 80px;" class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($utilityTypes as $type)
                                        @php
                                            $existing = $agreementUtilities->get($type->id);
                                        @endphp
                                        @if ($existing)
                                            <tr data-id="{{ $type->id }}">
                                                <td class="align-middle fw-semibold">
                                                    {{ $type->name }}
                                                    <input type="hidden" name="utilities[{{ $type->id }}][id]" value="{{ $type->id }}">
                                                </td>
                                                <td>
                                                    <div class="input-group input-group-sm">
                                                        <span class="input-group-text">৳</span>
                                                        <input type="number" step="0.01" class="form-control form-control-sm" 
                                                               name="utilities[{{ $type->id }}][amount]" 
                                                               value="{{ old('utilities.' . $type->id . '.amount', $existing->amount) }}" required>
                                                    </div>
                                                </td>
                                                <td class="align-middle">
                                                    <div class="form-check form-switch mb-0">
                                                        <input class="form-check-input" type="checkbox" 
                                                               name="utilities[{{ $type->id }}][disburse_with_rent]" value="1"
                                                               {{ old('utilities.' . $type->id . '.disburse_with_rent', $existing->disburse_with_rent) ? 'checked' : '' }}>
                                                        <label class="form-check-label fs-xs">Disburse with Rent</label>
                                                    </div>
                                                </td>
                                                <td class="text-center align-middle">
                                                    <button type="button" class="btn btn-sm btn-alt-danger remove-utility-row">
                                                        <i class="fa fa-times"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="row g-2 align-items-center">
                            <div class="col-md-4 col-sm-6">
                                <select id="utility_type_selector" class="form-select">
                                    <option value="">Choose Utility...</option>
                                    @foreach ($utilityTypes as $type)
                                        <option value="{{ $type->id }}" data-name="{{ $type->name }}"
                                            {{ $agreementUtilities->has($type->id) ? 'disabled' : '' }}>
                                            {{ $type->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 col-sm-6">
                                <button type="button" class="btn btn-alt-primary" id="addUtilityRowBtn">
                                    <i class="fa fa-plus me-1"></i> Add Utility
                                </button>
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
                                    <th>Years</th> {{-- NEW FIELD HEADER --}}
                                    <th>End Date</th>
                                    <th>Amount</th>
                                    <th>Percentage</th>
                                    <th>Method Description</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach (old('increments', $base->increments) as $ikey => $inc)
                                    <tr>
                                        <td><input type="date"
                                                name="increments[{{ $ikey }}][increment_start_date]"
                                                class="form-control inc-start-date"
                                                value="{{ old('increments.' . $ikey . '.increment_start_date', $inc->increment_start_date ?? '') }}"
                                                required>
                                        </td>
                                        <td><input type="number" name="increments[{{ $ikey }}][years]"
                                                class="form-control inc-years" min="1"
                                                value="{{ old('increments.' . $ikey . '.years', $inc->years ?? '') }}"
                                                required>
                                        </td>
                                        <td><input type="date"
                                                name="increments[{{ $ikey }}][increment_end_date]"
                                                class="form-control inc-end-date"
                                                value="{{ old('increments.' . $ikey . '.increment_end_date', $inc->increment_end_date ?? '') }}"
                                                required></td>
                                        <td><input type="number" step="0.01"
                                                name="increments[{{ $ikey }}][increment_amount]"
                                                class="form-control inc-amount"
                                                value="{{ old('increments.' . $ikey . '.increment_amount', $inc->increment_amount ?? '') }}"
                                                required></td>
                                        <td><input type="number" step="0.01"
                                                name="increments[{{ $ikey }}][increment_percentage]"
                                                class="form-control inc-percent"
                                                value="{{ old('increments.' . $ikey . '.increment_percentage', $inc->increment_percentage ?? '') }}">
                                        </td>
                                        <td><input type="text"
                                                name="increments[{{ $ikey }}][method_description]"
                                                class="form-control"
                                                value="{{ old('increments.' . $ikey . '.method_description', $inc->method_description ?? '') }}">
                                        </td>
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
                        @error('deposits')
                            <div class="alert alert-danger py-2">{{ $message }}</div>
                        @enderror
                        @php
                            $securityDeposits = $base->agreement->securityDeposits ?? collect();
                            // If old deposits exist from validation, use them first
                            $depositRows = collect(
                                old(
                                    'deposits',
                                    $securityDeposits
                                        ->filter(function ($deposit) {
                                            // Filter out empty deposits from DB to not show unnecessary rows
                                            return filled($deposit->absorb_amount) ||
                                                filled($deposit->absorb_start_date) ||
                                                filled($deposit->absorb_end_date) ||
                                                filled($deposit->method_description) ||
                                                filled($deposit->absorb_frequency);
                                        })
                                        ->values(),
                                ),
                            );
                            // For summary fields, check old data first, then DB, then empty object
                            $securityDepositSummary = (object) [
                                'security_deposit_total' => old(
                                    'security_deposit_total',
                                    $securityDeposits->first()->security_deposit_total ?? '',
                                ),
                                'security_deposit_absorbable' => old(
                                    'security_deposit_absorbable',
                                    $securityDeposits->first()->security_deposit_absorbable ?? '',
                                ),
                                'security_deposit_non_absorbable' => old(
                                    'security_deposit_non_absorbable',
                                    $securityDeposits->first()->security_deposit_non_absorbable ?? '',
                                ),
                            ];
                        @endphp
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Total</label>
                                <input type="number" step="0.01" name="security_deposit_total" class="form-control"
                                    value="{{ $securityDepositSummary->security_deposit_total }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Adjustable</label>
                                <input type="number" step="0.01" name="security_deposit_absorbable"
                                    class="form-control"
                                    value="{{ $securityDepositSummary->security_deposit_absorbable }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Non-Adjustable</label>
                                <input type="number" step="0.01" name="security_deposit_non_absorbable"
                                    class="form-control"
                                    value="{{ $securityDepositSummary->security_deposit_non_absorbable }}">
                            </div>
                        </div>
                        <table class="table table-bordered" id="depositsTable">
                            <thead>
                                <tr>
                                    <th>Adjust Amount</th>
                                    <th>Month Interval</th>
                                    <th>Adjust / Month</th>
                                    <th>Adjust Start</th>
                                    <th>Adjust End</th>
                                    <th>Method Desc</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if ($depositRows->isNotEmpty())
                                    @foreach ($depositRows as $dkey => $deposit)
                                        <tr>
                                            <td><input type="number" step="0.01"
                                                    name="deposits[{{ $dkey }}][absorb_amount]"
                                                    class="form-control abs-amount"
                                                    value="{{ old('deposits.' . $dkey . '.absorb_amount', $deposit->absorb_amount ?? '') }}">
                                            </td>
                                            <td><input type="number" name="deposits[{{ $dkey }}][month_interval]"
                                                    class="form-control dep-months" min="1"
                                                    value="{{ old('deposits.' . $dkey . '.month_interval', $deposit->absorb_frequency ?? '') }}"
                                                    required></td>
                                            <td><input type="number" step="0.01"
                                                    name="deposits[{{ $dkey }}][adjust_per_month]"
                                                    class="form-control dep-per-month"
                                                    value="{{ old('deposits.' . $dkey . '.adjust_per_month') }}"
                                                    readonly>
                                            </td>
                                            <td><input type="date"
                                                    name="deposits[{{ $dkey }}][absorb_start_date]"
                                                    class="form-control dep-start-date"
                                                    value="{{ old('deposits.' . $dkey . '.absorb_start_date', $deposit->absorb_start_date ?? '') }}">
                                            </td>
                                            <td><input type="date"
                                                    name="deposits[{{ $dkey }}][absorb_end_date]"
                                                    class="form-control dep-end-date"
                                                    value="{{ old('deposits.' . $dkey . '.absorb_end_date', $deposit->absorb_end_date ?? '') }}"
                                                    required></td>
                                            <td><input type="text"
                                                    name="deposits[{{ $dkey }}][method_description]"
                                                    class="form-control"
                                                    value="{{ old('deposits.' . $dkey . '.method_description', $deposit->method_description ?? '') }}">
                                            </td>
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
        <script src="{{ asset('js/plugins/bootstrap-notify/bootstrap-notify.min.js') }}"></script>
    @section('scripts')
        <script src="{{ asset('js/plugins/select2/js/select2.full.js') }}"></script>
        <script src="{{ asset('js/plugins/bootstrap-notify/bootstrap-notify.min.js') }}"></script>
        <script>
            One.helpersOnLoad(['jq-select2', 'jq-notify']);

            // --- Date Calculation Functions (Copied from previous examples, renamed for generality) ---
            function calculateEndDate(startDateStr, years) {
                if (!startDateStr || !years || years <= 0) {
                    return '';
                }
                const startDate = new Date(startDateStr + 'T00:00:00'); // Add time to ensure correct date interpretation
                if (isNaN(startDate.getTime())) {
                    return ''; // Invalid date
                }

                const endDate = new Date(startDate); // Start with the start date
                endDate.setFullYear(startDate.getFullYear() + parseInt(years, 10)); // Add years
                endDate.setDate(endDate.getDate() - 1); // Subtract one day

                // Format date to YYYY-MM-DD
                const year = endDate.getFullYear();
                const month = String(endDate.getMonth() + 1).padStart(2, '0');
                const day = String(endDate.getDate()).padStart(2, '0');
                return `${year}-${month}-${day}`;
            }

            function calculateNextStartDate(endDateStr) {
                if (!endDateStr) {
                    return '';
                }
                const endDate = new Date(endDateStr + 'T00:00:00'); // Add time to ensure correct date interpretation
                if (isNaN(endDate.getTime())) {
                    return ''; // Invalid date
                }
                const nextStartDate = new Date(endDate);
                nextStartDate.setDate(endDate.getDate() + 1);

                const year = nextStartDate.getFullYear();
                const month = String(nextStartDate.getMonth() + 1).padStart(2, '0');
                const day = String(nextStartDate.getDate()).padStart(2, '0');
                return `${year}-${month}-${day}`;
            }

            // --- NEW FUNCTION: Calculate years from a given start and end date ---
            function calculateYearsFromDates(startDateStr, endDateStr) {
                if (!startDateStr || !endDateStr) {
                    return '';
                }

                const startDate = new Date(startDateStr + 'T00:00:00');
                const endDate = new Date(endDateStr + 'T00:00:00');

                if (isNaN(startDate.getTime()) || isNaN(endDate.getTime())) {
                    return ''; // Invalid date
                }

                // Our `calculateEndDate` function produces an end date that is (start_date + years - 1 day).
                // So, to reverse, we need to consider (end_date + 1 day) as the "effective end date"
                // that would have been the anniversary if the day wasn't subtracted.
                const effectiveEndDate = new Date(endDate);
                effectiveEndDate.setDate(endDate.getDate() + 1);

                let years = effectiveEndDate.getFullYear() - startDate.getFullYear();

                // Adjust if the "anniversary" of the start date hasn't been reached yet in the effective end year
                // This means if effectiveEndDate is before the start date's day/month in the effectiveEndDate's year
                if (effectiveEndDate.getMonth() < startDate.getMonth() ||
                    (effectiveEndDate.getMonth() === startDate.getMonth() && effectiveEndDate.getDate() < startDate.getDate())
                ) {
                    years--;
                }

                return years > 0 ? years.toString() : ''; // Return as string, or empty if <= 0
            }

            function calculateMonthEndDate(startDateStr, months) {
                if (!startDateStr || !months || months <= 0) {
                    return '';
                }

                const startDate = new Date(startDateStr + 'T00:00:00');
                if (isNaN(startDate.getTime())) {
                    return '';
                }

                const endDate = new Date(startDate.getFullYear(), startDate.getMonth() + parseInt(months, 10),
                    startDate.getDate());
                endDate.setDate(endDate.getDate() - 1);

                const year = endDate.getFullYear();
                const month = String(endDate.getMonth() + 1).padStart(2, '0');
                const day = String(endDate.getDate()).padStart(2, '0');
                return `${year}-${month}-${day}`;
            }

            function calculateMonthsFromDates(startDateStr, endDateStr) {
                if (!startDateStr || !endDateStr) {
                    return '';
                }

                const startDate = new Date(startDateStr + 'T00:00:00');
                const endDate = new Date(endDateStr + 'T00:00:00');
                if (isNaN(startDate.getTime()) || isNaN(endDate.getTime())) {
                    return '';
                }

                const effectiveEndDate = new Date(endDate);
                effectiveEndDate.setDate(endDate.getDate() + 1);

                let months = (effectiveEndDate.getFullYear() - startDate.getFullYear()) * 12;
                months += effectiveEndDate.getMonth() - startDate.getMonth();
                if (effectiveEndDate.getDate() < startDate.getDate()) {
                    months--;
                }

                return months > 0 ? months.toString() : '';
            }

            function updateDepositPerMonth(row) {
                const amount = parseFloat(row.find('.abs-amount').val()) || 0;
                const months = parseInt(row.find('.dep-months').val(), 10) || 0;
                row.find('.dep-per-month').val(amount > 0 && months > 0 ? (amount / months).toFixed(2) : '');
            }

            // --- Rent Increment specific functions ---
            function updateSubsequentIncrements(changedRow) {
                let currentRow = changedRow;
                let currentEndDate = changedRow.find('.inc-end-date').val();

                while (currentRow.length) {
                    const nextRow = currentRow.next('tr');
                    if (!nextRow.length) break; // No more rows to update

                    const nextStartDateInput = nextRow.find('.inc-start-date');
                    const nextYearsInput = nextRow.find('.inc-years');
                    const nextEndDateInput = nextRow.find('.inc-end-date');

                    const newNextStartDate = calculateNextStartDate(currentEndDate);

                    // Only update start date if it's different to prevent unnecessary DOM writes
                    if (nextStartDateInput.val() !== newNextStartDate) {
                        nextStartDateInput.val(newNextStartDate);
                    }

                    const nextYears = nextYearsInput.val();
                    const newNextEndDate = calculateEndDate(newNextStartDate, nextYears);

                    // Only update end date if it's different
                    if (nextEndDateInput.val() !== newNextEndDate) {
                        nextEndDateInput.val(newNextEndDate);
                    }

                    currentEndDate = newNextEndDate; // Propagate for the next iteration
                    currentRow = nextRow; // Move to the next row
                    if (!currentEndDate) { // If a row in the chain results in an invalid end date, stop propagating
                        break;
                    }
                }
            }

            // --- Security Deposit specific functions ---
            function updateSubsequentDeposits(changedRow) {
                let currentRow = changedRow;
                let currentEndDate = changedRow.find('.dep-end-date').val();

                while (currentRow.length) {
                    const nextRow = currentRow.next('tr');
                    if (!nextRow.length) break; // No more rows to update

                    const nextStartDateInput = nextRow.find('.dep-start-date');
                    const nextMonthsInput = nextRow.find('.dep-months');
                    const nextEndDateInput = nextRow.find('.dep-end-date');

                    const newNextStartDate = calculateNextStartDate(currentEndDate);

                    if (nextStartDateInput.val() !== newNextStartDate) {
                        nextStartDateInput.val(newNextStartDate);
                    }

                    const nextMonths = nextMonthsInput.val();
                    const newNextEndDate = calculateMonthEndDate(newNextStartDate, nextMonths);

                    if (nextEndDateInput.val() !== newNextEndDate) {
                        nextEndDateInput.val(newNextEndDate);
                    }

                    currentEndDate = newNextEndDate;
                    currentRow = nextRow;
                    updateDepositPerMonth(nextRow);
                    if (!currentEndDate) {
                        break;
                    }
                }
            }


            $(document).ready(function() {

                // --- Agreement Link Update ---
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
                $('#agreement_id').on('change', updateAgreementLink).trigger('change'); // Run on load and change


                // --- Rent Increment Logic ---
                // Initialize index for new rows, accounting for existing and old data
                let incrementIndex = {{ max($base->increments->count(), count(old('increments', []))) }};

                $('#addIncrement').click(function() {
                    const lastRow = $('#incrementsTable tbody tr').last();
                    let newStartDate = '';

                    if (lastRow.length) {
                        const prevEndDate = lastRow.find('.inc-end-date').val();
                        if (prevEndDate) {
                            newStartDate = calculateNextStartDate(prevEndDate);
                        }
                    }

                    $('#incrementsTable tbody').append(`
                    <tr>
                        <td><input type="date" name="increments[${incrementIndex}][increment_start_date]" class="form-control inc-start-date" value="${newStartDate}" required></td>
                        <td><input type="number" name="increments[${incrementIndex}][years]" class="form-control inc-years" min="1" value="1" required></td>
                        <td><input type="date" name="increments[${incrementIndex}][increment_end_date]" class="form-control inc-end-date" required></td>
                        <td><input type="number" step="0.01" name="increments[${incrementIndex}][increment_amount]" class="form-control inc-amount" required></td>
                        <td><input type="number" step="0.01" name="increments[${incrementIndex}][increment_percentage]" class="form-control inc-percent"></td>
                        <td><input type="text" name="increments[${incrementIndex}][method_description]" class="form-control"></td>
                        <td><button type="button" class="btn btn-alt-danger btn-sm remove-increment">Remove</button></td>
                    </tr>
                `);

                    const newRow = $('#incrementsTable tbody tr').last();
                    const startDateInput = newRow.find('.inc-start-date');
                    const yearsInput = newRow.find('.inc-years');
                    if (startDateInput.val() && yearsInput.val()) {
                        const endDate = calculateEndDate(startDateInput.val(), yearsInput.val());
                        newRow.find('.inc-end-date').val(endDate);
                        updateSubsequentIncrements(newRow);
                    }

                    incrementIndex++;
                });

                $(document).on('click', '.remove-increment', function() {
                    const removedRow = $(this).closest('tr');
                    const prevRow = removedRow.prev('tr');
                    removedRow.remove();

                    if (prevRow.length) {
                        updateSubsequentIncrements(prevRow);
                    } else {
                        const firstRemainingRow = $('#incrementsTable tbody tr').first();
                        if (firstRemainingRow.length) {
                            // If the first row is removed, the "new first" row should have its dates reset
                            firstRemainingRow.find('.inc-start-date').val('');
                            firstRemainingRow.find('.inc-end-date').val('');
                            firstRemainingRow.find('.inc-years').val(1).trigger(
                                'change'); // Default years to 1 and trigger calculation
                        }
                    }
                });

                $(document).on('change', '.inc-start-date, .inc-years', function() {
                    const currentRow = $(this).closest('tr');
                    const startDateStr = currentRow.find('.inc-start-date').val();
                    const years = currentRow.find('.inc-years').val();
                    const endDateInput = currentRow.find('.inc-end-date');

                    const newEndDate = calculateEndDate(startDateStr, years);
                    endDateInput.val(newEndDate);

                    updateSubsequentIncrements(currentRow);
                });

                $(document).on('change', '.inc-end-date', function() {
                    const currentRow = $(this).closest('tr');
                    const startDateStr = currentRow.find('.inc-start-date').val();
                    const endDateStr = currentRow.find('.inc-end-date').val();
                    const years = calculateYearsFromDates(startDateStr, endDateStr);

                    if (years) {
                        currentRow.find('.inc-years').val(years);
                    }

                    updateSubsequentIncrements(currentRow);
                });

                // Initial calculation/chaining for existing increment rows on page load
                // This ensures dates are correct even if loaded from DB
                $('#incrementsTable tbody tr').each(function() {
                    const currentRow = $(this);
                    const startDateInput = currentRow.find('.inc-start-date');
                    const yearsInput = currentRow.find('.inc-years');
                    const endDateInput = currentRow.find('.inc-end-date');

                    let yearsVal = yearsInput.val();
                    // If 'years' is missing but start and end dates exist, derive it
                    if (!yearsVal && startDateInput.val() && endDateInput.val()) {
                        const derivedYears = calculateYearsFromDates(startDateInput.val(), endDateInput.val());
                        if (derivedYears !== '') {
                            yearsInput.val(derivedYears);
                            yearsVal = derivedYears; // Update yearsVal for subsequent logic
                        }
                    }

                    // If years is still empty (e.g., no dates, or derived years were <= 0), default to 1
                    if (!yearsVal) {
                        yearsInput.val(1);
                        yearsVal = 1;
                    }

                    // On edit, keep the DB/old end date as the default. Only calculate it if it is missing.
                    if (!endDateInput.val() && startDateInput.val() && yearsVal) {
                        const newEndDate = calculateEndDate(startDateInput.val(), yearsVal);
                        endDateInput.val(newEndDate);
                    }
                });


                // --- Security Deposits Logic ---
                // Initialize index for new rows, accounting for existing and old data
                let depositIndex = {{ max($depositRows->count(), count(old('deposits', []))) }};

                $('#addDeposit').click(function() {
                    const lastRow = $('#depositsTable tbody tr').last();
                    let newStartDate = '';

                    if (lastRow.length) {
                        const prevEndDate = lastRow.find('.dep-end-date').val();
                        if (prevEndDate) {
                            newStartDate = calculateNextStartDate(prevEndDate);
                        }
                    }

                    $('#depositsTable tbody').append(`
                    <tr>
                        <td><input type="number" step="0.01" name="deposits[${depositIndex}][absorb_amount]" class="form-control abs-amount"></td>
                        <td><input type="number" name="deposits[${depositIndex}][month_interval]" class="form-control dep-months" min="1" value="1" required></td>
                        <td><input type="number" step="0.01" name="deposits[${depositIndex}][adjust_per_month]" class="form-control dep-per-month" readonly></td>
                        <td><input type="date" name="deposits[${depositIndex}][absorb_start_date]" class="form-control dep-start-date" value="${newStartDate}"></td>
                        <td><input type="date" name="deposits[${depositIndex}][absorb_end_date]" class="form-control dep-end-date" required></td>
                        <td><input type="text" name="deposits[${depositIndex}][method_description]" class="form-control"></td>
                        <td><button type="button" class="btn btn-alt-danger btn-sm remove-deposit">Remove</button></td>
                    </tr>
                `);

                    const newRow = $('#depositsTable tbody tr').last();
                    const startDateInput = newRow.find('.dep-start-date');
                    const monthsInput = newRow.find('.dep-months');
                    if (startDateInput.val() && monthsInput.val()) {
                        const endDate = calculateMonthEndDate(startDateInput.val(), monthsInput.val());
                        newRow.find('.dep-end-date').val(endDate);
                        updateDepositPerMonth(newRow);
                        updateSubsequentDeposits(newRow);
                    }

                    depositIndex++;
                });

                $(document).on('click', '.remove-deposit', function() {
                    const removedRow = $(this).closest('tr');
                    const prevRow = removedRow.prev('tr');
                    removedRow.remove();

                    if (prevRow.length) {
                        updateSubsequentDeposits(prevRow);
                    } else {
                        const firstRemainingRow = $('#depositsTable tbody tr').first();
                        if (firstRemainingRow.length) {
                            firstRemainingRow.find('.dep-start-date').val('');
                            firstRemainingRow.find('.dep-end-date').val('');
                            firstRemainingRow.find('.dep-months').val(1).trigger(
                                'change'); // Default month interval to 1 and trigger calculation
                        }
                    }
                });

                $(document).on('change input', '.dep-start-date, .dep-months, .abs-amount', function() {
                    const currentRow = $(this).closest('tr');
                    const startDateStr = currentRow.find('.dep-start-date').val();
                    const months = currentRow.find('.dep-months').val();
                    const endDateInput = currentRow.find('.dep-end-date');

                    const newEndDate = calculateMonthEndDate(startDateStr, months);
                    endDateInput.val(newEndDate);
                    updateDepositPerMonth(currentRow);

                    updateSubsequentDeposits(currentRow);
                });

                $(document).on('change', '.dep-end-date', function() {
                    const currentRow = $(this).closest('tr');
                    const startDateStr = currentRow.find('.dep-start-date').val();
                    const endDateStr = currentRow.find('.dep-end-date').val();
                    const months = calculateMonthsFromDates(startDateStr, endDateStr);

                    if (months) {
                        currentRow.find('.dep-months').val(months);
                    }

                    updateDepositPerMonth(currentRow);
                    updateSubsequentDeposits(currentRow);
                });

                // Initial calculation/chaining for existing deposit rows on page load
                // This ensures dates are correct even if loaded from DB
                $('#depositsTable tbody tr').each(function() {
                    const currentRow = $(this);
                    const startDateInput = currentRow.find('.dep-start-date');
                    const monthsInput = currentRow.find('.dep-months');
                    const endDateInput = currentRow.find('.dep-end-date');

                    let monthsVal = monthsInput.val();
                    if (!monthsVal && startDateInput.val() && endDateInput.val()) {
                        const derivedMonths = calculateMonthsFromDates(startDateInput.val(), endDateInput.val());
                        if (derivedMonths !== '') {
                            monthsInput.val(derivedMonths);
                            monthsVal = derivedMonths;
                        }
                    }

                    if (!monthsVal) {
                        monthsInput.val(1);
                        monthsVal = 1;
                    }

                    updateDepositPerMonth(currentRow);

                    // On edit, keep the DB/old adjust end as the default. Only calculate it if it is missing.
                    if (!endDateInput.val() && startDateInput.val() && monthsVal) {
                        const newEndDate = calculateMonthEndDate(startDateInput.val(), monthsVal);
                        endDateInput.val(newEndDate);
                    }
                });


                // --- Form Submission Validation for Deposits ---
                $('form').on('submit', function(e) {
                    const absorbable = parseFloat($('[name="security_deposit_absorbable"]').val()) || 0;
                    const nonAbsorbable = parseFloat($('[name="security_deposit_non_absorbable"]').val()) || 0;
                    const depositRows = $('#depositsTable tbody tr').filter(function() {
                        return $(this).find('input').filter(function() {
                            // Consider a row "entered" if any of its main fields are filled.
                            return $(this).hasClass('abs-amount') && $(this).val() !== '' ||
                                $(this).hasClass('dep-start-date') && $(this).val() !== '' ||
                                ($(this).hasClass('dep-months') && $(this).val() !== '1' && $(
                                        this).val() !==
                                    '');
                        }).length > 0;
                    }).length;

                    if ((absorbable > 0 || nonAbsorbable > 0) && depositRows === 0) {
                        e.preventDefault();
                        One.helpers('jq-notify', {
                            type: 'danger',
                            icon: 'fa fa-times me-1',
                            message: 'Please add at least one deposit schedule row when Adjustable or Non-Adjustable amount is entered.'
                        });
                    }
                });


                // --- Base Rent Dependent Calculations ---
                function getBaseRent() {
                    return parseFloat($('#base_rent').val()) || 0;
                }

                function getIncrementBaseForRow(row) {
                    let runningRent = getBaseRent();

                    row.prevAll('tr').each(function() {
                        runningRent += parseFloat($(this).find('.inc-amount').val()) || 0;
                    });

                    return runningRent;
                }

                function refreshIncrementPercentages() {
                    let runningRent = getBaseRent();

                    $('#incrementsTable tbody tr').each(function() {
                        const row = $(this);
                        const amount = parseFloat(row.find('.inc-amount').val()) || 0;

                        if (runningRent > 0 && amount > 0) {
                            row.find('.inc-percent').val(((amount / runningRent) * 100).toFixed(2));
                        }

                        runningRent += amount;
                    });
                }

                function refreshIncrementAmountsFromPercentages() {
                    let runningRent = getBaseRent();

                    $('#incrementsTable tbody tr').each(function() {
                        const row = $(this);
                        const percent = parseFloat(row.find('.inc-percent').val()) || 0;

                        if (runningRent > 0 && percent > 0) {
                            row.find('.inc-amount').val(((percent / 100) * runningRent).toFixed(2));
                        }

                        runningRent += parseFloat(row.find('.inc-amount').val()) || 0;
                    });
                }

                $(document).on('input', '.inc-amount', function() {
                    let base = getIncrementBaseForRow($(this).closest('tr'));
                    let amt = parseFloat($(this).val()) || 0;
                    if (base > 0) $(this).closest('tr').find('.inc-percent').val(((amt / base) * 100).toFixed(2));
                    refreshIncrementPercentages();
                });

                $(document).on('input', '.inc-percent', function() {
                    let base = getIncrementBaseForRow($(this).closest('tr'));
                    let percent = parseFloat($(this).val()) || 0;
                    if (base > 0) $(this).closest('tr').find('.inc-amount').val(((percent / 100) * base).toFixed(2));
                    refreshIncrementAmountsFromPercentages();
                });

                $('#base_rent').on('input', function() {
                    refreshIncrementAmountsFromPercentages();
                    $('#depositsTable tbody tr').each(function() {
                        updateDepositPerMonth($(this));
                    });
                });

                // --- Dynamic Utilities Logic ---
                const utilitySelector = $('#utility_type_selector');
                const utilitiesTableBody = $('#utilitiesTable tbody');

                // Add Row
                $('#addUtilityRowBtn').click(function() {
                    const selectedOption = utilitySelector.find('option:selected');
                    const id = selectedOption.val();
                    const name = selectedOption.data('name');

                    if (!id) {
                        alert('Please select a utility type.');
                        return;
                    }

                    const row = `
                        <tr data-id="${id}">
                            <td class="align-middle fw-semibold">
                                ${name}
                                <input type="hidden" name="utilities[${id}][id]" value="${id}">
                            </td>
                            <td>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">৳</span>
                                    <input type="number" step="0.01" class="form-control form-control-sm" 
                                           name="utilities[${id}][amount]" placeholder="0.00" required>
                                </div>
                            </td>
                            <td class="align-middle">
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input" type="checkbox" 
                                           name="utilities[${id}][disburse_with_rent]" value="1" checked>
                                    <label class="form-check-label fs-xs">Disburse with Rent</label>
                                </div>
                            </td>
                            <td class="text-center align-middle">
                                <button type="button" class="btn btn-sm btn-alt-danger remove-utility-row">
                                    <i class="fa fa-times"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                    utilitiesTableBody.append(row);

                    selectedOption.prop('disabled', true);
                    utilitySelector.val('');
                });

                // Remove Row
                utilitiesTableBody.on('click', '.remove-utility-row', function() {
                    const row = $(this).closest('tr');
                    const id = row.data('id');

                    utilitySelector.find(`option[value="${id}"]`).prop('disabled', false);
                    row.remove();
                });

                refreshIncrementPercentages();
            });
        </script>
    @endsection
@endsection
