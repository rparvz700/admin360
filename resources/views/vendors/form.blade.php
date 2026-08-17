@php
    $vendor = $vendor ?? new \App\Models\Vendor();
    $assignedCategoryIds = old('categories', $assignedCategoryIds ?? ($vendor->exists && $vendor->relationLoaded('categories') ? $vendor->categories->pluck('id')->toArray() : []));
    $servicesOffered = old('services_offered', $vendor->services_offered ?? []) ?: [];
    $serviceOptions = [
        'oil_change'          => ['label' => 'Vehicle Oil Change',      'icon' => 'fa-oil-can'],
        'tyre_service'        => ['label' => 'Tyre & Alignment',        'icon' => 'fa-circle-notch'],
        'engine_repair'       => ['label' => 'Engine / Mechanical',     'icon' => 'fa-gears'],
        'body_work'           => ['label' => 'Body Work & Paint',       'icon' => 'fa-car-burst'],
        'property_maintenance'=> ['label' => 'Building Maintenance',    'icon' => 'fa-building-gear'],
        'electrical_work'     => ['label' => 'Electrical Services',     'icon' => 'fa-bolt'],
        'cleaning_security'   => ['label' => 'Cleaning & Security',     'icon' => 'fa-shield-halved'],
        'it_support'          => ['label' => 'IT & Office Hardware',     'icon' => 'fa-laptop-code'],
    ];
@endphp

@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <div class="fw-semibold mb-1"><i class="fa fa-exclamation-triangle me-1"></i> Please correct the following errors:</div>
        <ul class="mb-0 ps-3 fs-sm">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@isset($vendor)
    @if ($vendor && $vendor->vendor_code)
        <div class="alert alert-info d-flex align-items-center py-2 fs-sm mb-4" role="alert">
            <i class="fa fa-hashtag me-2"></i>
            <div>Vendor Code <span class="fw-bold font-monospace">{{ $vendor->vendor_code }}</span> &mdash; generated automatically.</div>
        </div>
    @endif
@endisset

<!-- Section 1: Basic & Category Information -->
<h4 class="fs-sm fw-semibold text-uppercase text-muted border-bottom pb-2 mb-3">
    <i class="fa fa-store text-primary me-1"></i> Basic &amp; Classification
</h4>
<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label" for="name">Vendor Name <span class="text-danger">*</span></label>
        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name"
               value="{{ old('name', $vendor->name ?? '') }}" placeholder="e.g. Rahman Auto Workshop / Square Properties" required>
        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label" for="categories">Vendor Categories <span class="text-muted fs-xs">(Select all applicable)</span></label>
        <select class="form-select select2 @error('categories') is-invalid @enderror" id="categories" name="categories[]" multiple="multiple" data-placeholder="Select vendor categories..." style="width: 100%;">
            @foreach ($categories as $cat)
                <option value="{{ $cat->id }}" {{ in_array($cat->id, (array) $assignedCategoryIds) ? 'selected' : '' }}>
                    {{ $cat->name }} ({{ $cat->getModuleScopeLabel() }})
                </option>
            @endforeach
        </select>
        <div class="form-text fs-xs">Categorizing vendors enables automatic filtering in Rent Agreements, Vehicle Maintenance, and Utilities.</div>
        @error('categories') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
    </div>
</div>

<!-- Section 2: Contact Details -->
<h4 class="fs-sm fw-semibold text-uppercase text-muted border-bottom pb-2 mb-3 mt-3">
    <i class="fa fa-address-book text-info me-1"></i> Contact Details
</h4>
<div class="row">
    <div class="col-md-4 mb-3">
        <label class="form-label" for="contact_person">Contact Person</label>
        <input type="text" class="form-control @error('contact_person') is-invalid @enderror" id="contact_person" name="contact_person"
               value="{{ old('contact_person', $vendor->contact_person ?? '') }}" placeholder="e.g. Md. Karim Hossain">
        @error('contact_person') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-label" for="phone">Phone <span class="text-danger">*</span></label>
        <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone"
               value="{{ old('phone', $vendor->phone ?? '') }}" placeholder="e.g. 01712-345678" required>
        @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-label" for="email">Email</label>
        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email"
               value="{{ old('email', $vendor->email ?? '') }}" placeholder="e.g. accounts@vendor.com">
        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-12 mb-3">
        <label class="form-label" for="address">Address / Office Location</label>
        <textarea class="form-control @error('address') is-invalid @enderror" id="address" name="address" rows="2"
                  placeholder="Street, Area, City">{{ old('address', $vendor->address ?? '') }}</textarea>
        @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<!-- Section 3: Banking & Financial Details -->
