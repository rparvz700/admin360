@extends('Partials.app', ['activeMenu' => 'tickets'])
@section('title')
    Create Ticket - {{ config('app.name') }}
@endsection
@section('styles')
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        /* Modern Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1050;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(3px);
        }

        .modal-content-custom {
            background-color: white;
            margin: 3% auto;
            padding: 24px;
            width: 90%;
            max-width: 750px;
            border-radius: 12px;
            position: relative;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2), 0 10px 10px -5px rgba(0, 0, 0, 0.1);
        }

        #map {
            height: 320px;
            width: 100%;
            border-radius: 8px;
            margin-bottom: 15px;
            border: 1px solid #cbd5e1;
        }

        #locationSearchInput {
            margin-bottom: 8px;
        }

        #searchResults {
            max-height: 160px;
            overflow-y: auto;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            margin-bottom: 12px;
            display: none;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        #searchResults .list-group-item {
            cursor: pointer;
            padding: 9px 14px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.875rem;
        }

        #searchResults .list-group-item:last-child {
            border-bottom: none;
        }

        #searchResults .list-group-item:hover {
            background-color: #f8fafc;
            color: #0d6efd;
        }

        .trip-location-row {
            background: #ffffff;
            padding: 12px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            margin-bottom: 10px;
            transition: all 0.2s ease;
        }
        .trip-location-row:hover {
            border-color: #cbd5e1;
            box-shadow: 0 2px 4px rgba(0,0,0,0.04);
        }
    </style>
@endsection

@section('content')
    <!-- Hero -->
    <div class="bg-body-light">
        <div class="content content-full">
            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
                <div class="flex-grow-1">
                    <h1 class="h3 fw-bold mb-1">
                        Create New Ticket
                    </h1>
                    <h2 class="fs-base lh-base fw-medium text-muted mb-0">
                        Submit a vehicle support request, new asset requisition, or asset repair request
                    </h2>
                </div>
                <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-alt">
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="{{ route('tickets.index') }}">Tickets</a>
                        </li>
                        <li class="breadcrumb-item" aria-current="page">
                            Create
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
    <!-- END Hero -->

    <!-- Page Content -->
    <div class="content">
        <div class="block block-rounded">
            <div class="block-header block-header-default">
                <h3 class="block-title">
                    <i class="fa fa-ticket text-primary me-1"></i> Ticket Form
                </h3>
                <div class="block-options">
                    <a href="{{ route('tickets.index') }}" class="btn btn-sm btn-alt-secondary">
                        <i class="fa fa-arrow-left me-1"></i> Back to List
                    </a>
                </div>
            </div>

            <div class="block-content p-4">
                <form action="{{ route('tickets.store') }}" method="POST" enctype="multipart/form-data" id="ticketForm">
                    @csrf

                    <!-- Ticket Type Selection -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Ticket Type <span class="text-danger">*</span></label>
                            <select name="ticket_type" id="ticket_type"
                                class="form-select @error('ticket_type') is-invalid @enderror" required>
                                <option value="">Select Ticket Type</option>
                                <option value="vehicle_support"
                                    {{ old('ticket_type') == 'vehicle_support' ? 'selected' : '' }}>🚗 Vehicle Support Request
                                </option>
                                <option value="asset_request" {{ old('ticket_type') == 'asset_request' ? 'selected' : '' }}>
                                    📦 New Asset Request</option>
                                <option value="asset_repair" {{ old('ticket_type') == 'asset_repair' ? 'selected' : '' }}>
                                    🔧 Asset Repair Request</option>
                            </select>
                            @error('ticket_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <h2 class="content-heading pt-0 mb-3">
                        <i class="fa fa-circle-info text-muted me-1"></i> Basic Details
                    </h2>

                    <!-- Common Fields -->
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                                value="{{ old('title') }}" placeholder="Brief summary of request" required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Priority <span class="text-danger">*</span></label>
                            <select name="priority" class="form-select @error('priority') is-invalid @enderror" required>
                                <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Low</option>
                                <option value="medium" {{ old('priority', 'medium') == 'medium' ? 'selected' : '' }}>Medium</option>
                                <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>High</option>
                                <option value="urgent" {{ old('priority') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                            </select>
                            @error('priority')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Company</label>
                            <select name="company_id" class="form-select @error('company_id') is-invalid @enderror">
                                <option value="">Select Company</option>
                                @foreach ($companies as $company)
                                    <option value="{{ $company['id'] }}"
                                        {{ old('company_id') == $company['id'] ? 'selected' : '' }}>
                                        {{ $company['name'] }}
                                    </option>
                                @endforeach
                            </select>
                            @error('company_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Project Name</label>
                            <select name="project_name" class="form-select @error('project_name') is-invalid @enderror">
                                <option value="">Select Project</option>
                                @foreach ($projects as $project)
                                    <option value="{{ $project->id }}"
                                        {{ old('project_name') == $project->id ? 'selected' : '' }}>
                                        {{ $project->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('project_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Description <span class="text-danger">*</span></label>
                            <textarea name="description" rows="3" class="form-control @error('description') is-invalid @enderror" placeholder="Detailed description of your request..." required>{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Vehicle Support Fields -->
                    <div id="vehicle_fields" class="type-specific-fields mb-4" style="display: none;">
                        <h2 class="content-heading pt-0 mb-3 text-primary">
                            <i class="fa fa-car me-1"></i> Vehicle Support Details
                        </h2>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Vehicle Type <span class="text-danger">*</span></label>
                                <select name="vehicle_type_id"
                                    class="form-select @error('vehicle_type_id') is-invalid @enderror">
                                    <option value="">Select Vehicle Type</option>
                                    @foreach ($vehicleTypes as $type)
                                        <option value="{{ $type->id }}"
                                            {{ old('vehicle_type_id') == $type->id ? 'selected' : '' }}>
                                            {{ $type->type_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('vehicle_type_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Passenger Count <span class="text-danger">*</span></label>
                                <input type="number" name="passenger_count"
                                    class="form-control @error('passenger_count') is-invalid @enderror"
                                    value="{{ old('passenger_count', 1) }}" min="1">
                                @error('passenger_count')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Trip Start Date & Time <span class="text-danger">*</span></label>
                                <input type="datetime-local" name="trip_start_datetime"
                                    class="form-control @error('trip_start_datetime') is-invalid @enderror"
                                    value="{{ old('trip_start_datetime') }}">
                                @error('trip_start_datetime')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Trip End Date & Time <span class="text-danger">*</span></label>
                                <input type="datetime-local" name="trip_end_datetime"
                                    class="form-control @error('trip_end_datetime') is-invalid @enderror"
                                    value="{{ old('trip_end_datetime') }}">
                                @error('trip_end_datetime')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Dynamic Trip Locations Grid -->
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Trip Locations / Stops <span class="text-danger">*</span></label>
                                <div class="p-3 bg-body-light rounded border">
                                    <div id="trip_locations_container">
                                        <!-- Trip location rows will be added here -->
                                    </div>
                                    <button type="button" class="btn btn-sm btn-success mt-2" id="add_trip_location">
                                        <i class="fa fa-plus me-1"></i> Add Stoppage / Leg
                                    </button>
                                </div>
                                @error('trip_locations')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- The Modal (UPDATED with search) -->
                        <div id="mapModal" class="modal">
                            <div class="modal-content-custom">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h4 class="mb-0 fw-bold"><i class="fa fa-map-pin text-primary me-1"></i> Select Location</h4>
                                    <button type="button" class="btn-close" id="closeModalX" aria-label="Close"></button>
                                </div>
                                <p class="text-muted fs-sm mb-3">Search for a place or click on the map to drop a pin.</p>

                                <!-- Location Search Input -->
                                <div class="position-relative mb-2">
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fa fa-search"></i></span>
                                        <input type="text" id="locationSearchInput" class="form-control"
                                            placeholder="Search for a location or address...">
                                    </div>
                                </div>
                                <!-- Search Results Container -->
                                <div id="searchResults" class="list-group">
                                    <!-- Search results will be dynamically added here -->
                                </div>

                                <div id="map"></div>
                                <div class="d-flex justify-content-end gap-2">
                                    <button type="button" class="btn btn-alt-secondary btn-sm" id="closeModal">Cancel</button>
                                    <button type="button" class="btn btn-primary btn-sm" id="confirmLocation">
                                        <i class="fa fa-check me-1"></i> Confirm Location
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Trip Purpose <span class="text-danger">*</span></label>
                                <textarea name="trip_purpose" rows="2" class="form-control @error('trip_purpose') is-invalid @enderror" placeholder="State the purpose and destination of this trip">{{ old('trip_purpose') }}</textarea>
                                @error('trip_purpose')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Asset Request Fields -->
                    <div id="asset_request_fields" class="type-specific-fields mb-4" style="display: none;">
                        <h2 class="content-heading pt-0 mb-3 text-info">
                            <i class="fa fa-box-open me-1"></i> New Asset Request Details
                        </h2>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Asset Category <span class="text-danger">*</span></label>
                                <select name="asset_category_id"
                                    class="form-select @error('asset_category_id') is-invalid @enderror">
                                    <option value="">Select Category</option>
                                    @foreach ($assetCategories as $category)
                                        <option value="{{ $category->id }}"
                                            {{ old('asset_category_id') == $category->id ? 'selected' : '' }}>
                                            {{ $category->category_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('asset_category_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Requested Asset Name <span class="text-danger">*</span></label>
                                <input type="text" name="requested_asset_name"
                                    class="form-control @error('requested_asset_name') is-invalid @enderror"
                                    value="{{ old('requested_asset_name') }}" placeholder="e.g. Ergonomic Chair, Dell Monitor">
                                @error('requested_asset_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Floor <span class="text-danger">*</span></label>
                                <select name="floor_id" class="form-select @error('floor_id') is-invalid @enderror">
                                    <option value="">Select Floor</option>
                                    @foreach ($floors as $floor)
                                        <option value="{{ $floor->id }}"
                                            {{ old('floor_id') == $floor->id ? 'selected' : '' }}>
                                            {{ $floor->building->site_name ?? '' }} - {{ $floor->floor_label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('floor_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Location within Floor <span class="text-danger">*</span></label>
                                <input type="text" name="location_within_floor"
                                    class="form-control @error('location_within_floor') is-invalid @enderror"
                                    value="{{ old('location_within_floor') }}" placeholder="e.g., Room 301, Desk A">
                                @error('location_within_floor')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Asset Specifications</label>
                                <textarea name="asset_specifications" rows="2"
                                    class="form-control @error('asset_specifications') is-invalid @enderror"
                                    placeholder="Provide any specific technical requirements or specifications">{{ old('asset_specifications') }}</textarea>
                                @error('asset_specifications')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Asset Repair Fields -->
                    <div id="asset_repair_fields" class="type-specific-fields mb-4" style="display: none;">
                        <h2 class="content-heading pt-0 mb-3 text-warning">
                            <i class="fa fa-wrench me-1"></i> Asset Repair Details
                        </h2>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Floor <span class="text-danger">*</span></label>
                                <select name="repair_floor_id" id="repair_floor_id"
                                    class="form-select @error('floor_id') is-invalid @enderror">
                                    <option value="">Select Floor</option>
                                    @foreach ($floors as $floor)
                                        <option value="{{ $floor->id }}"
                                            {{ old('floor_id') == $floor->id ? 'selected' : '' }}>
                                            {{ $floor->building->site_name ?? '' }} - {{ $floor->floor_label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('floor_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Location within Floor <span class="text-danger">*</span></label>
                                <input type="text" name="repair_location_within_floor"
                                    id="repair_location_within_floor"
                                    class="form-control @error('location_within_floor') is-invalid @enderror"
                                    value="{{ old('location_within_floor') }}" placeholder="e.g., Room 301, Desk A">
                                @error('location_within_floor')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Asset Category Filter</label>
                                <select id="repair_category_filter" class="form-select">
                                    <option value="">All Categories</option>
                                    @foreach ($assetCategories as $category)
                                        <option value="{{ $category->id }}">
                                            {{ $category->category_name }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Optional filter to narrow down assets</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Search Asset</label>
                                <input type="text" id="repair_asset_search" class="form-control"
                                    placeholder="Search by tag or name...">
                                <small class="text-muted">Quick search for assets</small>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Select Asset <span class="text-danger">*</span></label>
                                <select name="asset_id" id="repair_asset_id"
                                    class="form-select @error('asset_id') is-invalid @enderror" size="6">
                                    <option value="">Select Floor First</option>
                                </select>
                                @error('asset_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="mt-2">
                                    <span class="badge bg-info" id="asset_count_badge">0 assets available</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Attachments -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Attachments (Optional)</label>
                            <input type="file" name="attachments[]" class="form-control" multiple
                                accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                            <small class="text-muted">You can upload multiple files (PDF, Images, Word documents, max 10MB each)</small>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fa fa-paper-plane me-1"></i> Submit Ticket
                            </button>
                            <a href="{{ route('tickets.index') }}" class="btn btn-alt-secondary">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
        // Pass data from Blade to JavaScript
        const allAssets = @json($assets);
        let filteredRepairAssets = [];

        /* ============================
        VARIABLES
        ============================ */
        let activeInput = null;
        let map = null;
        let marker = null;
        let selectedCoords = null;
        let selectedAddr = null;
        let tripLocationIndex = 0;
        let reverseGeocodeTimeout = null;

        // Custom Leaflet Icons for Start and End Points
        const startIcon = L.divIcon({
            className: 'custom-div-icon',
            html: '<div style="background-color: #28a745; width: 12px; height: 12px; border-radius: 50%; border: 2px solid white; box-shadow: 0 0 5px rgba(0,0,0,0.5);"></div>',
            iconSize: [12, 12],
            iconAnchor: [6, 6]
        });

        const endIcon = L.divIcon({
            className: 'custom-div-icon',
            html: '<div style="background-color: #dc3545; width: 12px; height: 12px; border-radius: 50%; border: 2px solid white; box-shadow: 0 0 5px rgba(0,0,0,0.5);"></div>',
            iconSize: [12, 12],
            iconAnchor: [6, 6]
        });

        /* ============================
        TRIP LOCATIONS DYNAMIC GRID
        ============================ */
        function addTripLocation(startLocation = '', endLocation = '', startCoordinates = {}, endCoordinates = {}) {
            const container = document.getElementById('trip_locations_container');
            const index = tripLocationIndex++;

            let initialStartLocation = startLocation;
            let initialStartCoordinates = { ...startCoordinates };

            if (!initialStartLocation) {
                const existingRows = container.querySelectorAll('.trip-location-row');
                if (existingRows.length > 0) {
                    const lastRowEndInput = existingRows[existingRows.length - 1].querySelector('textarea[name$="[end]"]');
                    if (lastRowEndInput) {
                        initialStartLocation = lastRowEndInput.value;
                        initialStartCoordinates = getLocationCoordinates(lastRowEndInput);
                    }
                }
            }

            const startLat = initialStartCoordinates.latitude ?? initialStartCoordinates.lat ?? '';
            const startLng = initialStartCoordinates.longitude ?? initialStartCoordinates.lng ?? '';
            const endLat = endCoordinates.latitude ?? endCoordinates.lat ?? '';
            const endLng = endCoordinates.longitude ?? endCoordinates.lng ?? '';

            const row = document.createElement('div');
            row.className = 'trip-location-row mb-2';
            row.dataset.uniqueIndex = index;
            row.innerHTML = `
                <div class="row g-2 align-items-center">
                    <div class="col-md-1 text-center">
                        <span class="badge bg-primary fs-xs py-2 px-2">Stop X</span>
                    </div>
                    <div class="col-md-5">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-success-light text-success"><i class="fa fa-map-marker-alt"></i></span>
                            <textarea
                                name="trip_locations[${index}][start]"
                                id="trip_location_start_${index}"
                                class="form-control location-input"
                                placeholder="Start Location (click to pick on map)"
                                rows="2"
                                required>${initialStartLocation}</textarea>
                        </div>
                        <input type="hidden" name="trip_locations[${index}][start_lat]" value="${escapeHtml(startLat)}">
                        <input type="hidden" name="trip_locations[${index}][start_lng]" value="${escapeHtml(startLng)}">
                    </div>

                    <div class="col-md-5">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-danger-light text-danger"><i class="fa fa-flag-checkered"></i></span>
                            <textarea
                                name="trip_locations[${index}][end]"
                                id="trip_location_end_${index}"
                                class="form-control location-input"
                                placeholder="End Location (click to pick on map)"
                                rows="2"
                                required>${endLocation}</textarea>
                        </div>
                        <input type="hidden" name="trip_locations[${index}][end_lat]" value="${escapeHtml(endLat)}">
                        <input type="hidden" name="trip_locations[${index}][end_lng]" value="${escapeHtml(endLng)}">
                    </div>

                    <div class="col-md-1 text-center">
                        <button type="button"
                                class="btn btn-alt-danger btn-sm remove-trip-location"
                                title="Remove Stoppage"
                                data-unique-index="${index}">
                            <i class="fa fa-trash"></i>
                        </button>
                    </div>
                </div>
            `;

            container.appendChild(row);

            row.querySelector('.remove-trip-location').addEventListener('click', function() {
                removeTripLocation(this.dataset.uniqueIndex);
            });

            row.querySelectorAll('.location-input').forEach(input => {
                input.addEventListener('click', () => openMapModal(input));
            });

            updateTripLocationLabels();
        }

        function escapeHtml(value) {
            return String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/"/g, '&quot;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;');
        }

        function getLocationPoint(input) {
            return input.name.match(/\[(start|end)\]$/)?.[1] || null;
        }

        function getLocationCoordinates(input) {
            const point = getLocationPoint(input);
            const row = input.closest('.trip-location-row');

            if (!point || !row) {
                return {};
            }

            return {
                latitude: row.querySelector(`input[name$="[${point}_lat]"]`)?.value || '',
                longitude: row.querySelector(`input[name$="[${point}_lng]"]`)?.value || '',
            };
        }

        function setLocationCoordinates(input, latitude, longitude) {
            const point = getLocationPoint(input);
            const row = input.closest('.trip-location-row');

            if (!point || !row) {
                return;
            }

            const latInput = row.querySelector(`input[name$="[${point}_lat]"]`);
            const lngInput = row.querySelector(`input[name$="[${point}_lng]"]`);

            if (latInput) {
                latInput.value = Number(latitude).toFixed(6);
            }

            if (lngInput) {
                lngInput.value = Number(longitude).toFixed(6);
            }
        }

        function updateTripLocationLabels() {
            const rows = document.querySelectorAll('#trip_locations_container .trip-location-row');
            rows.forEach((row, visualIndex) => {
                const badge = row.querySelector('.badge');
                if (badge) {
                    badge.textContent = `Stop ${visualIndex + 1}`;
                }
            });
        }

        function removeTripLocation(uniqueIndex) {
            const row = document.querySelector(`.trip-location-row[data-unique-index="${uniqueIndex}"]`);
            if (row) {
                row.remove();
            }

            const remainingRows = document.querySelectorAll('.trip-location-row');
            if (remainingRows.length === 0) {
                addTripLocation();
            } else {
                updateTripLocationLabels();
            }
        }

        function initializeTripLocations() {
            const container = document.getElementById('trip_locations_container');
            container.innerHTML = '';
            tripLocationIndex = 0;

            const oldTripLocations = @json(old('trip_locations', []));

            if (oldTripLocations && oldTripLocations.length > 0) {
                oldTripLocations.forEach(location => {
                    addTripLocation(
                        location.start,
                        location.end, {
                            latitude: location.start_lat,
                            longitude: location.start_lng
                        }, {
                            latitude: location.end_lat,
                            longitude: location.end_lng
                        }
                    );
                });
            } else {
                addTripLocation();
            }
            updateTripLocationLabels();
        }

        document.getElementById('add_trip_location').addEventListener('click', function() {
            addTripLocation();
        });


        /* ============================
        MAP INITIALIZATION
        ============================ */
        function initMap() {
            if (map) return;

            map = L.map('map').setView([23.7516691, 90.3901753], 13);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);

            map.on('click', e => {
                selectedAddr = null;
                const point = activeInput ? getLocationPoint(activeInput) : null;
                const icon = point === 'start' ? startIcon : endIcon;

                if (!marker) {
                    marker = L.marker(e.latlng, { icon }).addTo(map);
                } else {
                    marker.setLatLng(e.latlng);
                    marker.setIcon(icon);
                }
                selectedCoords = e.latlng;
                reverseGeocode(e.latlng);
            });
        }

        function openMapModal(input) {
            activeInput = input;
            const modal = document.getElementById('mapModal');
            modal.style.display = 'block';

            initMap();
            setTimeout(() => {
                map.invalidateSize();
                const existingCoords = getLocationCoordinates(input);
                if (existingCoords.latitude && existingCoords.longitude) {
                    const lat = parseFloat(existingCoords.latitude);
                    const lng = parseFloat(existingCoords.longitude);
                    const latlng = L.latLng(lat, lng);
                    const point = getLocationPoint(input);
                    const icon = point === 'start' ? startIcon : endIcon;

                    if (!marker) {
                        marker = L.marker(latlng, { icon }).addTo(map);
                    } else {
                        marker.setLatLng(latlng);
                        marker.setIcon(icon);
                    }
                    map.setView(latlng, 15);
                    selectedCoords = latlng;
                    selectedAddr = input.value;
                } else {
                    if (marker) {
                        map.removeLayer(marker);
                        marker = null;
                    }
                    selectedCoords = null;
                    selectedAddr = null;
                }
            }, 200);
        }

        const closeModalHandler = () => {
            document.getElementById('mapModal').style.display = 'none';
            document.getElementById('locationSearchInput').value = '';
            document.getElementById('searchResults').style.display = 'none';
            document.getElementById('searchResults').innerHTML = '';
        };

        document.getElementById('closeModal').addEventListener('click', closeModalHandler);
        if (document.getElementById('closeModalX')) {
            document.getElementById('closeModalX').addEventListener('click', closeModalHandler);
        }

        let reverseGeocodePromise = null;

        async function fetchAddress(lat, lon) {
            // 1. Try internal proxy route
            try {
                const res = await fetch(`{{ route('api.reverse-geocode') }}?lat=${lat}&lon=${lon}`);
                if (res.ok) {
                    const data = await res.json();
                    if (data && data.display_name) {
                        return data.display_name;
                    }
                }
            } catch (err) {
                console.warn("Proxy reverse geocode failed, falling back to direct:", err);
            }

            // 2. Direct fallback
            try {
                const res = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lon}&addressdetails=1`);
                if (res.ok) {
                    const data = await res.json();
                    if (data && data.display_name) {
                        return data.display_name;
                    }
                }
            } catch (err) {
                console.error("Direct reverse geocode failed:", err);
            }

            return null;
        }

        function reverseGeocode(latlng) {
            const searchInput = document.getElementById('locationSearchInput');
            if (searchInput) {
                searchInput.value = "Fetching address...";
            }
            if (marker) {
                marker.bindPopup('<div class="fs-xs text-muted"><i class="fa fa-spinner fa-spin me-1"></i> Fetching address...</div>').openPopup();
            }

            reverseGeocodePromise = fetchAddress(latlng.lat, latlng.lng)
                .then(displayName => {
                    if (displayName) {
                        selectedAddr = displayName;
                        if (searchInput) searchInput.value = displayName;
                        if (marker) {
                            marker.bindPopup(`<div class="fs-xs fw-semibold text-dark">${displayName}</div>`).openPopup();
                        }
                    } else {
                        const fallbackCoords = `Lat: ${latlng.lat.toFixed(6)}, Lng: ${latlng.lng.toFixed(6)}`;
                        if (searchInput) searchInput.value = fallbackCoords;
                        if (marker) {
                            marker.bindPopup(`<div class="fs-xs text-dark">${fallbackCoords}</div>`).openPopup();
                        }
                    }
                    return displayName;
                });
        }

        document.getElementById('confirmLocation').addEventListener('click', async () => {
            if (activeInput && selectedCoords) {
                const confirmBtn = document.getElementById('confirmLocation');
                confirmBtn.disabled = true;
                const originalHtml = confirmBtn.innerHTML;
                confirmBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Locating...';

                // If reverse geocode is pending, await it
                if (!selectedAddr && reverseGeocodePromise) {
                    try {
                        const addr = await Promise.race([
                            reverseGeocodePromise,
                            new Promise(resolve => setTimeout(() => resolve(null), 2000))
                        ]);
                        if (addr) selectedAddr = addr;
                    } catch (e) {
                        console.error(e);
                    }
                } else if (!selectedAddr) {
                    try {
                        const addr = await fetchAddress(selectedCoords.lat, selectedCoords.lng);
                        if (addr) selectedAddr = addr;
                    } catch (e) {
                        console.error(e);
                    }
                }

                confirmBtn.disabled = false;
                confirmBtn.innerHTML = originalHtml;

                const finalAddress = selectedAddr || `Lat: ${selectedCoords.lat.toFixed(6)}, Lng: ${selectedCoords.lng.toFixed(6)}`;
                activeInput.value = finalAddress;
                setLocationCoordinates(activeInput, selectedCoords.lat, selectedCoords.lng);
            }
            closeModalHandler();
        });

        /* ============================
        LOCATION SEARCH FUNCTIONS
        ============================ */
        let searchTimeout;
        const searchInput = document.getElementById('locationSearchInput');
        const resultsContainer = document.getElementById('searchResults');

        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value.trim();

            if (query.length < 3) {
                resultsContainer.style.display = 'none';
                resultsContainer.innerHTML = '';
                return;
            }

            searchTimeout = setTimeout(() => {
                fetchLocationSuggestions(query);
            }, 400);
        });

        function fetchLocationSuggestions(query) {
            const url = `https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(query)}&format=json&addressdetails=1&limit=5`;

            fetch(url)
                .then(res => res.json())
                .then(data => {
                    displaySearchResults(data);
                })
                .catch(err => {
                    console.error("Search error:", err);
                    resultsContainer.style.display = 'none';
                });
        }

        function displaySearchResults(results) {
            resultsContainer.innerHTML = '';
            if (results.length === 0) {
                resultsContainer.style.display = 'none';
                return;
            }

            results.forEach(result => {
                const item = document.createElement('a');
                item.href = '#';
                item.className = 'list-group-item list-group-item-action';
                item.textContent = result.display_name;
                item.addEventListener('click', function(e) {
                    e.preventDefault();
                    selectSearchResult(result);
                });
                resultsContainer.appendChild(item);
            });

            resultsContainer.style.display = 'block';
        }

        function selectSearchResult(result) {
            const lat = parseFloat(result.lat);
            const lon = parseFloat(result.lon);
            const latlng = L.latLng(lat, lon);

            map.setView(latlng, 16);

            const point = activeInput ? getLocationPoint(activeInput) : null;
            const icon = point === 'start' ? startIcon : endIcon;

            if (!marker) {
                marker = L.marker(latlng, { icon }).addTo(map);
            } else {
                marker.setLatLng(latlng);
                marker.setIcon(icon);
            }

            selectedCoords = latlng;
            selectedAddr = result.display_name;
            searchInput.value = result.display_name;
            resultsContainer.style.display = 'none';
        }


        /* ============================
        TYPE SWITCHING & ASSET FILTERING
        ============================ */
        document.getElementById('ticket_type').addEventListener('change', function() {
            document.querySelectorAll('.type-specific-fields').forEach(el => {
                el.style.display = 'none';
                el.querySelectorAll('input, select, textarea').forEach(input => {
                    input.removeAttribute('required');
                });
            });

            const selectedType = this.value;
            if (selectedType === 'vehicle_support') {
                const fields = document.getElementById('vehicle_fields');
                fields.style.display = 'block';

                fields.querySelector('select[name="vehicle_type_id"]').setAttribute('required', 'required');
                fields.querySelector('input[name="passenger_count"]').setAttribute('required', 'required');
                fields.querySelector('input[name="trip_start_datetime"]').setAttribute('required', 'required');
                fields.querySelector('input[name="trip_end_datetime"]').setAttribute('required', 'required');
                fields.querySelector('textarea[name="trip_purpose"]').setAttribute('required', 'required');

                initializeTripLocations();
            } else if (selectedType === 'asset_request') {
                const fields = document.getElementById('asset_request_fields');
                fields.style.display = 'block';
                fields.querySelector('select[name="floor_id"]').setAttribute('required', 'required');
                fields.querySelector('input[name="location_within_floor"]').setAttribute('required', 'required');
                fields.querySelector('select[name="asset_category_id"]').setAttribute('required', 'required');
                fields.querySelector('input[name="requested_asset_name"]').setAttribute('required', 'required');
            } else if (selectedType === 'asset_repair') {
                const fields = document.getElementById('asset_repair_fields');
                fields.style.display = 'block';
                fields.querySelector('select[name="repair_floor_id"]').setAttribute('required', 'required');
                fields.querySelector('input[name="repair_location_within_floor"]').setAttribute('required', 'required');
                fields.querySelector('select[name="asset_id"]').setAttribute('required', 'required');
            }
        });

        function filterAndRenderRepairAssets() {
            const floorId = document.getElementById('repair_floor_id').value;
            const categoryFilter = document.getElementById('repair_category_filter').value;
            const searchTerm = document.getElementById('repair_asset_search').value.toLowerCase();
            const assetSelect = document.getElementById('repair_asset_id');

            assetSelect.innerHTML = '';

            if (!floorId) {
                assetSelect.innerHTML = '<option value="">Select Floor First</option>';
                assetSelect.size = 1;
                updateAssetCount(0);
                return;
            }

            filteredRepairAssets = allAssets.filter(asset => {
                const matchesFloor = asset.floor_id == floorId;
                const matchesCategory = !categoryFilter || asset.category_id == categoryFilter;
                const matchesSearch = !searchTerm ||
                    asset.asset_tag.toLowerCase().includes(searchTerm) ||
                    asset.asset_name.toLowerCase().includes(searchTerm) ||
                    (asset.category && asset.category.category_name.toLowerCase().includes(searchTerm));

                return matchesFloor && matchesCategory && matchesSearch;
            });

            if (filteredRepairAssets.length === 0) {
                assetSelect.innerHTML = '<option value="">No assets found with current filters</option>';
                assetSelect.size = 1;
                updateAssetCount(0);
                return;
            }

            assetSelect.size = Math.min(filteredRepairAssets.length + 1, 8);

            const headerOption = document.createElement('option');
            headerOption.value = "";
            headerOption.textContent = `--- Select Asset (${filteredRepairAssets.length} found) ---`;
            assetSelect.appendChild(headerOption);

            filteredRepairAssets.forEach(asset => {
                const option = document.createElement('option');
                option.value = asset.id;

                let optionText = `${asset.asset_tag} - ${asset.asset_name}`;
                if (asset.category) {
                    optionText += ` (${asset.category.category_name})`;
                }
                if (asset.location_within_floor) {
                    optionText += ` [${asset.location_within_floor}]`;
                }

                option.textContent = optionText;
                assetSelect.appendChild(option);
            });

            updateAssetCount(filteredRepairAssets.length);
        }

        function updateAssetCount(count) {
            const badge = document.getElementById('asset_count_badge');
            badge.textContent = `${count} asset${count !== 1 ? 's' : ''} available`;

            if (count === 0) {
                badge.className = 'badge bg-secondary';
            } else if (count < 5) {
                badge.className = 'badge bg-warning';
            } else {
                badge.className = 'badge bg-success';
            }
        }

        document.getElementById('repair_floor_id').addEventListener('change', filterAndRenderRepairAssets);
        document.getElementById('repair_category_filter').addEventListener('change', filterAndRenderRepairAssets);

        let assetSearchTimeout;
        document.getElementById('repair_asset_search').addEventListener('input', function() {
            clearTimeout(assetSearchTimeout);
            assetSearchTimeout = setTimeout(filterAndRenderRepairAssets, 300);
        });

        document.getElementById('repair_asset_id').addEventListener('change', function() {
            const assetId = this.value;
            const locationInput = document.getElementById('repair_location_within_floor');

            if (!assetId) {
                locationInput.value = '';
                return;
            }

            const selectedAsset = allAssets.find(asset => asset.id == assetId);

            if (selectedAsset && selectedAsset.location_within_floor) {
                locationInput.value = selectedAsset.location_within_floor;
            } else {
                locationInput.value = '';
            }
        });

        const initialTicketType = document.getElementById('ticket_type').value;
        if (initialTicketType) {
            document.getElementById('ticket_type').dispatchEvent(new Event('change'));

            if (initialTicketType === 'vehicle_support') {
                initializeTripLocations();
            }

            if (initialTicketType === 'asset_repair') {
                setTimeout(filterAndRenderRepairAssets, 100);
            }
        }
    </script>
@endsection
