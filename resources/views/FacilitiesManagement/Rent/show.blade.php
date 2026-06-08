@extends('Partials.app', ['activeMenu' => 'rent'])

@section('title')
    {{ config('app.name') }} - Rent Details: {{ $base->agreement->agreement_ref_no ?? 'N/A' }}
@endsection

@section('page_title')
    Rent Details <small class="text-muted">({{ $base->agreement->agreement_ref_no ?? 'N/A' }})</small>
@endsection

@section('content')
    <div class="content">
        <div class="block block-rounded">
            <div class="block-header block-header-default">
                <h3 class="block-title">Rent for Agreement: <span
                        class="fw-bold">{{ $base->agreement->agreement_ref_no ?? 'N/A' }}</span></h3>
                <div class="block-options">
                    {{-- Assuming an 'edit' route for rent --}}
                    <a href="{{ route('rent.edit', $base) }}" class="btn btn-sm btn-primary me-2">
                        <i class="fa fa-pencil-alt me-1"></i> Edit Rent
                    </a>
                    <a href="{{ route('rent.index') }}" class="btn btn-sm btn-secondary">
                        <i class="fa fa-arrow-left me-1"></i> Back to List
                    </a>
                </div>
            </div>
            <div class="block-content">
                <!-- Tabs Navigation -->
                <ul class="nav nav-tabs nav-tabs-alt" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active" id="details-tab" data-bs-toggle="tab" data-bs-target="#details-pane"
                            type="button" role="tab" aria-controls="details-pane" aria-selected="true">
                            <i class="fa fa-info-circle me-1"></i> Details
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" id="history-tab" data-bs-toggle="tab" data-bs-target="#history-pane"
                            type="button" role="tab" aria-controls="history-pane" aria-selected="false"
                            data-rent-id="{{ $base->id }}">
                            <i class="fa fa-history me-1"></i> History
                        </button>
                    </li>
                </ul>

                <!-- Tabs Content -->
                <div class="tab-content pb-4">
                    <!-- Details Tab Pane -->
                    <div class="tab-pane fade show active" id="details-pane" role="tabpanel" aria-labelledby="details-tab"
                        tabindex="0">
                        <!-- Base Rent Section -->
                        <h4 class="fw-light mt-4 mb-3">Base Rent Information</h4>
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-striped table-bordered fs-sm">
                                    <tbody>
                                        <tr>
                                            <th>Agreement Ref No.</th>
                                            <td><a
                                                    href="{{ route('agreements.show', $base->agreement) }}">{{ $base->agreement->agreement_ref_no ?? 'N/A' }}</a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Base Rent</th>
                                            <td>{{ number_format($base->base_rent ?? 0, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <th>VAT</th>
                                            <td>{{ number_format($base->vat ?? 0, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <th>Tax</th>
                                            <td>{{ number_format($base->tax ?? 0, 2) }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-striped table-bordered fs-sm">
                                    <tbody>
                                        <tr>
                                            <th>Is At Source</th>
                                            <td>
                                                @if ($base->is_at_source)
                                                    <span class="badge bg-success">Yes</span>
                                                @else
                                                    <span class="badge bg-secondary">No</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Rent Type</th>
                                            <td>{{ $base->rent_type ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Start Date</th>
                                            <td>{{ $base->agreement->from_date ? \Carbon\Carbon::parse($base->agreement->from_date)->format('Y-m-d') : 'N/A' }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>End Date</th>
                                            <td>{{ $base->agreement->to_date ? \Carbon\Carbon::parse($base->agreement->to_date)->format('Y-m-d') : 'N/A' }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="col-12">
                                <table class="table table-striped table-bordered fs-sm">
                                    <tbody>
                                        <tr>
                                            <th>Remarks</th>
                                            <td>{{ $base->remarks ?? 'N/A' }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <h4 class="fw-light mt-4 mb-3">Rent Segregation</h4>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-vcenter fs-sm">
                                <thead class="table-light">
                                    <tr>
                                        <th>Space Type</th>
                                        <th>Area (sft)</th>
                                        <th>Rent</th>
                                        <th>VAT Applied</th>
                                        <th>VAT</th>
                                        <th>Tax</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($base->components as $component)
                                        <tr>
                                            <td>{{ \App\Services\RentComponentCalculator::COMPONENTS[$component->component_type]['label'] ?? $component->component_type }}</td>
                                            <td>{{ number_format($component->area_sft ?? 0, 2) }}</td>
                                            <td>{{ number_format($component->rent_amount ?? 0, 2) }}</td>
                                            <td>
                                                <span class="badge bg-{{ $component->vat_applicable ? 'success' : 'secondary' }}">
                                                    {{ $component->vat_applicable ? 'Yes' : 'No' }}
                                                </span>
                                            </td>
                                            <td>{{ number_format($component->vat_amount ?? 0, 2) }}</td>
                                            <td>{{ number_format($component->tax_amount ?? 0, 2) }}</td>
                                            <td>{{ number_format($component->total_amount ?? 0, 2) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-3">No rent segregation defined.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Rent Increments Section -->
                        <h4 class="fw-light mt-4 mb-3">Rent Increments</h4>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-vcenter fs-sm">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 15%;">Start Date</th>
                                        <th style="width: 15%;">End Date</th>
                                        <th style="width: 15%;">Amount</th>
                                        <th style="width: 15%;">Percentage</th>
                                        <th>Method Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($base->increments as $inc)
                                        <tr>
                                            <td>{{ $inc->increment_start_date ? \Carbon\Carbon::parse($inc->increment_start_date)->format('Y-m-d') : 'N/A' }}
                                            </td>
                                            <td>{{ $inc->increment_end_date ? \Carbon\Carbon::parse($inc->increment_end_date)->format('Y-m-d') : 'N/A' }}
                                            </td>
                                            <td>{{ number_format($inc->increment_amount ?? 0, 2) }}</td>
                                            <td>{{ number_format($inc->increment_percentage ?? 0, 2) }}%</td>
                                            <td>{{ $inc->method_description ?? 'N/A' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-3">No rent increments
                                                defined.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Security Deposits Section -->
                        <h4 class="fw-light mt-4 mb-3">Security Deposits</h4>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-vcenter fs-sm">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 10%;">Total</th>
                                        <th style="width: 10%;">Adjustable</th>
                                        <th style="width: 10%;">Non-Adjustable</th>
                                        <th style="width: 12%;">Adjust Start</th>
                                        <th style="width: 12%;">Adjust End</th>
                                        <th style="width: 10%;">Adjust Amount</th>
                                        <th style="width: 10%;">Month Interval</th>
                                        <th style="width: 10%;">Adjust / Month</th>
                                        <th>Method Desc</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($base->securityDeposits as $dep)
                                        @php
                                            $monthInterval = (int) ($dep->absorb_frequency ?? 0);
                                            $adjustPerMonth =
                                                $monthInterval > 0
                                                    ? ((float) ($dep->absorb_amount ?? 0)) / $monthInterval
                                                    : null;
                                        @endphp
                                        <tr>
                                            <td>{{ number_format($dep->security_deposit_total ?? 0, 2) }}</td>
                                            <td>{{ number_format($dep->security_deposit_absorbable ?? 0, 2) }}</td>
                                            <td>{{ number_format($dep->security_deposit_non_absorbable ?? 0, 2) }}</td>
                                            <td>{{ $dep->absorb_start_date ? \Carbon\Carbon::parse($dep->absorb_start_date)->format('Y-m-d') : 'N/A' }}
                                            </td>
                                            <td>{{ $dep->absorb_end_date ? \Carbon\Carbon::parse($dep->absorb_end_date)->format('Y-m-d') : 'N/A' }}
                                            </td>
                                            <td>{{ number_format($dep->absorb_amount ?? 0, 2) }}</td>
                                            <td>{{ $monthInterval > 0 ? $monthInterval . ' month(s)' : 'N/A' }}</td>
                                            <td>{{ $adjustPerMonth !== null ? number_format($adjustPerMonth, 2) : 'N/A' }}</td>
                                            <td>{{ $dep->method_description ?? 'N/A' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center text-muted py-3">No security deposits
                                                defined.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- History Tab Pane -->
                    <div class="tab-pane fade" id="history-pane" role="tabpanel" aria-labelledby="history-tab"
                        tabindex="0">
                        <h4 class="fw-light mt-4 mb-3">Rent History Log</h4>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-vcenter fs-sm">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 15%;">Date</th>
                                        <th style="width: 15%;">User</th>
                                        <th style="width: 20%;">Field</th>
                                        <th style="width: 25%;">Old Value</th>
                                        <th style="width: 25%;">New Value</th>
                                    </tr>
                                </thead>
                                <tbody id="history-items-body">
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">
                                            <div class="spinner-border text-primary spinner-border-sm me-2" role="status">
                                                <span class="visually-hidden">Loading...</span>
                                            </div> Loading history...
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('js/lib/jquery.min.js') }}"></script>
    <script>
        jQuery(function() {
            const rentId = {{ $base->id }};
            const historyTabButton = jQuery('#history-tab');
            const historyItemsBody = jQuery('#history-items-body');

            function loadRentHistory() {
                historyItemsBody.html(`
                <tr>
                    <td colspan="5" class="text-center text-muted py-4">
                        <div class="spinner-border text-primary spinner-border-sm me-2" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div> Loading history...
                    </td>
                </tr>
            `);

                jQuery.ajax({
                    url: `/rent/${rentId}/history`, // IMPORTANT: Ensure this route exists and is properly implemented in your backend
                    method: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        let html = '';
                        if (data && data.length > 0) {
                            data.forEach(log => {
                                const userName = log.user ? (log.user.name || log.user) :
                                    'System';
                                const logDate = log.date ? new Date(log.date).toLocaleString() :
                                    'N/A';

                                if (log.changes && log.changes.length > 0) {
                                    log.changes.forEach(change => {
                                        const formatValue = (value, field) => {
                                            if (value === null)
                                                return '<em class="text-muted">(null)</em>';
                                            if (value === '')
                                                return '<em class="text-muted">(empty)</em>';
                                            // Specific formatting for boolean field 'Is At Source' if it's logged as 0/1
                                            if (field === 'Is At Source') {
                                                return value === 1 || value ===
                                                    '1' ? 'Yes' : 'No';
                                            }
                                            // Format currency fields
                                            if (['Base Rent', 'Vat', 'Tax',
                                                    'Increment Amount',
                                                    'Security Deposit Total',
                                                    'Security Deposit Absorbable',
                                                    'Security Deposit Non Absorbable',
                                                    'Absorb Amount'
                                                ].includes(field)) {
                                                return parseFloat(value)
                                                    .toLocaleString(undefined, {
                                                        minimumFractionDigits: 2,
                                                        maximumFractionDigits: 2
                                                    });
                                            }
                                            // Format percentage fields
                                            if (['Increment Percentage',
                                                    'Absorb Amount Percentage'
                                                ].includes(field)) {
                                                return parseFloat(value)
                                                    .toLocaleString(undefined, {
                                                        minimumFractionDigits: 2,
                                                        maximumFractionDigits: 2
                                                    }) + '%';
                                            }
                                            // Try to format dates
                                            if (['Start Date', 'End Date',
                                                    'Increment Start Date',
                                                    'Increment End Date',
                                                    'Absorb Start Date',
                                                    'Absorb End Date',
                                                    'Agreement From Date',
                                                    'Agreement To Date'
                                                ].includes(field) && value.match(
                                                    /^\d{4}-\d{2}-\d{2}/)) {
                                                try {
                                                    return new Date(value)
                                                        .toLocaleDateString();
                                                } catch (e) {
                                                    // Fallback if date parsing fails
                                                }
                                            }
                                            return value;
                                        };

                                        html += `<tr>
                                        <td>${logDate}</td>
                                        <td>${userName}</td>
                                        <td><strong>${change.field || 'N/A'}</strong></td>
                                        <td class="text-danger">${formatValue(change.from, change.field)}</td>
                                        <td class="text-success">${formatValue(change.to, change.field)}</td>
                                    </tr>`;
                                    });
                                } else {
                                    html += `<tr>
                                    <td>${logDate}</td>
                                    <td>${userName}</td>
                                    <td colspan="3" class="text-center text-muted">No specific changes recorded for this entry.</td>
                                </tr>`;
                                }
                            });
                        } else {
                            html =
                                '<tr><td colspan="5" class="text-center text-muted py-4">No history found for this rent entry.</td></tr>';
                        }
                        historyItemsBody.html(html);
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        console.error("Error loading rent history:", textStatus, errorThrown, jqXHR);
                        historyItemsBody.html(`
                        <tr>
                            <td colspan="5" class="text-center text-danger py-4">
                                <i class="fa fa-exclamation-triangle me-1"></i> Failed to load history. Please try again.
                                <small class="d-block mt-1">${jqXHR.responseJSON ? jqXHR.responseJSON.message : errorThrown}</small>
                            </td>
                        </tr>
                    `);
                    }
                });
            }

            historyTabButton.on('show.bs.tab', function() {
                if (!historyTabButton.data('history-loaded')) {
                    loadRentHistory();
                    historyTabButton.data('history-loaded', true);
                }
            });
        });
    </script>
@endsection
