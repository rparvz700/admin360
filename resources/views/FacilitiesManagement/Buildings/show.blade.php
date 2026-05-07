@extends('Partials.app', ['activeMenu' => 'buildings'])

@section('title', 'Building Details')

@section('page_title')
    Building Details
@endsection

@section('content')
    @php
        $hasCoordinates = filled($building->lat) && filled($building->long);
        $mapUrl = $hasCoordinates
            ? 'https://www.google.com/maps/search/?api=1&query=' . urlencode($building->lat . ',' . $building->long)
            : null;

        $locationParts = collect([$building->area, $building->upazila, $building->district, $building->division])
            ->filter()
            ->values();
    @endphp

    <div class="content">
        <div class="building-page-header">
            <div>
                <div class="building-eyebrow">Facilities Management</div>
                <h2>{{ $building->site_name ?: 'Building Details' }}</h2>
                <p>{{ $building->code }}{{ $building->district ? ' - ' . $building->district : '' }}</p>
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
                    <div class="text-muted fs-sm">
                        Last updated {{ optional($building->updated_at)->format('Y-m-d H:i') ?? 'N/A' }}
                    </div>
                </div>
                <span class="badge bg-primary">{{ $building->code ?? 'N/A' }}</span>
            </div>

            <div class="block-content fs-sm data-content">
                <div class="building-show-summary">
                    <div class="building-show-icon">
                        <i class="fa fa-building"></i>
                    </div>
                    <div class="building-show-title">
                        <span>Site</span>
                        <strong>{{ $building->site_name ?? 'N/A' }}</strong>
                    </div>
                    <div class="building-show-meta">
                        <span>Location</span>
                        <strong>{{ $locationParts->isNotEmpty() ? $locationParts->join(', ') : 'N/A' }}</strong>
                    </div>
                    <div class="building-show-meta">
                        <span>Country</span>
                        <strong>{{ $building->country ?? 'N/A' }}</strong>
                    </div>
                </div>

                <div class="building-show-layout">
                    <section class="building-show-section">
                        <div class="building-section-heading">
                            <span class="building-section-icon"><i class="fa fa-id-card"></i></span>
                            <div>
                                <h4>Identity</h4>
                                <p>Core building reference information.</p>
                            </div>
                        </div>

                        <div class="building-detail-list">
                            <div class="building-detail-row">
                                <span>Code</span>
                                <strong>{{ $building->code ?? 'N/A' }}</strong>
                            </div>
                            <div class="building-detail-row">
                                <span>Site Name</span>
                                <strong>{{ $building->site_name ?? 'N/A' }}</strong>
                            </div>
                            <div class="building-detail-row">
                                <span>Country</span>
                                <strong>{{ $building->country ?? 'N/A' }}</strong>
                            </div>
                        </div>
                    </section>

                    <section class="building-show-section">
                        <div class="building-section-heading">
                            <span class="building-section-icon"><i class="fa fa-map-marker-alt"></i></span>
                            <div>
                                <h4>Location</h4>
                                <p>Administrative area and physical address.</p>
                            </div>
                        </div>

                        <div class="building-detail-list">
                            <div class="building-detail-row">
                                <span>Division</span>
                                <strong>{{ $building->division ?? 'N/A' }}</strong>
                            </div>
                            <div class="building-detail-row">
                                <span>District</span>
                                <strong>{{ $building->district ?? 'N/A' }}</strong>
                            </div>
                            <div class="building-detail-row">
                                <span>Upazila</span>
                                <strong>{{ $building->upazila ?? 'N/A' }}</strong>
                            </div>
                            <div class="building-detail-row">
                                <span>Area</span>
                                <strong>{{ $building->area ?? 'N/A' }}</strong>
                            </div>
                            <div class="building-detail-row building-detail-row-wide">
                                <span>Address</span>
                                <strong>{{ $building->address ?? 'N/A' }}</strong>
                            </div>
                        </div>
                    </section>
                </div>

                <section class="building-show-section building-coordinates-section">
                    <div class="building-section-heading">
                        <span class="building-section-icon"><i class="fa fa-location-arrow"></i></span>
                        <div>
                            <h4>Coordinates</h4>
                            <p>Latitude and longitude used for map placement.</p>
                        </div>
                    </div>

                    <div class="building-coordinate-panel">
                        <div class="building-coordinate-values">
                            <div class="building-coordinate-value">
                                <span>Latitude</span>
                                <strong>{{ $building->lat ?? 'N/A' }}</strong>
                            </div>
                            <div class="building-coordinate-value">
                                <span>Longitude</span>
                                <strong>{{ $building->long ?? 'N/A' }}</strong>
                            </div>
                        </div>

                        @if ($hasCoordinates)
                            <a href="{{ $mapUrl }}" target="_blank" rel="noopener" class="btn btn-alt-primary">
                                <i class="fa fa-map-marked-alt me-1"></i> View on Map
                            </a>
                        @else
                            <span class="text-muted">Coordinates are not available.</span>
                        @endif
                    </div>
                </section>
            </div>
        </div>
    </div>
@endsection
