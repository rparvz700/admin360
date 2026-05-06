@extends('Partials.app', ['activeMenu' => 'buildings'])

@section('title', 'Building Details')

@section('page_title')
    Building Details
@endsection

@section('content')
    <div class="content">
        <div class="building-page-header">
            <div>
                <div class="building-eyebrow">Facilities Management</div>
                <h2>{{ $building->site_name ?: 'Building Details' }}</h2>
                <p>{{ $building->code }}{{ $building->district ? ' · ' . $building->district : '' }}</p>
            </div>
            <div class="building-header-actions">
                <a href="{{ route('buildings.edit', $building->id) }}" class="btn btn-primary">
                    <i class="fa fa-pencil-alt me-1"></i> Edit
                </a>
                <a href="{{ route('buildings.index') }}" class="btn btn-alt-secondary">
                    <i class="fa fa-arrow-left me-1"></i> Back
                </a>
            </div>
        </div>

        <div class="block block-rounded building-shell">
            <div class="block-header block-header-default building-block-header">
                <div>
                    <h3 class="block-title">Building Profile</h3>
                    <div class="text-muted fs-sm">Identity and location information.</div>
                </div>
            </div>
            <div class="block-content">
                <div class="building-detail-grid">
                    <div class="building-detail-item">
                        <span>Code</span>
                        <strong>{{ $building->code ?? 'N/A' }}</strong>
                    </div>
                    <div class="building-detail-item">
                        <span>Site Name</span>
                        <strong>{{ $building->site_name ?? 'N/A' }}</strong>
                    </div>
                    <div class="building-detail-item">
                        <span>Country</span>
                        <strong>{{ $building->country ?? 'N/A' }}</strong>
                    </div>
                    <div class="building-detail-item">
                        <span>Division</span>
                        <strong>{{ $building->division ?? 'N/A' }}</strong>
                    </div>
                    <div class="building-detail-item">
                        <span>District</span>
                        <strong>{{ $building->district ?? 'N/A' }}</strong>
                    </div>
                    <div class="building-detail-item">
                        <span>Upazila</span>
                        <strong>{{ $building->upazila ?? 'N/A' }}</strong>
                    </div>
                    <div class="building-detail-item">
                        <span>Area</span>
                        <strong>{{ $building->area ?? 'N/A' }}</strong>
                    </div>
                    <div class="building-detail-item">
                        <span>Address</span>
                        <strong>{{ $building->address ?? 'N/A' }}</strong>
                    </div>
                    <div class="building-detail-item">
                        <span>Latitude</span>
                        <strong>{{ $building->lat ?? 'N/A' }}</strong>
                    </div>
                    <div class="building-detail-item">
                        <span>Longitude</span>
                        <strong>{{ $building->long ?? 'N/A' }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
