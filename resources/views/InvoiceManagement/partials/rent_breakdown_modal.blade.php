<div class="mb-3 p-3 bg-body-light border rounded fs-sm">
    <div class="row">
        <div class="col-md-4">
            <strong>Agreement Ref:</strong> {{ $rent->agreement->agreement_ref_no ?? 'N/A' }}
        </div>
        <div class="col-md-4">
            <strong>Vendor:</strong> {{ $rent->agreement->vendor->name ?? 'N/A' }}
        </div>
        <div class="col-md-4">
            <strong>Rent Type:</strong> {{ ucfirst($rent->rent_type ?? 'N/A') }}
        </div>
    </div>
    <div class="row mt-2">
        <div class="col-md-4">
            <strong>Site Code:</strong> {{ $siteCode ?? 'N/A' }}
        </div>
        <div class="col-md-4">
            <strong>Building Name:</strong> {{ $buildingName ?? 'N/A' }}
        </div>
        <div class="col-md-4">
            <strong>Floor Info:</strong> {{ $floorInfo ?? 'N/A' }}
        </div>
    </div>
</div>

@include('FacilitiesManagement.Rent.partials.breakdown_tables', ['rent' => $rent])
