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
                                <select id="agreement_id" name="agreement_id" class="form-select js-select2"
                                    data-placeholder="Select agreement" required>
                                    <option value=""></option>
                                    @foreach ($agreements as $agreement)
                                        <option value="{{ $agreement->id }}"
                                            {{ old('agreement_id') == $agreement->id ? 'selected' : '' }}>
                                            {{ $agreement->agreement_ref_no }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 col-sm-12 mb-4">
                                <label class="form-label" for="base_rent">Base Rent</label>
                                <input type="number" step="0.01" class="form-control" id="base_rent" name="base_rent"
                                    value="{{ old('base_rent') }}" required>
                            </div>
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
                                    <option value="Monthly" {{ old('rent_type') == 'Monthly' ? 'selected' : '' }}>Monthly
                                    </option>
                                    <option value="Quarterly" {{ old('rent_type') == 'Quarterly' ? 'selected' : '' }}>
                                        Quarterly</option>
                                    <option value="Half Yearly" {{ old('rent_type') == 'Half Yearly' ? 'selected' : '' }}>
                                        Half Yearly</option>
                                    <option value="Yearly" {{ old('rent_type') == 'Yearly' ? 'selected' : '' }}>Yearly
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-12 mb-4">
                                <label class="form-label" for="remarks">Remarks</label>
                                <textarea class="form-control" id="remarks" name="remarks">{{ old('remarks') }}</textarea>
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
                                    <!-- Dynamic rows will go here -->
                                </tbody>
                            </table>
                        </div>
                        <div class="row g-2 align-items-center">
                            <div class="col-md-4 col-sm-6">
                                <select id="utility_type_selector" class="form-select">
                                    <option value="">Choose Utility...</option>
                                    @foreach ($utilityTypes as $type)
                                        <option value="{{ $type->id }}" data-name="{{ $type->name }}">{{ $type->name }}</option>
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
                                    <th>Years</th>
                                    <th>End Date</th>
                                    <th>Amount</th>
                                    <th>Percentage</th>
                                    <th>Method Description</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if (old('increments'))
                                    @foreach (old('increments') as $index => $increment)
                                        <tr>
                                            <td><input type="date"
                                                    name="increments[{{ $index }}][increment_start_date]"
                                                    class="form-control inc-start-date"
                                                    value="{{ $increment['increment_start_date'] ?? '' }}" required></td>
                                            <td><input type="number" name="increments[{{ $index }}][years]"
                                                    class="form-control inc-years" min="1"
                                                    value="{{ $increment['years'] ?? 1 }}" required></td>
                                            <td><input type="date"
                                                    name="increments[{{ $index }}][increment_end_date]"
                                                    class="form-control inc-end-date"
                                                    value="{{ $increment['increment_end_date'] ?? '' }}" required></td>
                                            <td><input type="number" step="0.01"
                                                    name="increments[{{ $index }}][increment_amount]"
                                                    class="form-control inc-amount"
                                                    value="{{ $increment['increment_amount'] ?? '' }}" required></td>
                                            <td><input type="number" step="0.01"
                                                    name="increments[{{ $index }}][increment_percentage]"
                                                    class="form-control inc-percent"
                                                    value="{{ $increment['increment_percentage'] ?? '' }}"></td>
                                            <td><input type="text"
                                                    name="increments[{{ $index }}][method_description]"
                                                    class="form-control"
                                                    value="{{ $increment['method_description'] ?? '' }}"></td>
                                            <td><button type="button"
                                                    class="btn btn-alt-danger btn-sm remove-increment">Remove</button></td>
                                        </tr>
                                    @endforeach
                                @endif
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
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Total</label>
                                <input type="number" step="0.01" name="security_deposit_total" class="form-control"
                                    value="{{ old('security_deposit_total') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Adjustable</label>
                                <input type="number" step="0.01" name="security_deposit_absorbable"
                                    class="form-control" value="{{ old('security_deposit_absorbable') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Non-Adjustable</label>
                                <input type="number" step="0.01" name="security_deposit_non_absorbable"
                                    class="form-control" value="{{ old('security_deposit_non_absorbable') }}">
                            </div>
                        </div>
                        <table class="table table-bordered" id="depositsTable">
                            <thead>
                                <tr>
                                    <th>Adjust Amount</th>
                                    <th>Adjust %</th>
                                    <th>Adjust Start</th>
                                    <th>Years</th> {{-- NEW FIELD HEADER for Deposits --}}
                                    <th>Adjust End</th>
                                    <th>Method Desc</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if (old('deposits'))
                                    @foreach (old('deposits') as $index => $deposit)
                                        <tr>
                                            <td><input type="number" step="0.01"
                                                    name="deposits[{{ $index }}][absorb_amount]"
                                                    class="form-control abs-amount"
                                                    value="{{ $deposit['absorb_amount'] ?? '' }}"></td>
                                            <td><input type="number" step="0.01"
                                                    name="deposits[{{ $index }}][absorb_amount_percentage]"
                                                    class="form-control abs-percent"
                                                    value="{{ $deposit['absorb_amount_percentage'] ?? '' }}"></td>
                                            <td><input type="date"
                                                    name="deposits[{{ $index }}][absorb_start_date]"
                                                    class="form-control dep-start-date"
                                                    value="{{ $deposit['absorb_start_date'] ?? '' }}"></td>
                                            <td><input type="number" name="deposits[{{ $index }}][years]"
                                                    class="form-control dep-years" min="1"
                                                    value="{{ $deposit['years'] ?? 1 }}" required></td>
                                            {{-- NEW INPUT for Deposits --}}
                                            <td><input type="date"
                                                    name="deposits[{{ $index }}][absorb_end_date]"
                                                    class="form-control dep-end-date"
                                                    value="{{ $deposit['absorb_end_date'] ?? '' }}" required></td>
                                            <td><input type="text"
                                                    name="deposits[{{ $index }}][method_description]"
                                                    class="form-control"
                                                    value="{{ $deposit['method_description'] ?? '' }}"></td>
                                            <td><button type="button"
                                                    class="btn btn-alt-danger btn-sm remove-deposit">Remove</button></td>
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
    <script src="{{ asset('js/plugins/bootstrap-notify/bootstrap-notify.min.js') }}"></script>

    <script>
        One.helpersOnLoad(["jq-select2", "jq-notify"]);

        // --- Date Calculation Functions (Renamed for generality) ---
        function calculateEndDate(startDateStr, years) {
            if (!startDateStr || !years || years <= 0) {
                return '';
            }
            const startDate = new Date(startDateStr);
            // Ensure date is valid and adjust for timezone issues if any by getting UTC components
            const startYear = startDate.getFullYear();
            const startMonth = startDate.getMonth();
            const startDay = startDate.getDate();

            if (isNaN(startDate.getTime())) {
                return ''; // Invalid date
            }

            const endDate = new Date(startYear + parseInt(years, 10), startMonth, startDay);
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
            const endDate = new Date(endDateStr);
            // Ensure date is valid and adjust for timezone issues if any by getting UTC components
            const endYear = endDate.getFullYear();
            const endMonth = endDate.getMonth();
            const endDay = endDate.getDate();

            if (isNaN(endDate.getTime())) {
                return ''; // Invalid date
            }
            const nextStartDate = new Date(endYear, endMonth, endDay + 1); // Add one day

            const year = nextStartDate.getFullYear();
            const month = String(nextStartDate.getMonth() + 1).padStart(2, '0');
            const day = String(nextStartDate.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
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
                const nextYearsInput = nextRow.find('.dep-years');
                const nextEndDateInput = nextRow.find('.dep-end-date');

                const newNextStartDate = calculateNextStartDate(currentEndDate);

                if (nextStartDateInput.val() !== newNextStartDate) {
                    nextStartDateInput.val(newNextStartDate);
                }

                const nextYears = nextYearsInput.val();
                const newNextEndDate = calculateEndDate(newNextStartDate, nextYears);

                if (nextEndDateInput.val() !== newNextEndDate) {
                    nextEndDateInput.val(newNextEndDate);
                }

                currentEndDate = newNextEndDate;
                currentRow = nextRow;
                if (!currentEndDate) {
                    break;
                }
            }
        }


        document.addEventListener('DOMContentLoaded', function() {
            if (window.jQuery) {
                // --- Rent Increment Logic ---
                let incrementIndex = {{ count(old('increments', [])) }}; // Initialize index based on old data

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

                    // Immediately try to calculate end date for the newly added row if start date and years are present
                    const newRow = $('#incrementsTable tbody tr').last();
                    const startDateInput = newRow.find('.inc-start-date');
                    const yearsInput = newRow.find('.inc-years');
                    if (startDateInput.val() && yearsInput.val()) {
                        const endDate = calculateEndDate(startDateInput.val(), yearsInput.val());
                        newRow.find('.inc-end-date').val(endDate);
                        updateSubsequentIncrements(newRow); // Trigger update from this new row
                    }

                    incrementIndex++;
                });

                $(document).on('click', '.remove-increment', function() {
                    const removedRow = $(this).closest('tr');
                    const prevRow = removedRow.prev('tr'); // Get the row *before* the one being removed
                    removedRow.remove();

                    // If there's a previous row, trigger update from it to fix the chain
                    if (prevRow.length) {
                        updateSubsequentIncrements(prevRow);
                    } else {
                        // If the first row was removed, and there are still increments,
                        // the new first row needs to be re-evaluated (its start date might now be editable/empty)
                        const firstRemainingRow = $('#incrementsTable tbody tr').first();
                        if (firstRemainingRow.length) {
                            firstRemainingRow.find('.inc-start-date').val(
                                ''); // Clear its start date for user input
                            firstRemainingRow.find('.inc-end-date').val(
                                ''); // Clear its end date as it depends on start date
                            firstRemainingRow.find('.inc-years').trigger(
                                'change'); // Trigger its own calculation
                        }
                    }
                });

                // Handle changes to start date or years for any increment row
                $(document).on('change', '.inc-start-date, .inc-years', function() {
                    const currentRow = $(this).closest('tr');
                    const startDateStr = currentRow.find('.inc-start-date').val();
                    const years = currentRow.find('.inc-years').val();
                    const endDateInput = currentRow.find('.inc-end-date');

                    const newEndDate = calculateEndDate(startDateStr, years);
                    endDateInput.val(newEndDate);

                    // Trigger cascading update for subsequent rows
                    updateSubsequentIncrements(currentRow);
                });

                // Initialize/recalculate dates for increments when the page loads (e.g., after validation error)
                $('#incrementsTable tbody tr').each(function() {
                    const currentRow = $(this);
                    const startDateInput = currentRow.find('.inc-start-date');
                    const yearsInput = currentRow.find('.inc-years');
                    const endDateInput = currentRow.find('.inc-end-date');

                    if (startDateInput.val() && yearsInput.val()) {
                        const newEndDate = calculateEndDate(startDateInput.val(), yearsInput.val());
                        if (endDateInput.val() !== newEndDate) {
                            endDateInput.val(newEndDate);
                        }
                    }
                });
                // Ensure the entire chain is correct on load
                const firstIncrementRow = $('#incrementsTable tbody tr').first();
                if (firstIncrementRow.length) {
                    updateSubsequentIncrements(firstIncrementRow);
                }


                // --- Security Deposits Logic ---
                let depositIndex = {{ count(old('deposits', [])) }};

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
                            <td><input type="number" step="0.01" name="deposits[${depositIndex}][absorb_amount_percentage]" class="form-control abs-percent"></td>
                            <td><input type="date" name="deposits[${depositIndex}][absorb_start_date]" class="form-control dep-start-date" value="${newStartDate}"></td>
                            <td><input type="number" name="deposits[${depositIndex}][years]" class="form-control dep-years" min="1" value="1" required></td>
                            <td><input type="date" name="deposits[${depositIndex}][absorb_end_date]" class="form-control dep-end-date" required></td>
                            <td><input type="text" name="deposits[${depositIndex}][method_description]" class="form-control"></td>
                            <td><button type="button" class="btn btn-alt-danger btn-sm remove-deposit">Remove</button></td>
                        </tr>
                    `);

                    const newRow = $('#depositsTable tbody tr').last();
                    const startDateInput = newRow.find('.dep-start-date');
                    const yearsInput = newRow.find('.dep-years');
                    if (startDateInput.val() && yearsInput.val()) {
                        const endDate = calculateEndDate(startDateInput.val(), yearsInput.val());
                        newRow.find('.dep-end-date').val(endDate);
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
                            firstRemainingRow.find('.dep-years').trigger('change');
                        }
                    }
                });

                // Handle changes to start date or years for any deposit row
                $(document).on('change', '.dep-start-date, .dep-years', function() {
                    const currentRow = $(this).closest('tr');
                    const startDateStr = currentRow.find('.dep-start-date').val();
                    const years = currentRow.find('.dep-years').val();
                    const endDateInput = currentRow.find('.dep-end-date');

                    const newEndDate = calculateEndDate(startDateStr, years);
                    endDateInput.val(newEndDate);

                    updateSubsequentDeposits(currentRow);
                });

                // Initialize/recalculate dates for deposits when the page loads (e.g., after validation error)
                $('#depositsTable tbody tr').each(function() {
                    const currentRow = $(this);
                    const startDateInput = currentRow.find('.dep-start-date');
                    const yearsInput = currentRow.find('.dep-years');
                    const endDateInput = currentRow.find('.dep-end-date');

                    if (startDateInput.val() && yearsInput.val()) {
                        const newEndDate = calculateEndDate(startDateInput.val(), yearsInput.val());
                        if (endDateInput.val() !== newEndDate) {
                            endDateInput.val(newEndDate);
                        }
                    }
                });
                // Ensure the entire chain is correct on load
                const firstDepositRow = $('#depositsTable tbody tr').first();
                if (firstDepositRow.length) {
                    updateSubsequentDeposits(firstDepositRow);
                }


                // --- Form Submission Validation for Deposits ---
                $('form').on('submit', function(e) {
                    const absorbable = parseFloat($('[name="security_deposit_absorbable"]').val()) || 0;
                    const nonAbsorbable = parseFloat($('[name="security_deposit_non_absorbable"]').val()) ||
                        0;
                    const depositRows = $('#depositsTable tbody tr').filter(function() {
                        return $(this).find('input').filter(function() {
                            // Consider a row "entered" if any of its main fields (amount, percentage, start date, years) are filled
                            return $(this).hasClass('abs-amount') && $(this).val() !== '' ||
                                $(this).hasClass('abs-percent') && $(this).val() !== '' ||
                                $(this).hasClass('dep-start-date') && $(this).val() !==
                                '' ||
                                $(this).hasClass('dep-years') && $(this).val() !== '1' && $(
                                    this).val() !==
                                ''; // Default is 1, so check for non-default or empty
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
            }
        });

        // --- Agreement ID Change Logic ---
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
        }).trigger('change'); // Trigger on load to set initial state


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

        // Increment: If Amount is typed, calculate Percentage from cumulative rent.
        $(document).on('input', '.inc-amount', function() {
            let baseRent = getIncrementBaseForRow($(this).closest('tr'));
            let amount = parseFloat($(this).val()) || 0;
            let row = $(this).closest('tr');

            if (baseRent > 0) {
                let percentage = (amount / baseRent) * 100;
                row.find('.inc-percent').val(percentage.toFixed(2));
            }

            refreshIncrementPercentages();
        });

        // Increment: If Percentage is typed, calculate Amount from cumulative rent.
        $(document).on('input', '.inc-percent', function() {
            let baseRent = getIncrementBaseForRow($(this).closest('tr'));
            let percentage = parseFloat($(this).val()) || 0;
            let row = $(this).closest('tr');

            if (baseRent > 0) {
                let amount = (percentage / 100) * baseRent;
                row.find('.inc-amount').val(amount.toFixed(2));
            }

            refreshIncrementAmountsFromPercentages();
        });

        // Deposit: If Amount is typed, calculate Percentage
        $(document).on('input', '.abs-amount', function() {
            let baseRent = getBaseRent();
            let amount = parseFloat($(this).val()) || 0;
            let row = $(this).closest('tr');

            if (baseRent > 0) {
                let percentage = (amount / baseRent) * 100;
                row.find('.abs-percent').val(percentage.toFixed(2));
            }
        });

        // Deposit: If Percentage is typed, calculate Amount
        $(document).on('input', '.abs-percent', function() {
            let baseRent = getBaseRent();
            let percentage = parseFloat($(this).val()) || 0;
            let row = $(this).closest('tr');

            if (baseRent > 0) {
                let amount = (percentage / 100) * baseRent;
                row.find('.abs-amount').val(amount.toFixed(2));
            }
        });

        // Re-calculate all percentage/amount fields if Base Rent changes
        $('#base_rent').on('input', function() {
            refreshIncrementAmountsFromPercentages();

            let baseRent = parseFloat($(this).val()) || 0;
            if (baseRent > 0) {
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
    </script>
@endsection