<h4 class="fs-sm fw-semibold text-uppercase text-muted border-bottom pb-2 mb-3 mt-3">
    <i class="fa fa-building-columns text-success me-1"></i> Banking &amp; Financial Details
</h4>
<div class="row">
    <div class="col-md-3 mb-3">
        <label class="form-label" for="bank_name">Bank Name</label>
        <input type="text" class="form-control @error('bank_name') is-invalid @enderror" id="bank_name" name="bank_name"
               value="{{ old('bank_name', $vendor->bank_name ?? '') }}" placeholder="e.g. Dutch-Bangla Bank">
        @error('bank_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-3 mb-3">
        <label class="form-label" for="bank_account_no">Account Number</label>
        <input type="text" class="form-control @error('bank_account_no') is-invalid @enderror" id="bank_account_no" name="bank_account_no"
               value="{{ old('bank_account_no', $vendor->bank_account_no ?? '') }}" placeholder="e.g. 120-110-XXXXX">
        @error('bank_account_no') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-3 mb-3">
        <label class="form-label" for="routing_number">Routing Number</label>
        <input type="text" class="form-control @error('routing_number') is-invalid @enderror" id="routing_number" name="routing_number"
               value="{{ old('routing_number', $vendor->routing_number ?? '') }}" placeholder="e.g. 090271234">
        @error('routing_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-3 mb-3">
        <label class="form-label" for="tin_vat_no">TIN / VAT Reg. No.</label>
        <input type="text" class="form-control @error('tin_vat_no') is-invalid @enderror" id="tin_vat_no" name="tin_vat_no"
               value="{{ old('tin_vat_no', $vendor->tin_vat_no ?? '') }}" placeholder="e.g. 1234567890">
        @error('tin_vat_no') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<!-- Section 4: Services Offered, Rating & Status -->
<h4 class="fs-sm fw-semibold text-uppercase text-muted border-bottom pb-2 mb-3 mt-3">
    <i class="fa fa-screwdriver-wrench text-warning me-1"></i> Services, Rating &amp; Status
</h4>
<div class="row">
    <div class="col-md-8 mb-3">
        <label class="form-label">Services Offered</label>
        <div class="row g-2">
            @foreach ($serviceOptions as $value => $option)
                <div class="col-sm-6">
                    <div class="form-check form-check-inline w-100 bg-body-light border rounded px-3 py-2 m-0">
                        <input class="form-check-input" type="checkbox" name="services_offered[]"
                               value="{{ $value }}" id="service_{{ $value }}"
                               {{ in_array($value, (array) $servicesOffered) ? 'checked' : '' }}>
                        <label class="form-check-label w-100" for="service_{{ $value }}">
                            <i class="fa {{ $option['icon'] }} text-muted me-1"></i> {{ $option['label'] }}
                        </label>
                    </div>
                </div>
            @endforeach
        </div>
        @error('services_offered') <div class="text-danger fs-sm mt-1">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <div class="mb-3">
            <label class="form-label" for="rating">Rating (0&ndash;5)</label>
            <div class="input-group">
                <span class="input-group-text"><i class="fa fa-star text-warning"></i></span>
                <input type="number" step="0.1" min="0" max="5"
                       class="form-control @error('rating') is-invalid @enderror" id="rating" name="rating"
                       value="{{ old('rating', $vendor->rating ?? '') }}" placeholder="e.g. 4.5">
                @error('rating') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="form-text fs-xs">Leave blank if unrated.</div>
        </div>

        <div class="mb-3">
            <label class="form-label">Status</label>
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                       {{ old('is_active', $vendor->is_active ?? 1) ? 'checked' : '' }}>
                <label class="form-check-label" for="is_active">Active Vendor</label>
            </div>
            <div class="form-text fs-xs">Inactive vendors remain in historical reports but are hidden from new form dropdowns.</div>
        </div>
    </div>
</div>
