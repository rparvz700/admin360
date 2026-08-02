{{-- Rent Complete Breakdown Partial (Rent Segregation, Utilities, Increments, Security Deposits) --}}
<div class="rent-breakdown-container mt-4">
    <!-- 1. Rent Segregation Breakdown -->
    <div class="block block-rounded block-bordered mb-4">
        <div class="block-header block-header-default bg-body-light py-2">
            <h5 class="block-title fs-sm fw-bold text-primary mb-0">
                <i class="fa fa-cubes me-1"></i> 1. Rent Segregation
            </h5>
        </div>
        <div class="block-content p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-vcenter fs-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Space / Component Type</th>
                            <th class="text-end">Area (sft)</th>
                            <th class="text-end">Rate / sft (৳)</th>
                            <th class="text-end">Rent Amount (৳)</th>
                            <th class="text-center">VAT Applicable</th>
                            <th class="text-end">VAT (৳)</th>
                            <th class="text-end">Tax / AIT (৳)</th>
                            <th class="text-end">Total Amount (৳)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(isset($rent->components) && $rent->components->count() > 0)
                            @foreach ($rent->components as $component)
                                <tr>
                                    <td class="fw-semibold">
                                        {{ \App\Services\RentComponentCalculator::COMPONENTS[$component->component_type]['label'] ?? ucfirst(str_replace('_', ' ', $component->component_type)) }}
                                    </td>
                                    <td class="text-end">{{ number_format($component->area_sft ?? 0, 2) }}</td>
                                    <td class="text-end">৳ {{ number_format($component->rate ?? 0, 2) }}</td>
                                    <td class="text-end">৳ {{ number_format($component->rent_amount ?? 0, 2) }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-{{ $component->vat_applicable ? 'success-light text-success' : 'secondary-light text-secondary' }}">
                                            {{ $component->vat_applicable ? 'Yes' : 'No' }}
                                        </span>
                                    </td>
                                    <td class="text-end text-muted">৳ {{ number_format($component->vat_amount ?? 0, 2) }}</td>
                                    <td class="text-end text-muted">৳ {{ number_format($component->tax_amount ?? 0, 2) }}</td>
                                    <td class="text-end fw-bold text-primary">৳ {{ number_format($component->total_amount ?? 0, 2) }}</td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td class="fw-semibold">Base Rent Main Component</td>
                                <td class="text-end">-</td>
                                <td class="text-end">-</td>
                                <td class="text-end">৳ {{ number_format($rent->base_rent ?? 0, 2) }}</td>
                                <td class="text-center">
                                    <span class="badge bg-{{ ($rent->vat ?? 0) > 0 ? 'success-light text-success' : 'secondary-light text-secondary' }}">
                                        {{ ($rent->vat ?? 0) > 0 ? 'Yes' : 'No' }}
                                    </span>
                                </td>
                                <td class="text-end text-muted">৳ {{ number_format($rent->vat ?? 0, 2) }}</td>
                                <td class="text-end text-muted">৳ {{ number_format($rent->tax ?? 0, 2) }}</td>
                                <td class="text-end fw-bold text-primary">৳ {{ number_format(($rent->base_rent ?? 0) + ($rent->vat ?? 0) + ($rent->tax ?? 0), 2) }}</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- 2. Utilities & Service Charges -->
    <div class="block block-rounded block-bordered mb-4">
        <div class="block-header block-header-default bg-body-light py-2">
            <h5 class="block-title fs-sm fw-bold text-primary mb-0">
                <i class="fa fa-bolt me-1"></i> 2. Utilities & Service Charges
            </h5>
        </div>
        <div class="block-content p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-vcenter fs-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Utility / Charge Name</th>
                            <th class="text-end">Monthly Charge (৳)</th>
                            <th class="text-center">Disburse with Rent</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $utilities = $rent->agreement->utilities ?? collect();
                            if ($utilities->isEmpty() && isset($rent->agreement_id)) {
                                $utilities = \App\Models\AgreementUtility::with('utilityType')->where('agreement_id', $rent->agreement_id)->get();
                            }
                        @endphp
                        @forelse ($utilities as $util)
                            <tr>
                                <td class="fw-semibold">
                                    <i class="fa fa-plug text-muted me-1"></i>
                                    {{ $util->utilityType->name ?? 'Utility #' . $util->utility_type_id }}
                                </td>
                                <td class="text-end fw-semibold">৳ {{ number_format($util->amount ?? 0, 2) }}</td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $util->disburse_with_rent ? 'info-light text-info' : 'secondary-light text-secondary' }}">
                                        {{ $util->disburse_with_rent ? 'Yes (Combined)' : 'No (Separate)' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted py-3">
                                    <i class="fa fa-info-circle me-1"></i> No utility or service charges configured for this agreement.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- 3. Rent Increments Breakdown -->
    <div class="block block-rounded block-bordered mb-4">
        <div class="block-header block-header-default bg-body-light py-2">
            <h5 class="block-title fs-sm fw-bold text-primary mb-0">
                <i class="fa fa-chart-line me-1"></i> 3. Rent Increments Schedule
            </h5>
        </div>
        <div class="block-content p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-vcenter fs-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 15%;">Start Date</th>
                            <th style="width: 15%;">End Date</th>
                            <th class="text-end" style="width: 15%;">Increment Rate (%)</th>
                            <th class="text-end" style="width: 15%;">Increased Amount (৳)</th>
                            <th class="text-end" style="width: 20%;">New Base Rent (৳)</th>
                            <th>Method Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rent->increments as $inc)
                            <tr>
                                <td>{{ $inc->increment_start_date ? \Carbon\Carbon::parse($inc->increment_start_date)->format('d M Y') : 'N/A' }}</td>
                                <td>{{ $inc->increment_end_date ? \Carbon\Carbon::parse($inc->increment_end_date)->format('d M Y') : 'N/A' }}</td>
                                <td class="text-end fw-semibold text-info">{{ number_format($inc->increment_percentage ?? 0, 2) }}%</td>
                                <td class="text-end">৳ {{ number_format($inc->increment_amount ?? 0, 2) }}</td>
                                <td class="text-end fw-bold text-success">
                                    ৳ {{ number_format($inc->incremented_amount ?? (($rent->base_rent ?? 0) + ($inc->increment_amount ?? 0)), 2) }}
                                </td>
                                <td>{{ $inc->method_description ?? 'Standard Escalation' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-3">
                                    <i class="fa fa-info-circle me-1"></i> No rent increment schedule defined.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- 4. Security Deposits Breakdown -->
    <div class="block block-rounded block-bordered mb-2">
        <div class="block-header block-header-default bg-body-light py-2">
            <h5 class="block-title fs-sm fw-bold text-primary mb-0">
                <i class="fa fa-shield-alt me-1"></i> 4. Security Deposits
            </h5>
        </div>
        <div class="block-content p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-vcenter fs-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-end">Total Deposit (৳)</th>
                            <th class="text-end">Absorbable (৳)</th>
                            <th class="text-end">Non-Absorbable (৳)</th>
                            <th class="text-center">Absorb Start</th>
                            <th class="text-center">Absorb End</th>
                            <th class="text-end">Monthly Absorb (৳)</th>
                            <th>Method Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rent->securityDeposits as $dep)
                            @php
                                $monthInterval = (int) ($dep->absorb_frequency ?? 0);
                                $adjustPerMonth = $monthInterval > 0
                                    ? ((float) ($dep->absorb_amount ?? 0)) / $monthInterval
                                    : null;
                            @endphp
                            <tr>
                                <td class="text-end fw-bold">৳ {{ number_format($dep->security_deposit_total ?? 0, 2) }}</td>
                                <td class="text-end text-success">৳ {{ number_format($dep->security_deposit_absorbable ?? 0, 2) }}</td>
                                <td class="text-end text-secondary">৳ {{ number_format($dep->security_deposit_non_absorbable ?? 0, 2) }}</td>
                                <td class="text-center">{{ $dep->absorb_start_date ? \Carbon\Carbon::parse($dep->absorb_start_date)->format('d M Y') : 'N/A' }}</td>
                                <td class="text-center">{{ $dep->absorb_end_date ? \Carbon\Carbon::parse($dep->absorb_end_date)->format('d M Y') : 'N/A' }}</td>
                                <td class="text-end fw-semibold text-info">
                                    {{ $adjustPerMonth !== null ? '৳ ' . number_format($adjustPerMonth, 2) . '/mo' : (isset($dep->absorb_amount) && $dep->absorb_amount > 0 ? '৳ ' . number_format($dep->absorb_amount, 2) : 'N/A') }}
                                </td>
                                <td>{{ $dep->method_description ?? 'Security Deposit' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-3">
                                    <i class="fa fa-info-circle me-1"></i> No security deposit records defined.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
