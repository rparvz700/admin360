@php
    $isEdit = ($mode ?? 'create') === 'edit';
@endphp

@if ($errors->any())
    <div class="alert alert-danger d-flex align-items-start gap-2" role="alert">
        <i class="fa fa-exclamation-circle mt-1"></i>
        <div>
            <div class="fw-semibold">Please review the building information.</div>
            <div class="small">{{ $errors->first() }}</div>
        </div>
    </div>
@endif

<div class="building-form-section">
    <div class="building-section-heading">
        <span class="building-section-icon"><i class="fa fa-building"></i></span>
        <div>
            <h4>Building Identity</h4>
            <p>Primary code and site naming used across facilities records.</p>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-5">
            <label class="form-label" for="code">Code <span class="text-danger">*</span></label>
            <input type="text" class="form-control form-control-lg @error('code') is-invalid @enderror" id="code"
                name="code" value="{{ old('code', $building->code ?? '') }}" required>
            @error('code')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-lg-7">
            <label class="form-label" for="site_name">Site Name</label>
            <input type="text" class="form-control form-control-lg @error('site_name') is-invalid @enderror"
                id="site_name" name="site_name" value="{{ old('site_name', $building->site_name ?? '') }}">
            @error('site_name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<div class="building-form-section">
    <div class="building-section-heading">
        <span class="building-section-icon"><i class="fa fa-map-marker-alt"></i></span>
        <div>
            <h4>Location</h4>
            <p>Administrative area, address, and geo coordinates.</p>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-6">
            <label class="form-label" for="country">Country</label>
            <input type="text" class="form-control @error('country') is-invalid @enderror" id="country"
                name="country" value="{{ old('country', $building->country ?? '') }}">
            @error('country')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-lg-6">
            <label class="form-label" for="division">Division</label>
            <input type="text" class="form-control @error('division') is-invalid @enderror" id="division"
                name="division" value="{{ old('division', $building->division ?? '') }}">
            @error('division')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-lg-6">
            <label class="form-label" for="district">District</label>
            <select class="form-select js-select2 @error('district') is-invalid @enderror" id="district" name="district"
                style="width: 100%;">
                <option value="">Select District</option>
                @foreach ($districts as $district)
                    <option value="{{ $district['district'] }}"
                        {{ old('district', $building->district ?? '') == $district['district'] ? 'selected' : '' }}>
                        {{ $district['district'] }}
                    </option>
                @endforeach
            </select>
            @error('district')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-lg-6">
            <label class="form-label" for="upazila">Upazila</label>
            <select class="form-select js-select2 @error('upazila') is-invalid @enderror" id="upazila" name="upazila"
                style="width: 100%;">
                <option value="">Select Upazila</option>
            </select>
            @error('upazila')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-lg-6">
            <label class="form-label" for="area">Area</label>
            <input type="text" class="form-control @error('area') is-invalid @enderror" id="area" name="area"
                value="{{ old('area', $building->area ?? '') }}">
            @error('area')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-lg-6">
            <label class="form-label" for="address">Address</label>
            <input type="text" class="form-control @error('address') is-invalid @enderror" id="address" name="address"
                value="{{ old('address', $building->address ?? '') }}">
            @error('address')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-lg-6">
            <label class="form-label" for="lat">Latitude</label>
            <input type="text" class="form-control @error('lat') is-invalid @enderror" id="lat" name="lat"
                value="{{ old('lat', $building->lat ?? '') }}" placeholder="23.8103">
            @error('lat')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-lg-6">
            <label class="form-label" for="long">Longitude</label>
            <input type="text" class="form-control @error('long') is-invalid @enderror" id="long" name="long"
                value="{{ old('long', $building->long ?? '') }}" placeholder="90.4125">
            @error('long')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>
