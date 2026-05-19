@php
    $componentTypes = $componentTypes ?? \App\Services\RentComponentCalculator::COMPONENTS;
    $sourceRows = collect(old('rent_components'));

    if ($sourceRows->isEmpty() && isset($base)) {
        $sourceRows = $base->components->keyBy('component_type')->map(function ($component) {
            return [
                'area_sft' => $component->area_sft,
                'rent_amount' => $component->rent_amount,
                'vat_amount' => $component->vat_amount,
                'tax_amount' => $component->tax_amount,
                'total_amount' => $component->total_amount,
            ];
        });

        if ($sourceRows->isEmpty()) {
            $floors = optional($base->agreement)->floors ?? collect();
            $floorArea = (float) $floors->sum('floor_area_sft');
            $sourceRows = collect([
                'floor_area' => [
                    'area_sft' => $floorArea,
                    'rent_amount' => (float) $base->base_rent,
                ],
                'car_parking' => ['area_sft' => (float) $floors->sum('car_parking'), 'rent_amount' => 0],
                'dg_space' => ['area_sft' => (float) $floors->sum('dg_space_sft'), 'rent_amount' => 0],
                'store_space' => ['area_sft' => (float) $floors->sum('store_space_sft'), 'rent_amount' => 0],
            ]);
        }
    }
@endphp

<section class="{{ $panelClass ?? 'mb-4 p-3 border rounded rent-panel' }}">
    <div class="d-flex align-items-center justify-content-between gap-3 mb-3">
        <div>
            <h5 class="mb-1">Rent Segregation</h5>
            <div class="text-muted fs-sm">
                VAT {{ number_format((float) optional($vatTax ?? null)->vat, 2) }}% and Tax {{ number_format((float) optional($vatTax ?? null)->tax, 2) }}%
                apply only when a component area is at least {{ number_format($taxableAreaSft ?? 150, 2) }} sft.
            </div>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-sm align-middle mb-3 rent-components-table">
            <thead>
                <tr>
                    <th>Space Type</th>
                    <th style="width: 16%;">Area (sft)</th>
                    <th style="width: 16%;">Rent</th>
                    <th style="width: 12%;">VAT/Tax</th>
                    <th style="width: 14%;">VAT</th>
                    <th style="width: 14%;">Tax</th>
                    <th style="width: 14%;">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($componentTypes as $type => $meta)
                    @php
                        $row = $sourceRows->get($type, []);
                    @endphp
                    <tr data-rent-component="{{ $type }}">
                        <td class="fw-semibold">{{ $meta['label'] }}</td>
                        <td>
                            <input type="number" step="0.01" min="0"
                                name="rent_components[{{ $type }}][area_sft]"
                                class="form-control form-control-sm rc-area"
                                value="{{ $row['area_sft'] ?? '' }}">
                        </td>
                        <td>
                            <input type="number" step="0.01" min="0"
                                name="rent_components[{{ $type }}][rent_amount]"
                                class="form-control form-control-sm rc-rent"
                                value="{{ $row['rent_amount'] ?? '' }}">
                        </td>
                        <td>
                            <span class="badge rc-tax-badge bg-secondary">No</span>
                        </td>
                        <td>
                            <input type="number" step="0.01"
                                class="form-control form-control-sm rc-vat"
                                value="{{ $row['vat_amount'] ?? '' }}" readonly>
                        </td>
                        <td>
                            <input type="number" step="0.01"
                                class="form-control form-control-sm rc-tax"
                                value="{{ $row['tax_amount'] ?? '' }}" readonly>
                        </td>
                        <td>
                            <input type="number" step="0.01"
                                class="form-control form-control-sm rc-total"
                                value="{{ $row['total_amount'] ?? '' }}" readonly>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="text-muted text-center py-3 d-none rent-components-empty">
            No available rentable space entered yet.
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label" for="base_rent">Total Base Rent <span class="text-danger">*</span></label>
            <input type="number" step="0.01" name="base_rent" id="base_rent"
                class="form-control{{ isset($invalidClass) ? $invalidClass('base_rent') : '' }}" required
                value="{{ old('base_rent', $base->base_rent ?? '') }}" readonly>
            @error('base_rent')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-4">
            <label class="form-label">VAT Total</label>
            <input type="number" step="0.01" id="rent_vat_total" class="form-control"
                value="{{ old('vat', $base->vat ?? '') }}" readonly>
        </div>
        <div class="col-md-4">
            <label class="form-label">Tax Total</label>
            <input type="number" step="0.01" id="rent_tax_total" class="form-control"
                value="{{ old('tax', $base->tax ?? '') }}" readonly>
        </div>
    </div>
</section>
